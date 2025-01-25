<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\MachineStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportType;
    public $timeout = 3600;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $reportType)
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
        $this->reportType = $reportType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->sendReports($this->reportType);
            Log::info("Report sent successfully for type: {$this->reportType}");
        } catch (Exception $e) {
            Log::error("Report sending failed: {$e->getMessage()}");
            throw $e;
        }
    }

    protected function sendReports(string $filter)
    {
        $query = MachineStatus::with('user');

        switch ($filter) {
            case 'daily':
                $query->whereDate('machine_status.created_at', '2024-12-11');
                // $query->whereDate('machine_status.created_at', Carbon::today());
                break;
            case 'weekly':
                $query->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'monthly':
                $query->whereBetween('machine_status.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
            case 'yearly':
                $query->whereYear('machine_status.created_at', Carbon::now()->year);
                break;
            default:
                $query->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
        }

        $result = $query->distinct()->pluck('user_id');
        $users = User::whereIn('id', $result)->get();

        if ($users->isEmpty()) {
            Log::info("No data found for report type: {$filter}");
            return;
        }

        Log::info("users: {$users}");

        foreach ($users as $key => $user) {
            $userId = $user->id;
            $this->generateReportApi($filter, $userId);
        }
    }

    protected function generateReportApi($filter, $userId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('GENERATE_REPORT_BASE_URL') . "$filter/$userId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
