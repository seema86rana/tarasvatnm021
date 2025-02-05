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
    protected $reportFormat;
    public $timeout = 3600;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $reportType, string $reportFormat)
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
        $this->reportType = $reportType;
        $this->reportFormat = $reportFormat;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->sendReports($this->reportType, $this->reportFormat);
            Log::info("Report sent successfully for type: {$this->reportType}");
        } catch (Exception $e) {
            Log::error("Report sending failed: {$e->getMessage()}");
            throw new Exception($e->getMessage());
        }
    }

    protected function sendReports(string $filter, string $format)
    {
        $query = MachineStatus::with('machine.node.device.user');

        switch ($filter) {
            case 'daily':
                // $query->whereDate('machine_status.created_at', '2025-01-28');
                $query->whereDate('machine_status.created_at', Carbon::today());
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

        $userIds = $query->get()->pluck('machine.node.device.user.id')->unique();
        $users = User::whereIn('id', $userIds)->get();

        if ($users->isEmpty()) {
            Log::info("No data found for report type: {$filter}");
            return;
        }

        Log::info("users: {$users}");

        foreach ($users as $user) {
            $userId = $user->id;
            $this->generateReportApi($filter, $format, $userId);
        }
    }

    protected function generateReportApi($filter, $format, $userId)
    {
        $url = env('GENERATE_REPORT_BASE_URL') . "{$filter}/{$format}/{$userId}";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: Laravel-cURL'
            ],
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        Log::info("generateReportApi URL: " . $url);
        Log::info("generateReportApi Response: " . ($error ?: $response));

        return $response;
    }
}
