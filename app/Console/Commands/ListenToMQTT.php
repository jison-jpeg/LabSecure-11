<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\User;
use App\Models\Laboratory;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\TransactionLog;
use App\Events\LaboratoryStatusUpdated;
use App\Events\AttendanceRecorded;
use Carbon\Carbon;

class ListenToMQTT extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listens to MQTT messages on a topic.';

    private $mqtt;

    public function handle()
    {
        // Get MQTT details from .env
        $brokerAddress = env('MQTT_BROKER_ADDRESS', '127.0.0.1');
        $brokerPort = env('MQTT_PORT', 1883);
        $mqttUser = env('MQTT_USERNAME') ?: null;
        $mqttPassword = env('MQTT_PASSWORD') ?: null;

        // Create MQTT client and keep the connection alive
        $this->mqtt = new MqttClient($brokerAddress, $brokerPort, 'laravel_mqtt_listener');
        $connectionSettings = (new ConnectionSettings())
            ->setKeepAliveInterval(60)  // Keep the connection alive for 60 seconds
            ->setUsername($mqttUser)
            ->setPassword($mqttPassword);

        // Connect to the broker
        try {
            $this->mqtt->connect($connectionSettings, true);
            $this->info("Connected to MQTT Broker at {$brokerAddress}. Port: {$brokerPort}");
        } catch (\Exception $e) {
            $this->error("Failed to connect to MQTT Broker: " . $e->getMessage());
            return;
        }

        // Subscribe to separate topics for entrance and exit
        try {
            // Subscribe to entrance topic
            $this->mqtt->subscribe('laravel_topic/entrance', function ($topic, $message) {
                $this->info("Received '{$message}' on topic '{$topic}'");

                // Decode the JSON message
                $payload = json_decode($message, true);
                if (isset($payload['rfid_number']) && isset($payload['type'])) {
                    $this->handleLaboratoryAccess($payload['rfid_number'], 'entrance');
                } else {
                    $this->error('Invalid message format.');
                }
            }, 0);

            // Subscribe to exit topic
            $this->mqtt->subscribe('laravel_topic/exit', function ($topic, $message) {
                $this->info("Received '{$message}' on topic '{$topic}'");

                // Decode the JSON message
                $payload = json_decode($message, true);
                if (isset($payload['rfid_number']) && isset($payload['type'])) {
                    $this->handleLaboratoryAccess($payload['rfid_number'], 'exit');
                } else {
                    $this->error('Invalid message format.');
                }
            }, 0);
        } catch (\Exception $e) {
            $this->error("Failed to subscribe to topic: " . $e->getMessage());
            return;
        }

        // Keep the MQTT loop running
        try {
            $this->mqtt->loop(true);
        } catch (\Exception $e) {
            $this->error("Error during MQTT loop: " . $e->getMessage());
        }
    }

    /**
     * Handles laboratory access based on RFID number and access type.
     *
     * @param string $rfid_number
     * @param string $type 'entrance' or 'exit'
     * @return void
     */
    private function handleLaboratoryAccess($rfid_number, $type)
    {
        // Find the user by RFID
        $user = User::where('rfid_number', $rfid_number)->first();
        if (!$user) {
            $error = 'User not found';
            $this->error($error);
            $this->publishToMqtt($rfid_number, $type, 'denied', null, $error);
            return;
        }

        // Check if the user is active
        if (!$user->isActive()) {
            $error = 'User is inactive';
            $this->error($error);
            $this->publishToMqtt($rfid_number, $type, 'denied', $user->full_name, $error);
            return;
        }

        // Check if the user is Admin, IT Support, or can access without a schedule
        if (in_array($user->role->name, ['admin', 'it_support'])) {
            $this->handlePersonnelAccess($user, $type);
        } else {
            $this->handleScheduledUserAccess($user, $type);
        }
    }

    /**
     * Handles access for personnel without schedules (Admin, IT Support, etc.).
     *
     * @param \App\Models\User $user
     * @param string $type 'entrance' or 'exit'
     * @return void
     */
    private function handlePersonnelAccess($user, $type)
    {
        // Find or create today's attendance record
        $currentDate = Carbon::now()->toDateString();
        $attendance = Attendance::firstOrCreate([
            'user_id' => $user->id,
            'date' => $currentDate,
            'schedule_id' => null, // Assuming personnel without schedule
        ]);

        if ($type === 'entrance') {
            // Check if there's already an open session
            $openSession = $attendance->sessions()->whereNull('time_out')->first();

            if ($openSession) {
                // Already checked in, ignore additional check-ins
                $this->info("User {$user->full_name} is already checked in.");
                $this->publishToMqtt($user->rfid_number, $type, 'granted', $user->full_name);
                return;
            }

            // Create a new session for entrance
            $session = $attendance->sessions()->create(['time_in' => Carbon::now()]);

            // Log the entrance action
            TransactionLog::create([
                'user_id' => $user->id,
                'action' => 'in',
                'model' => 'AttendanceSession',
                'model_id' => $session->id,
                'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Occupied']),
            ]);

            // Update laboratory status
            $laboratory = Laboratory::find(1); // Adjust logic to find the correct laboratory
            $laboratory->update(['status' => 'Occupied']);
            LaboratoryStatusUpdated::dispatch($laboratory);

            $this->publishToMqtt($user->rfid_number, $type, 'granted', $user->full_name);
        }

        if ($type === 'exit') {
            // Find the open session
            $session = $attendance->sessions()->whereNull('time_out')->first();

            if ($session) {
                // Close the session
                $session->update(['time_out' => Carbon::now()]);

                // Log the exit action
                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'out',
                    'model' => 'AttendanceSession',
                    'model_id' => $session->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Available']),
                ]);

                // Update laboratory status
                $laboratory = Laboratory::find(1); // Adjust logic to find the correct laboratory
                $laboratory->update(['status' => 'Available']);
                LaboratoryStatusUpdated::dispatch($laboratory);

                $this->publishToMqtt($user->rfid_number, $type, 'granted', $user->full_name);
            } else {
                $error = 'No active session found to check out.';
                $this->error($error);
                $this->publishToMqtt($user->rfid_number, $type, 'denied', $user->full_name, $error);
            }
        }

        // Recalculate attendance status after each check-in/out
        $attendance->calculateAndSaveStatusAndRemarks();
    }

    /**
     * Handles access for users with active schedules.
     *
     * @param \App\Models\User $user
     * @param string $type 'entrance' or 'exit'
     * @return void
     */
    private function handleScheduledUserAccess($user, $type)
    {
        // Find the current time and day
        $currentTime = Carbon::now();
        $currentDay = $currentTime->format('l'); // Full day name (e.g., Monday)

        // Find the current schedule based on time, day, and user's role
        $scheduleQuery = Schedule::where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->whereJsonContains('days_of_week', $currentDay);

        $schedule = null;

        if ($type === 'entrance') {
            if ($user->isInstructor()) {
                // Check if the user is the assigned instructor for this schedule
                $schedule = $scheduleQuery->where('instructor_id', $user->id)->first();
            } elseif ($user->isStudent()) {
                // Check if the student's section matches the section in the schedule
                $schedule = $scheduleQuery->where('section_id', $user->section_id)->first();
            }

            if (!$schedule) {
                $error = 'No schedule found.';
                $this->error($error);
                $this->publishToMqtt($user->rfid_number, $type, 'denied', $user->full_name, $error);
                return;
            }

            // Prevent access if the laboratory is locked and the user is not an admin
            $laboratory = $schedule->laboratory;
            if ($laboratory->status === 'Locked' && !$user->isAdmin()) {
                $error = 'Laboratory is locked.';
                $this->error($error);
                $this->publishToMqtt($user->rfid_number, $type, 'denied', $user->full_name, $error);
                return;
            }

            // Access the subject name and schedule code from the schedule
            $subjectName = $schedule->subject->name;
            $scheduleCode = $schedule->schedule_code; // Get the schedule code

            // Find or create today's attendance record
            $currentDate = $currentTime->toDateString();
            $attendance = Attendance::firstOrCreate([
                'user_id' => $user->id,
                'date' => $currentDate,
                'schedule_id' => $schedule->id,
            ]);

            if ($type === 'entrance') {
                // Check if there's already an open session
                $openSession = $attendance->sessions()->whereNull('time_out')->first();

                if ($openSession) {
                    // Already checked in, ignore additional check-ins
                    $this->info("User {$user->full_name} is already checked in for schedule {$scheduleCode}.");
                    $this->publishToMqtt($user->rfid_number, $type, 'granted', $user->full_name, null, $subjectName, $scheduleCode);
                    return;
                }

                // Create a new session for entrance
                $session = $attendance->sessions()->create(['time_in' => $currentTime]);

                // Log the entrance action
                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'in',
                    'model' => 'AttendanceSession',
                    'model_id' => $session->id,
                    'details' => json_encode([
                        'rfid_number' => $user->rfid_number,
                        'laboratory_status' => 'Occupied',
                        'subject_name' => $subjectName,
                        'schedule_code' => $scheduleCode,
                    ]),
                ]);

                // Update laboratory status
                $laboratory->update(['status' => 'Occupied']);
                LaboratoryStatusUpdated::dispatch($laboratory);

                $this->publishToMqtt(
                    $user->rfid_number,
                    $type,
                    'granted',
                    $user->full_name,
                    null,
                    $subjectName,
                    $scheduleCode
                );
            }
        }

        if ($type === 'exit') {
            // Find the schedule for the current time
            if (!$schedule) {
                $schedule = Schedule::where('start_time', '<=', $currentTime)
                    ->where('end_time', '>=', $currentTime)
                    ->whereJsonContains('days_of_week', $currentDay)
                    ->where(function ($q) use ($user) {
                        if ($user->isInstructor()) {
                            $q->where('instructor_id', $user->id);
                        } elseif ($user->isStudent()) {
                            $q->where('section_id', $user->section_id);
                        }
                    })
                    ->first();
            }

            if (!$schedule) {
                $error = 'No schedule found for exit.';
                $this->error($error);
                $this->publishToMqtt($user->rfid_number, $type, 'denied', $user->full_name, $error);
                return;
            }

            // Find the attendance record
            $currentDate = $currentTime->toDateString();
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $currentDate)
                ->where('schedule_id', $schedule->id)
                ->first();

            if (!$attendance) {
                $error = 'No check-in record found for today.';
                $this->error($error);
                $this->publishToMqtt($user->rfid_number, $type, 'denied', $user->full_name, $error);
                return;
            }

            // Find the open session
            $session = $attendance->sessions()->whereNull('time_out')->first();

            if ($session) {
                // Close the session
                $session->update(['time_out' => $currentTime]);

                // Log the exit action
                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'out',
                    'model' => 'AttendanceSession',
                    'model_id' => $session->id,
                    'details' => json_encode([
                        'rfid_number' => $user->rfid_number,
                        'laboratory_status' => 'Available',
                        'subject_name' => $attendance->schedule->subject->name ?? 'Unknown Subject',
                        'schedule_code' => $attendance->schedule->schedule_code ?? 'Unknown Schedule Code',
                    ]),
                ]);

                // Update laboratory status
                $laboratory = $attendance->schedule->laboratory ?? Laboratory::find(1);
                $laboratory->update(['status' => 'Available']);
                LaboratoryStatusUpdated::dispatch($laboratory);

                $this->publishToMqtt(
                    $user->rfid_number,
                    $type,
                    'granted',
                    $user->full_name,
                    null,
                    $attendance->schedule->subject->name ?? null,
                    $attendance->schedule->schedule_code ?? null
                );
            } else {
                $error = 'No active session found to check out.';
                $this->error($error);
                $this->publishToMqtt($user->rfid_number, $type, 'denied', $user->full_name, $error);
                return;
            }
        }

        // Recalculate attendance status after each check-in/out
        if (isset($attendance)) {
            $attendance->calculateAndSaveStatusAndRemarks();
            AttendanceRecorded::dispatch($attendance);
            $this->info('Attendance recorded successfully.');
        }
    }

    /**
     * Helper method to publish the access result to MQTT with error handling.
     *
     * @param string $rfid_number
     * @param string $type 'entrance' or 'exit'
     * @param string $status 'granted' or 'denied'
     * @param string|null $fullName
     * @param string|null $error
     * @param string|null $subjectName
     * @param string|null $scheduleCode
     * @return void
     */
    private function publishToMqtt($rfid_number, $type, $status, $fullName = null, $error = null, $subjectName = null, $scheduleCode = null)
    {
        $message = json_encode([
            'rfid_number' => $rfid_number,
            'type' => $type,
            'status' => $status,
            'full_name' => $fullName,
            'subject_name' => $subjectName,
            'schedule_code' => $scheduleCode,
            'error' => $error,
        ]);

        // Publish to specific topic based on entrance or exit type
        $topic = ($type === 'entrance') ? 'laravel_topic_response/entrance' : 'laravel_topic_response/exit';

        try {
            $this->mqtt->publish($topic, $message, 0); // QoS 0 for simplicity
            $this->info("Published access result to {$topic}: {$message}");
        } catch (\Exception $e) {
            $this->error("Failed to publish to MQTT: " . $e->getMessage());
        }
    }
}
