<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\MachineStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Services\WhatsappAPIService;

class MachineStopAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $datas = MachineStatus::whereDate('shift_date', Carbon::today())
                ->whereHas('machine', function($query) {
                    $query->where('priority', 1);
                })->groupBy('machine_status.machine_id')->orderBy('id', 'desc')->get();

            if ($datas->isEmpty()) {
                Log::info("No machine stop data found for today.");
                return 0;
            }

            $storeResponse = [];

            foreach ($datas as $data) {
                $deviceDatetime = Carbon::parse($data->device_datetime);
                $machineDatetime = Carbon::parse($data->machine_datetime);
                $machine = $data->machine;
                $user = $machine?->node?->device?->user;
                $machineName = $machine->display_name ?? $machine->name;
                $lastStopTime = $deviceDatetime->format('d/m/Y h:i:s A');
                $downtime = self::formatTime($machineDatetime->diffInSeconds($deviceDatetime));
                $diffInMins = $machineDatetime->diffInMinutes($deviceDatetime);

                if ($diffInMins < 30) {
                    Log::info("Machine has stopped for less than 30 minutes. Machine -> $machineName, diffInMins -> $diffInMins");
                    continue;
                }

                

                if (empty($machine) || empty($user)) {
                    Log::warning("Missing machine or user info. Skipping alert. Machine: {$machine?->name}, User phone: {$user?->phone_number}");
                    continue;
                }

               

                $whatsappAPIService = new WhatsAppAPIService();
                $sent = $whatsappAPIService->send_machineStopAlert($user, $machineName, $lastStopTime, $downtime);

                $storeResponse[] = [
                    'user' => $user->email ?? $user->phone_number,
                    'machine' => $machineName,
                    'sent' => $sent,
                ];
            }

            Log::info("Machine stop alerts dispatched.", $storeResponse);
            return 1;

        } catch (\Exception $e) {
            Log::error('MachineStopAlert job failed: ' . $e->getMessage());
            return 0;
        }
    }

    private static function formatTime($seconds)
    {
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $secs = $seconds % 60;
        $mins = $minutes % 60;

        return ($hours > 0 ? "{$hours}hr " : "") .
            ($mins > 0 ? "{$mins}min " : "") .
            ($secs > 0 ? "{$secs}sec" : "");
    }
}
