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

class Report implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $reportType;
    protected $reportFormat;
    public $timeout = 3600;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $type, string $reportType, string $reportFormat)
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
        $this->type = $type;
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
            $this->generateReports($this->type, $this->reportType, $this->reportFormat);
            Log::info("Report sent successfully for type: {$this->reportType} and format: {$this->reportFormat}");
        } catch (Exception $e) {
            Log::error("Report sending failed: {$e->getMessage()}");
            throw new Exception($e->getMessage());
        }
    }

    protected function generateReports(string $type, string $filter, string $format)
    {
        $query = MachineStatus::with('machine.node.device.user');

        switch ($filter) {
            case 'daily':
                $query->whereDate('machine_status.created_at', Carbon::today());
                // $query->whereDate('machine_status.created_at', '2025-04-01');
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
            $this->generateReportApi($type, $filter, $format, $userId);
        }
    }

    protected function generateReportApi($type, $filter, $format, $userId)
    {
        $url = env('GENERATE_REPORT_BASE_URL', '');
        $data = [
            'type' => $type,
            'report_type' => $filter,
            'report_format' => $format,
            'user_id' => $userId,
        ];

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
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: Laravel-cURL'
            ],
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        Log::info("generateReportApi URL: {$url}, payload: " . json_encode($data) . ", response: {$response}, error: {$error}");
        return $response;
    }
}
