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

        // Subscribe to the topic
        try {
            $this->mqtt->subscribe('laravel_topic', function ($topic, $message) {
                $this->info("Received '{$message}' on topic '{$topic}'");

                // Decode the JSON message
                $payload = json_decode($message, true);
                if (isset($payload['rfid_number']) && isset($payload['type'])) {
                    $this->handleLaboratoryAccess($payload['rfid_number'], $payload['type']);
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
            $this->error('User not found');
            $this->publishToMqtt($rfid_number, $type, 'denied');
            return;
        }

        // Check if the user is Admin, IT Support, or can access without a schedule
        if (in_array($user->role->name, ['admin', 'it_support'])) {
            $this->handlePersonnelAccess($user, $type);
            $this->publishToMqtt($rfid_number, $type, 'granted');
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

        $this->info('Access granted.');
        $this->publishToMqtt($user->rfid_number, $type, 'granted');
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

    if ($user->isInstructor()) {
        // Check if the user is the assigned instructor for this schedule
        $schedule = $scheduleQuery->where('instructor_id', $user->id)->first();
    } elseif ($user->isStudent()) {
        // Check if the student's section matches the section in the schedule
        $schedule = $scheduleQuery->where('section_id', $user->section_id)->first();
    } else {
        // Deny access if the user is neither an instructor nor a student
        $this->error('No active schedule found for this time or user role is not valid');
        $this->publishToMqtt($user->rfid_number, $type, 'denied');
        return;
    }

    if (!$schedule) {
        $this->error('No active schedule found for this time or day');
        $this->publishToMqtt($user->rfid_number, $type, 'denied');
        return;
    }

    $laboratory = $schedule->laboratory;

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
                'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Occupied']),
            ]);
            $this->publishToMqtt($user->rfid_number, $type, 'granted');
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
                'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Available']),
            ]);
            $this->publishToMqtt($user->rfid_number, $type, 'granted');
            break;
    }

    // Finalize attendance for the day
    $attendance->calculateAndSaveStatusAndRemarks();
    AttendanceRecorded::dispatch($attendance);

    $this->info('Attendance recorded successfully.');
}



    // Helper method to publish the access result to MQTT
    private function publishToMqtt($rfid_number, $type, $status)
    {
        $message = json_encode([
            'rfid_number' => $rfid_number,
            'type' => $type,
            'status' => $status,
        ]);

        try {
            $this->mqtt->publish('laravel_topic_response', $message, 0); // QoS 0 for simplicity
            $this->info("Published access result: {$message}");
        } catch (\Exception $e) {
            $this->error("Failed to publish to MQTT: " . $e->getMessage());
        }
    }
}