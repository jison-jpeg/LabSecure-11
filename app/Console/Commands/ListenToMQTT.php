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

    public function handle()
    {
        // Get MQTT details from .env
        $brokerAddress = env('MQTT_BROKER_ADDRESS', '127.0.0.1');
        $brokerPort = env('MQTT_PORT', 1883);

        // Create MQTT client
        $mqtt = new MqttClient($brokerAddress, $brokerPort, 'laravel_mqtt_listener');
        $connectionSettings = new ConnectionSettings();

        // Connect to the broker
        $mqtt->connect($connectionSettings, true);
        $this->info("Connected to MQTT Broker at {$brokerAddress}. Port: {$brokerPort}");

        // Subscribe to the topic
        $mqtt->subscribe('laravel_topic', function ($topic, $message) {
            $this->info("Received '{$message}' on topic '{$topic}'");

            // Decode the JSON message (assuming it contains rfid_number and type)
            $payload = json_decode($message, true);
            if (isset($payload['rfid_number']) && isset($payload['type'])) {
                $this->handleLaboratoryAccess($payload['rfid_number'], $payload['type']);
            } else {
                $this->error('Invalid message format.');
            }
        }, 0);

        // Keep the MQTT loop running
        $mqtt->loop(true);
        $mqtt->disconnect();
    }

    // This method handles the laboratory access using the same logic as your API controller
    private function handleLaboratoryAccess($rfid_number, $type)
    {
        // Find the user by RFID
        $user = User::where('rfid_number', $rfid_number)->first();
        if (!$user) {
            $this->error('User not found');
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
                $this->publishAttendanceSuccess();  // Publish MQTT message for successful attendance
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
    }

    // Handle access for users with active schedules
    private function handleScheduledUserAccess($user, $type)
    {
        // Find the current schedule based on time
        $currentTime = Carbon::now();
        $schedule = Schedule::where('start_time', '<=', $currentTime)
                            ->where('end_time', '>=', $currentTime)
                            ->first();

        if (!$schedule) {
            $this->error('No active schedule found for this time');
            return;
        }

        $laboratory = $schedule->laboratory;

        // Handle attendance
        $currentDate = Carbon::now()->toDateString();
        $attendance = Attendance::firstOrCreate([
            'user_id' => $user->id,
            'date' => $currentDate,
            'schedule_id' => $schedule->id,
        ]);

        switch ($type) {
            case 'entrance':
                // Log the entrance action
                $attendance->sessions()->create(['time_in' => Carbon::now()]);
                $laboratory->update(['status' => 'Occupied']);
                LaboratoryStatusUpdated::dispatch($laboratory);

                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'check_in',
                    'model' => 'Attendance',
                    'model_id' => $attendance->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Occupied']),
                ]);
                $this->publishAttendanceSuccess();  // Publish MQTT message for successful attendance
                break;

            case 'exit':
                // Log the exit action
                $lastSession = $attendance->sessions()->whereNull('time_out')->latest('time_in')->first();
                if ($lastSession) {
                    $lastSession->update(['time_out' => Carbon::now()]);
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
                break;
        }

        // Finalize attendance for the day
        $attendance->calculateAndSaveStatusAndRemarks();
        AttendanceRecorded::dispatch($attendance);

        $this->info('Attendance recorded successfully.');
    }

    // Publish attendance success message to MQTT
    private function publishAttendanceSuccess()
    {
        // Get MQTT details from .env
        $brokerAddress = env('MQTT_BROKER_ADDRESS', '127.0.0.1');
        $brokerPort = env('MQTT_PORT', 1883);

        // Create MQTT client
        $mqtt = new MqttClient($brokerAddress, $brokerPort, 'laravel_mqtt_publisher');
        $connectionSettings = new ConnectionSettings();

        // Connect to the broker and publish the success message
        $mqtt->connect($connectionSettings, true);
        $mqtt->publish('laravel_topic', 'attendance_successful');
        $mqtt->disconnect();

        $this->info('Attendance success message published to MQTT.');
    }
}
