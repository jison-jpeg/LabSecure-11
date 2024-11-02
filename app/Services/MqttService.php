<?php
// app/Services/MqttService.php

namespace App\Services;

use App\Events\AttendanceRecorded;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\User;
use App\Models\Laboratory;
use App\Models\TransactionLog;
use App\Events\LaboratoryStatusUpdated;
use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;

class MqttService
{
    protected $mqttClient;
    protected $connectionSettings;

    public function __construct()
    {
        $this->mqttClient = new MqttClient(env('MQTT_BROKER_ADDRESS'), env('MQTT_PORT'), 'laravel_client_' . uniqid());
        $this->connectionSettings = (new ConnectionSettings())
            ->setUsername(env('MQTT_USERNAME'))
            ->setPassword(env('MQTT_PASSWORD'));

        $this->connect();
        $this->subscribeToTopics();
    }

    protected function connect()
    {
        $this->mqttClient->connect($this->connectionSettings, true);
    }

    protected function subscribeToTopics()
    {
        $this->mqttClient->subscribe('rfid/access', function ($topic, $message) {
            $data = json_decode($message, true);
            $this->handleLaboratoryAccess($data);
        }, 0);

        $this->mqttClient->loop(true);
    }

    public function handleLaboratoryAccess($data)
    {
        $user = User::where('rfid_number', $data['rfid_number'])->first();
        if (!$user) {
            return ['message' => 'User not found', 'status' => 404];
        }

        if (in_array($user->role->name, ['admin', 'it_support'])) {
            return $this->handlePersonnelAccess($user, $data['type']);
        }

        return $this->handleScheduledUserAccess($user, $data['type']);
    }

    private function handlePersonnelAccess($user, $type)
    {
        $laboratory = Laboratory::where('id', 1)->first();  // Adjust logic to find the correct laboratory

        switch ($type) {
            case 'entrance':
                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'in',
                    'model' => 'Laboratory',
                    'model_id' => $laboratory->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Occupied']),
                ]);
                LaboratoryStatusUpdated::dispatch($laboratory);
                break;

            case 'exit':
                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'out',
                    'model' => 'Laboratory',
                    'model_id' => $laboratory->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Available']),
                ]);
                LaboratoryStatusUpdated::dispatch($laboratory);
                break;
        }

        return ['message' => 'Access granted.', 'laboratory' => $laboratory];
    }

    private function handleScheduledUserAccess($user, $type)
    {
        $currentTime = Carbon::now();
        $schedule = Schedule::where('start_time', '<=', $currentTime)
                            ->where('end_time', '>=', $currentTime)
                            ->first();

        if (!$schedule) {
            return ['message' => 'No active schedule found for this time', 'status' => 404];
        }

        $laboratory = $schedule->laboratory;
        $currentDate = Carbon::now()->toDateString();
        $attendance = Attendance::firstOrCreate([
            'user_id' => $user->id,
            'date' => $currentDate,
            'schedule_id' => $schedule->id,
        ]);

        switch ($type) {
            case 'entrance':
                $attendance->sessions()->create(['time_in' => Carbon::now()]);
                $laboratory->update(['status' => 'Occupied']);
                LaboratoryStatusUpdated::dispatch($laboratory);

                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'in',
                    'model' => 'Attendance',
                    'model_id' => $attendance->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Occupied']),
                ]);
                break;

            case 'exit':
                $lastSession = $attendance->sessions()->whereNull('time_out')->latest('time_in')->first();
                if ($lastSession) {
                    $lastSession->update(['time_out' => Carbon::now()]);
                }
                $laboratory->update(['status' => 'Available']);
                LaboratoryStatusUpdated::dispatch($laboratory);

                TransactionLog::create([
                    'user_id' => $user->id,
                    'action' => 'out',
                    'model' => 'Attendance',
                    'model_id' => $attendance->id,
                    'details' => json_encode(['rfid_number' => $user->rfid_number, 'laboratory_status' => 'Available']),
                ]);
                break;
        }

        AttendanceRecorded::dispatch($attendance);
        return ['message' => 'Attendance recorded successfully', 'data' => $attendance->load('sessions')];
    }
}
