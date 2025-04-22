<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MachineStopAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'machine:stop-alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Triggers the machine stop alert endpoint and logs the response for monitoring.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = env('LOCAL_BASE_URL') . 'api/machine/report/machine_stop_alert';

        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                $error = curl_error($curl);
                Log::error("MachineStopAlert failed: $error");
            } else {
                Log::info("MachineStopAlert success: $response");
            }

            curl_close($curl);
        } catch (\Exception $e) {
            Log::error('MachineStopAlert Exception: ' . $e->getMessage());
        }

        return 0;
    }
}
