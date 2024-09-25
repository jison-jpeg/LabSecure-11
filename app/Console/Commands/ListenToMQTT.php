<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\User;
use App\Models\Laboratory;
use App\Models\Schedule;
use App\Models\Attendance;
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

        // Create MQTT client and keep the connection alive
        $this->mqtt = new MqttClient($brokerAddress, $brokerPort, 'laravel_mqtt_listener');
        $connectionSettings = (new ConnectionSettings())
            ->setKeepAliveInterval(60);  // Keep the connection alive for 60 seconds

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

    // This method handles the laboratory access using the same logic as your API controller
    private function handleLaboratoryAccess($rfid_number, $type)
    {
        // Find the user by RFID
        $user = User::where('rfid_number', $rfid_number)->first();
        if (!$user) {
            $error = 'User not found';
            $this->error($error);
            $this->publishToMqtt($rfid_number, $type, 'denied', null, $error);  // Publish error
            return;
        }

        // Check if the user is Admin, IT Support, or can access without a schedule
        if (in_array($user->role->name, ['admin', 'it_support'])) {
            $this->handlePersonnelAccess($user, $type);
        } else {
            $this->handleScheduledUserAccess($user, $type);
        }
    }

    // Handle access for personnel without schedules (Admin, IT Support, etc.)
    private function handlePersonnelAccess($user, $type)
    {
        $laboratory = Laboratory::where('id', 1)->first();  // Adjust logic to find the correct laboratory

        switch ($type) {
            case 'entrance':
                // Log the entrance action
                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'check_in',
                    'model' => 'Laboratory',
                    'model_id' => $laboratory->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Occupied']),
                ]);
                LaboratoryStatusUpdated::dispatch($laboratory);
                break;

            case 'exit':
                // Log the exit action
                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'check_out',
                    'model' => 'Laboratory',
                    'model_id' => $laboratory->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Available']),
                ]);
                LaboratoryStatusUpdated::dispatch($laboratory);
                break;
        }

        $this->publishToMqtt($user->rfid_number, $type, 'granted', $user->full_name);
    }

    // Handle access for users with active schedules
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
            $this->publishToMqtt($user->rfid_number, $type, 'denied', $user->full_name, $error);  // Publish error
            return;
        }

        $laboratory = $schedule->laboratory;

        // Prevent access if the laboratory is locked and the user is not an admin
        if ($laboratory->status === 'Locked' && !$user->isAdmin()) {
            $error = 'Laboratory is locked.';
            $this->error($error);
            $this->publishToMqtt($user->rfid_number, $type, 'denied', $user->full_name, $error);  // Publish error
            return;
        }

        // Access the subject name and schedule code from the schedule
        $subjectName = $schedule->subject->name;
        $scheduleCode = $schedule->schedule_code; // Get the schedule code

        // Handle attendance
        $currentDate = $currentTime->toDateString();
        $attendance = Attendance::firstOrCreate([
            'user_id' => $user->id,
            'date' => $currentDate,
            'schedule_id' => $schedule->id,
        ]);

        switch ($type) {
            case 'entrance':
                // Log the entrance action
                $attendance->sessions()->create(['time_in' => $currentTime]);
                $laboratory->update(['status' => 'Occupied']);
                LaboratoryStatusUpdated::dispatch($laboratory);

                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'check_in',
                    'model' => 'Attendance',
                    'model_id' => $attendance->id,
                    'details' => json_encode([
                        'rfid_number' => $user->rfid_number,
                        'laboratory_status' => 'Occupied',
                        'subject_name' => $subjectName,
                        'schedule_code' => $scheduleCode,  // Include schedule code in the log
                    ]),
                ]);
                $this->publishToMqtt($user->rfid_number, $type, 'granted', $user->full_name, null, $subjectName, $scheduleCode);  // Include schedule code
                break;

            case 'exit':
                // Log the exit action
                $lastSession = $attendance->sessions()->whereNull('time_out')->latest('time_in')->first();
                if ($lastSession) {
                    $lastSession->update(['time_out' => $currentTime]);
                }
                $laboratory->update(['status' => 'Available']);
                LaboratoryStatusUpdated::dispatch($laboratory);

                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'check_out',
                    'model' => 'Attendance',
                    'model_id' => $attendance->id,
                    'details' => json_encode([
                        'rfid_number' => $user->rfid_number,
                        'laboratory_status' => 'Available',
                        'subject_name' => $subjectName,
                        'schedule_code' => $scheduleCode,  // Include schedule code in the log
                    ]),
                ]);
                $this->publishToMqtt($user->rfid_number, $type, 'granted', $user->full_name, null, $subjectName, $scheduleCode);  // Include schedule code
                break;
        }

        // Finalize attendance for the day
        $attendance->calculateAndSaveStatusAndRemarks();
        AttendanceRecorded::dispatch($attendance);

        $this->info('Attendance recorded successfully.');
    }


    // Helper method to publish the access result to MQTT with error handling
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
