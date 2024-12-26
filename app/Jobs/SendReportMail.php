<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Device;
use App\Models\NodeMaster;
use App\Models\NodeErrorLogs;
use App\Models\MachineMaster;
use App\Models\MachineLogs;
use App\Models\MachineStatus;
use App\Models\TempMachineStatus;
use App\Models\PickCalculation;
use App\Mail\ReportMail;
use Carbon\Carbon;
use Exception;

class SendReportMail implements ShouldQueue
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
            \Log::info("Report sent successfully for type: {$this->reportType}");
        } catch (Exception $e) {
            \Log::error("Report sending failed: {$e->getMessage()}");
            throw $e;
        }
    }

    protected function sendReports(string $filter)
    {
        $query = MachineStatus::with('user');

        switch ($filter) {
            case 'daily':
                // $query->whereDate('machine_status.created_at', '2024-12-11');
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

        $result = $query->distinct()->pluck('user_id');
        $users = User::whereIn('id', $result)->get();

        if ($users->isEmpty()) {
            \Log::info("No data found for report type: {$filter}");
            return;
        }

        foreach ($users as $key => $user) {
            $fileName = $this->generateReport($filter, $user->id);
            $this->sendOnEmail($user, $filter, $fileName);
            $this->sendOnWhatsApp($user, $filter, $fileName);
        }
    }

    /**
     * Send report via email.
     */
    private function sendOnEmail(object $user, string $reportType, string $fileName)
    {
        $mailData = [
            'companyName' => ucwords(str_replace("_", " ", config('app.name', 'TARASVAT Industrial Electronics'))),
            'reportType' => ucfirst($reportType),
            'reportDate' => now()->toDateString(),
            'reportLink' => route('generate.report', [$reportType, $user->id]),
            // 'reportLink' => asset('/') . "reports/html/$fileName",
        ];

        Mail::to($user->email)->send(new ReportMail($mailData, $fileName));
    }

    /**
     * Placeholder for sending report via WhatsApp.
     */
    private function sendOnWhatsApp(object $user, string $reportType, string $fileName)
    {
        // Add WhatsApp sending logic here
    }

    private function generateReport($filter, $userId)
    {
        $previousLabel = '';
        $currentLabel = '';
        
        $queryPrevious = MachineStatus::
                selectRaw("node_master.name, machine_status.user_id, machine_master.machine_display_name, SUM(machine_status.speed) as speed, SUM(machine_status.efficiency) as efficiency, SUM(machine_status.no_of_stoppage) as no_of_stoppage, SUM(pick_calculations.shift_pick) as shift_pick")
                ->leftJoin('node_master', 'machine_status.node_id', '=', 'node_master.id')
                ->leftJoin('machine_master', 'machine_status.machine_id', '=', 'machine_master.id')
                ->leftJoin('pick_calculations', 'machine_status.id', '=', 'pick_calculations.machine_status_id')
                ->where('machine_status.user_id', $userId);

        $queryCurrent = clone $queryPrevious;

        switch ($filter) {
            case 'daily':
                // $queryPrevious->whereDate('machine_status.created_at', '2024-12-10');
                // $queryCurrent->whereDate('machine_status.created_at', '2024-12-11');
                $queryPrevious->whereDate('machine_status.created_at', Carbon::yesterday());
                $queryCurrent->whereDate('machine_status.created_at', Carbon::today());
    
                $previousLabel = "Yesterday " . Carbon::yesterday()->format('d M Y');
                $currentLabel = "Today " . Carbon::today()->format('d M Y');
                break;
    
            case 'weekly':
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    
                $previousLabel = "Last Week " . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->subWeek()->endOfWeek()->format('d M Y');
                $currentLabel = "Current Week " . Carbon::now()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y');
                break;
    
            case 'monthly':
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
    
                $previousLabel = "Last Month " . Carbon::now()->subMonth()->format('M Y');
                $currentLabel = "Current Month " . Carbon::now()->format('M Y');
                break;
    
            case 'yearly':
                $queryPrevious->whereYear('machine_status.created_at', Carbon::now()->subYear()->year);
                $queryCurrent->whereYear('machine_status.created_at', Carbon::now()->year);
    
                $previousLabel = "Last Year " . Carbon::now()->subYear()->year;
                $currentLabel = "Current Year " . Carbon::now()->year;
                break;
    
            default:
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    
                $previousLabel = "Last Week " . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->subWeek()->endOfWeek()->format('d M Y');
                $currentLabel = "Current Week " . Carbon::now()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y');
                break;
        }

        $previous = $queryPrevious->groupBy('machine_status.machine_id', 'node_master.name', 'machine_status.user_id', 'machine_master.machine_display_name')->get();
        $current = $queryCurrent->groupBy('machine_status.machine_id', 'node_master.name', 'machine_status.user_id', 'machine_master.machine_display_name')->get();

        $totalLoop = max(count($previous->toArray()), count($current->toArray()));
        if ($totalLoop <= 0) {
            return response()->json(['status' => false, 'message' => 'No report found, or the report data has been deleted.'], 404);
        }

        $resultArray = [];
        for ($i = 0; $i < $totalLoop; $i++) {
            
            $user = $previous[$i]->user_id ?? $current[$i]->user_id;
            $node = $previous[$i]->name ?? $current[$i]->name;
            $machineDisplayName = $previous[$i]->machine_display_name ?? $current[$i]->machine_display_name;
        
            // Build metrics with previous and current values
            $speed = [
                'label' => $machineDisplayName,
                'previous' => round((float)$this->getValue($previous, $i, 'speed'), 2),
                'current' => round((float)$this->getValue($current, $i, 'speed'), 2),
            ];
            $efficiency = [
                'label' => $machineDisplayName,
                'previous' => round((float)$this->getValue($previous, $i, 'efficiency'), 2),
                'current' => round((float)$this->getValue($current, $i, 'efficiency'), 2),
            ];
            $no_of_stoppage = [
                'label' => $machineDisplayName,
                'previous' => round((float)$this->getValue($previous, $i, 'no_of_stoppage'), 2),
                'current' => round((float)$this->getValue($current, $i, 'no_of_stoppage'), 2),
            ];
            $shift_pick = [
                'label' => $machineDisplayName,
                'previous' => round((float)$this->getValue($previous, $i, 'shift_pick'), 2),
                'current' => round((float)$this->getValue($current, $i, 'shift_pick'), 2),
            ];
        
            // Organize the result array
            $resultArray[$user][$node]['label'] = $node . ' (' . ucwords($filter) . ')';
            $resultArray[$user][$node]['speed'][] = $speed;
            $resultArray[$user][$node]['efficiency'][] = $efficiency;
            $resultArray[$user][$node]['no_of_stoppage'][] = $no_of_stoppage;
            $resultArray[$user][$node]['shift_pick'][] = $shift_pick;
        }

        foreach ($resultArray as $key => $value) {
            $htmlData = view('report.pdf', compact('value', 'previousLabel', 'currentLabel'))->render();
            $fileName = time() . "-$filter-report-$userId.html";
            $filePath = public_path("reports/html/$fileName");
            file_put_contents($filePath, $htmlData);
            return $fileName;
            exit;
        }
    }

    private function getValue($data, $index, $key, $default = 0) {
        return isset($data[$index]) ? $data[$index]->$key : $default;
    }
}
