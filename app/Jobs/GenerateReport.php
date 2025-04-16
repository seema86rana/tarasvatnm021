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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TempMachineStatus;

class GenerateReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $userId;
    protected $reportType;
    protected $reportFormat;

    public $timeout = 3600;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $type, int $userId, string $reportType, string $reportFormat)
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
        $this->type = $type;
        $this->userId = $userId;
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
            if ($this->type == 'machine_status') {
                $this->generateMachineStatusReport($this->type, $this->reportType, $this->reportFormat, $this->userId);
                Log::info("Report generate successfully for type: {$this->type}, user_id: {$this->userId}, reportType: {$this->reportType}, reportFormat: {$this->reportFormat}");
            }

            if ($this->type == 'machine_stop') {
                $this->generateMachineStopReport($this->type, $this->reportType, $this->reportFormat, $this->userId);
                Log::info("Report generate successfully for type: {$this->type}, user_id: {$this->userId}, reportType: {$this->reportType}, reportFormat: {$this->reportFormat}");
            }
            
            return response()->json(['success' => true, 'message' => 'Report generated successfully.'], 200);
        } catch (Exception $e) {
            Log::error("Report sending failed: {$e->getMessage()}");
            throw new Exception($e->getMessage());
        }
    }

    public function generateMachineStatusReport($type, $reportType, $reportFormat, $userId)
    {
        $previousLabel = '';
        $currentLabel = '';
        $previousDay = '';
        $currentDay = '';

        $userDetail = User::findOrFail($userId);
        
        $queryPrevious = MachineStatus::query()
            ->selectRaw("
                users.id AS user_id, devices.name AS device_name, node_master.name AS node_name, machine_master.name AS machine_name, machine_status.shift_name AS shift_name,
                DATE_FORMAT(MIN(machine_status.shift_start_datetime), '%h:%i %p') AS shift_start,
                DATE_FORMAT(MAX(machine_status.shift_end_datetime), '%h:%i %p') AS shift_end,
                SUM(machine_status.speed) AS speed,
                SUM(machine_status.efficiency) AS efficiency,
                SUM(machine_status.no_of_stoppage) AS stoppage,
                SUM(pick_calculations.shift_pick) AS pick,
                COUNT(users.id) AS total_record
            ")
            ->join('machine_master', 'machine_status.machine_id', '=', 'machine_master.id')
            ->join('node_master', 'machine_master.node_id', '=', 'node_master.id')
            ->join('devices', 'node_master.device_id', '=', 'devices.id')
            ->join('users', 'devices.user_id', '=', 'users.id')
            ->join('pick_calculations', 'machine_status.id', '=', 'pick_calculations.machine_status_id')
            ->where('users.id', $userId)
            ->groupBy('users.id', 'devices.name', 'node_master.name', 'machine_master.name', 'machine_status.machine_id', 'machine_status.shift_name');

        $queryCurrent = clone $queryPrevious;

        switch ($reportType) {
            case 'daily':
                // $queryPrevious->whereDate('machine_status.shift_date', '2025-04-01');
                // $queryCurrent->whereDate('machine_status.shift_date', '2025-04-02');
                $queryPrevious->whereDate('machine_status.shift_date', Carbon::yesterday()->subDay());
                $queryCurrent->whereDate('machine_status.shift_date', Carbon::yesterday());
                
                $previousLabel = "Yesterday " . Carbon::yesterday()->subDay()->format('d M Y');
                $currentLabel = "Today " . Carbon::yesterday()->format('d M Y');

                $previousDay = Carbon::yesterday()->subDay()->format('d/m/Y');
                $currentDay = Carbon::yesterday()->format('d/m/Y');
                break;

            case 'weekly':
                $queryPrevious->whereBetween('machine_status.shift_date', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                $queryCurrent->whereBetween('machine_status.shift_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                
                $previousLabel = "Last Week " . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->subWeek()->endOfWeek()->format('d M Y');
                $currentLabel = "Current Week " . Carbon::now()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y');
                
                $previousDay = Carbon::now()->subWeek()->startOfWeek()->format('d M Y');
                $firstDayOfWeekPrevious = Carbon::parse($previousDay)->startOfWeek()->format('d/m/Y');
                $lastDayOfWeekPrevious = Carbon::parse($previousDay)->endOfWeek()->format('d/m/Y');
                $previousDay = $firstDayOfWeekPrevious . " - " . $lastDayOfWeekPrevious;
    
                $currentDay = Carbon::now()->endOfWeek()->format('d M Y');
                $firstDayOfWeekCurrent = Carbon::parse($currentDay)->startOfWeek()->format('d/m/Y');
                $lastDayOfWeekCurrent = Carbon::parse($currentDay)->endOfWeek()->format('d/m/Y');
                $currentDay = $firstDayOfWeekCurrent . " - " . $lastDayOfWeekCurrent;
                break;

            case 'monthly':
                $queryPrevious->whereBetween('machine_status.shift_date', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                $queryCurrent->whereBetween('machine_status.shift_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                
                $previousLabel = "Last Month " . Carbon::now()->subMonth()->format('M Y');
                $currentLabel = "Current Month " . Carbon::now()->format('M Y');

                $previousDay = Carbon::now()->subMonth()->format('M Y');
                $firstDayOfMonthPrevious = Carbon::parse($previousDay)->startOfMonth()->format('d/m/Y');
                $lastDayOfMonthPrevious = Carbon::parse($previousDay)->endOfMonth()->format('d/m/Y');
                $previousDay = $firstDayOfMonthPrevious . " - " . $lastDayOfMonthPrevious;
    
                $currentDay = Carbon::now()->format('M Y');
                $firstDayOfMonthCurrent = Carbon::parse($currentDay)->startOfMonth()->format('d/m/Y');
                $lastDayOfMonthCurrent = Carbon::parse($currentDay)->endOfMonth()->format('d/m/Y');
                $currentDay = $firstDayOfMonthCurrent . " - " . $lastDayOfMonthCurrent;
                break;

            case 'yearly':
                $queryPrevious->whereYear('machine_status.shift_date', Carbon::now()->subYear()->year);
                $queryCurrent->whereYear('machine_status.shift_date', Carbon::now()->year);
                
                $previousLabel = "Last Year " . Carbon::now()->subYear()->year;
                $currentLabel = "Current Year " . Carbon::now()->year;

                $previousDay = Carbon::now()->subYear()->year;
                $firstDayOfYearPrevious = Carbon::parse($previousDay)->startOfYear()->format('d/m/Y');
                $lastDayOfYearPrevious = Carbon::parse($previousDay)->endOfYear()->format('d/m/Y');
                $previousDay = $firstDayOfYearPrevious . " - " . $lastDayOfYearPrevious;
                
                $currentDay = Carbon::now()->year;
                $firstDayOfYearCurrent = Carbon::parse($currentDay)->startOfYear()->format('d/m/Y');
                $lastDayOfYearCurrent = Carbon::parse($currentDay)->endOfYear()->format('d/m/Y');
                $currentDay = $firstDayOfYearCurrent . " - " . $lastDayOfYearCurrent;
                break;

            default:
                $queryPrevious->whereBetween('machine_status.shift_date', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                $queryCurrent->whereBetween('machine_status.shift_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                
                $previousLabel = "Last Week " . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->subWeek()->endOfWeek()->format('d M Y');
                $currentLabel = "Current Week " . Carbon::now()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y');
                
                $previousDay = Carbon::now()->subWeek()->startOfWeek()->format('d M Y');
                $firstDayOfWeekPrevious = Carbon::parse($previousDay)->startOfWeek()->format('d/m/Y');
                $lastDayOfWeekPrevious = Carbon::parse($previousDay)->endOfWeek()->format('d/m/Y');
                $previousDay = $firstDayOfWeekPrevious . " - " . $lastDayOfWeekPrevious;
    
                $currentDay = Carbon::now()->endOfWeek()->format('d M Y');
                $firstDayOfWeekCurrent = Carbon::parse($currentDay)->startOfWeek()->format('d/m/Y');
                $lastDayOfWeekCurrent = Carbon::parse($currentDay)->endOfWeek()->format('d/m/Y');
                $currentDay = $firstDayOfWeekCurrent . " - " . $lastDayOfWeekCurrent;
                break;
        }

        $previous = $queryPrevious->get();
        $current = $queryCurrent->get();

        $firstRec = $queryPrevious->first();
        if (!$firstRec) {
            $firstRec = $queryCurrent->first();
        }

        $totalLoop = max(count($previous), count($current));
        if ($totalLoop <= 0) {
            Log::info("No report found, or the report data has been deleted.");
            return response()->json(['status' => false, 'message' => 'No report found, or the report data has been deleted.'], 404);
        }

        if ($reportFormat == env('REPORT_FORMAT', 'table')) {
            $reportData = $this->generateMachineStatusReportTable($previous, $current, $totalLoop);
            $htmlFile = view('report.machine_status.table', compact('reportData', 'reportType', 'firstRec', 'previousDay', 'currentDay', 'userDetail'))->render();
        }
        else {
            $reportData = $this->generateMachineStatusReportChart($reportType, $previous, $current, $totalLoop);
            $htmlFile = view('report.machine_status.chart', compact('reportData', 'previousLabel', 'currentLabel'))->render();
        }

        try {
            // Generate HTML content
            $fileName = time() . "-{$reportType}-{$reportFormat}-machine_status-report-$userId.html";
            $filePath = public_path("reports/html/$fileName");
    
            // Save HTML file locally
            if (!file_put_contents($filePath, $htmlFile)) {
                throw new Exception("Failed to save HTML file locally at $filePath.");
            }

            if ($reportFormat == env('REPORT_FORMAT', 'table')) {
                $pdfFilePath = $this->generateTablePdf($filePath);
            }
            else {
                Log::alert("Report format is not supported for generating PDF file.");
                throw new Exception("Report format is not supported for generating PDF file.");
            }
    
            Log::info("PDF file path: " . ($pdfFilePath ?? 'Not Found'));
            $this->sendReportApi($type, $userId, $reportType, $pdfFilePath);

        } catch (Exception $e) {
            Log::error("Error processing report for user ID: $userId, Filter: $reportType. Message: " . $e->getMessage());
            throw new Exception("Error processing report for User ID: $userId - " . $e->getMessage());
        }

        return true;
    }

    protected function generateMachineStatusReportTable($previous, $current, $totalLoop)
    {
        $groupedData = [];
        for ($i = 0; $i < $totalLoop; $i++) {

            $nodeName = ($previous[$i]->node_name ?? $current[$i]->node_name);
            $shiftName = str_replace(" ", '', ($previous[$i]->shift_name ?? $current[$i]->shift_name));
            $preMachineName = ($previous[$i]->machine_name ?? $current[$i]->machine_name) . ' (Last)';
            $curMachineName = ($previous[$i]->machine_name ?? $current[$i]->machine_name) . ' (Current)';

            $groupedData[$nodeName]['total_record'][$shiftName] = ($previous[$i]->total_record ?? $current[$i]->total_record);
            $groupedData[$nodeName][$shiftName] = ($previous[$i]->shift_start ?? $current[$i]->shift_start) . ' - ' . ($previous[$i]->shift_end ?? $current[$i]->shift_end);
            
            $groupedData[$nodeName]['efficiency'][$shiftName][$preMachineName] = (round((float)$this->getValue($previous, $i, 'efficiency'), 2));
            $groupedData[$nodeName]['efficiency'][$shiftName][$curMachineName] = (round((float)$this->getValue($current, $i, 'efficiency'), 2));

            $groupedData[$nodeName]['speed'][$shiftName][$preMachineName] = (round((float)$this->getValue($previous, $i, 'speed'), 2));
            $groupedData[$nodeName]['speed'][$shiftName][$curMachineName] = (round((float)$this->getValue($current, $i, 'speed'), 2));
            
            $groupedData[$nodeName]['pick'][$shiftName][$preMachineName] = (round((float)$this->getValue($previous, $i, 'pick'), 2));
            $groupedData[$nodeName]['pick'][$shiftName][$curMachineName] = (round((float)$this->getValue($current, $i, 'pick'), 2));
            
            $groupedData[$nodeName]['stoppage'][$shiftName][$preMachineName] = (round((float)$this->getValue($previous, $i, 'stoppage'), 2));
            $groupedData[$nodeName]['stoppage'][$shiftName][$curMachineName] = (round((float)$this->getValue($current, $i, 'stoppage'), 2));
        }

        return $groupedData;        
    }

    protected function generateMachineStatusReportChart($reportType, $previous, $current, $totalLoop)
    {
        $groupedData = [];
        for ($i = 0; $i < $totalLoop; $i++) {
            
            $node = $previous[$i]->node_name ?? $current[$i]->node_name;
            $machineDisplayName = $previous[$i]->machine_name ?? $current[$i]->machine_name;
            $shiftName = str_replace(" ", '', ($previous[$i]->shift_name ?? $current[$i]->shift_name));
        
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
            $groupedData[$node][$shiftName]['label'] = $node . ' (' . ucwords($reportType) . ')' . ' (' . ($previous[$i]->shift_start ?? $current[$i]->shift_start) . ' - ' . ($previous[$i]->shift_end ?? $current[$i]->shift_end) . ')';
            $groupedData[$node][$shiftName]['speed'][] = $speed;
            $groupedData[$node][$shiftName]['efficiency'][] = $efficiency;
            $groupedData[$node][$shiftName]['no_of_stoppage'][] = $no_of_stoppage;
            $groupedData[$node][$shiftName]['shift_pick'][] = $shift_pick;
        }

        return $groupedData;
    }

    public function generateMachineStopReport($type, $reportType, $reportFormat, $userId)
    {
        $currentDay = '';
        $userDetail = User::findOrFail($userId);
        
        $query = TempMachineStatus::query()
            ->selectRaw("
                users.id AS user_id,
                devices.name AS device_name,
                machine_master.name AS machine_name,
                temp_machine_status.shift_name AS shift_name,
                temp_machine_status.shift_date AS shift_date,
                DATE_FORMAT(MIN(temp_machine_status.shift_start_datetime), '%h:%i %p') AS shift_start,
                DATE_FORMAT(MAX(temp_machine_status.shift_end_datetime), '%h:%i %p') AS shift_end,
                temp_machine_status.no_of_stoppage AS stoppage,
                temp_machine_status.machine_id AS machine_id,
                MIN(temp_machine_status.last_stop) AS first_stopage,
                MAX(temp_machine_status.last_stop) AS last_stopage,
                COUNT(temp_machine_status.id) AS total_record,
                MIN(temp_machine_status.machine_datetime) AS first_machine_datetime,
                MAX(temp_machine_status.machine_datetime) AS last_machine_datetime,
                MIN(temp_machine_status.device_datetime) AS first_device_datetime,
                MAX(temp_machine_status.device_datetime) AS last_device_datetime
            ")
            ->join('machine_master', 'temp_machine_status.machine_id', '=', 'machine_master.id')
            ->join('node_master', 'machine_master.node_id', '=', 'node_master.id')
            ->join('devices', 'node_master.device_id', '=', 'devices.id')
            ->join('users', 'devices.user_id', '=', 'users.id')
            ->where('users.id', $userId)
            ->where('temp_machine_status.status', 0)
            ->groupBy(
                'users.id',
                'devices.name',
                'node_master.name',
                'machine_master.name',
                'temp_machine_status.machine_id',
                'temp_machine_status.shift_name',
                'temp_machine_status.shift_date',
                'temp_machine_status.no_of_stoppage'
            )
            ->orderBy('temp_machine_status.machine_id')
            ->orderBy('temp_machine_status.shift_name')
            ->orderBy('temp_machine_status.no_of_stoppage');

        switch ($reportType) {
            case 'daily':
                // $query->whereDate('temp_machine_status.shift_date', '2025-04-02');
                $query->whereDate('temp_machine_status.shift_date', Carbon::yesterday());
    
                $previousDay = Carbon::yesterday()->format('d/m/Y');
                $currentDay = Carbon::today()->format('d/m/Y');

                $currentDay = "{$previousDay} - {$currentDay}";
                break;
    
            case 'weekly':
                $query->whereBetween('temp_machine_status.shift_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    
                $currentDay = Carbon::now()->endOfWeek()->format('d M Y');
                $firstDayOfWeekCurrent = Carbon::parse($currentDay)->startOfWeek()->format('d/m/Y');
                $lastDayOfWeekCurrent = Carbon::parse($currentDay)->endOfWeek()->format('d/m/Y');
                $currentDay = $firstDayOfWeekCurrent . " - " . $lastDayOfWeekCurrent;
                break;
    
            case 'monthly':
                $query->whereBetween('temp_machine_status.shift_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
    
                $currentDay = Carbon::now()->format('M Y');
                $firstDayOfMonthCurrent = Carbon::parse($currentDay)->startOfMonth()->format('d/m/Y');
                $lastDayOfMonthCurrent = Carbon::parse($currentDay)->endOfMonth()->format('d/m/Y');
                $currentDay = $firstDayOfMonthCurrent . " - " . $lastDayOfMonthCurrent;
                break;
    
            case 'yearly':
                $query->whereYear('temp_machine_status.shift_date', Carbon::now()->year);
    
                $currentDay = Carbon::now()->year;
                $firstDayOfYearCurrent = Carbon::parse($currentDay)->startOfYear()->format('d/m/Y');
                $lastDayOfYearCurrent = Carbon::parse($currentDay)->endOfYear()->format('d/m/Y');
                $currentDay = $firstDayOfYearCurrent . " - " . $lastDayOfYearCurrent;
                break;
    
            default:
                $query->whereBetween('temp_machine_status.shift_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    
                $currentDay = Carbon::now()->endOfWeek()->format('d M Y');
                $firstDayOfWeekCurrent = Carbon::parse($currentDay)->startOfWeek()->format('d/m/Y');
                $lastDayOfWeekCurrent = Carbon::parse($currentDay)->endOfWeek()->format('d/m/Y');
                $currentDay = $firstDayOfWeekCurrent . " - " . $lastDayOfWeekCurrent;
                break;
        }

        $current = $query->get();
        $firstRec = $query->first();
        $totalLoop = count($current);

        if ($totalLoop <= 0) {
            Log::info("No report found, or the report data has been deleted.");
            return response()->json(['status' => false, 'message' => 'No report found, or the report data has been deleted.']);
        }

        if ($reportFormat == env('REPORT_FORMAT', 'table')) {
            $reportData = $this->generateMachineStopReportTable($current);
            $htmlFile = view('report.machine_stop.table', compact('reportData', 'reportType', 'firstRec', 'currentDay', 'userDetail'))->render();
        }
        else {
            Log::alert("Report format is not supported");
            throw new Exception("Report format is not supported");
        }

        try {
            // Generate HTML content
            $fileName = time() . "-{$reportType}-{$reportFormat}-machine_stop-report-{$userId}.html";
            $filePath = public_path("reports/html/$fileName");
    
            // Save HTML file locally
            if (!file_put_contents($filePath, $htmlFile)) {
                throw new Exception("Failed to save HTML file locally at $filePath.");
            }

            if ($reportFormat == env('REPORT_FORMAT', 'table')) {
                $pdfFilePath = $this->generateTablePdf($filePath);
            }
            else {
                Log::alert("Report format is not supported for generating PDF file.");
                throw new Exception("Report format is not supported for generating PDF file.");
            }
    
            Log::info("PDF file path: " . ($pdfFilePath ?? 'Not Found'));
            $this->sendReportApi($type, $userId, $reportType, $pdfFilePath);

        } catch (Exception $e) {
            Log::error("Error processing report for user ID: $userId, Filter: $reportType. Message: " . $e->getMessage());
            throw new Exception("Error processing report for User ID: $userId - " . $e->getMessage());
        }

        return true;
    }

    protected function generateMachineStopReportTable($result)
    {
        $groupedData = [];

        foreach ($result as $item) {
            $machineName = $item->machine_name;
            $shiftTime = "{$item->shift_name}: {$item->shift_start} - {$item->shift_end}";
            $stopCount = $item->stoppage;

            // Ensure the machine and shift group exist
            if (!isset($groupedData[$machineName][$shiftTime])) {
                $groupedData[$machineName][$shiftTime] = [
                    'records' => [],
                    'total_duration_sec' => 0
                ];
            }

            $lastMachine = Carbon::parse($item->last_machine_datetime);
            $lastDevice = Carbon::parse($item->last_device_datetime);
            $durationSec = $lastMachine->diffInSeconds($lastDevice);

            $groupedData[$machineName][$shiftTime]['records'][] = [
                'stop_count' => $stopCount,
                'stop_time' => date('h:i:s A', strtotime($item->last_machine_datetime)) . ' – ' . date('h:i:s A', strtotime($item->last_device_datetime)),
                'duration_sec' => $durationSec,
            ];

            $groupedData[$machineName][$shiftTime]['total_duration_sec'] += $durationSec;
        }

        return $groupedData;
    }

    public function generateTablePdf($filePath)
    {
        // Load the HTML file from the 'public' directory
        $htmlContent = file_get_contents($filePath);

        // Generate PDF
        $pdf = Pdf::loadHTML($htmlContent)
            ->setPaper('a4', 'landscape'); // Set to A4 landscape

        // Define the storage path
        $pdfFileName = "reports/pdf/" . uniqid() . ".pdf";
        $pdfPath = public_path($pdfFileName);

        // Store PDF in public directory
        $pdf->save($pdfPath);

        unlink($filePath);

        return $pdfPath;
    }

    private function getValue($data, $index, $key, $default = 0) {
        return isset($data[$index]) ? $data[$index]->$key : $default;
    }

    protected function sendReportApi($type, $userId, $reportType, $pdfFilePath)
    {
        $url = env('SEND_REPORT_BASE_URL', '');
        $data = [
            'type' => $type,
            'userId' => $userId,
            'reportType' => $reportType,
            'pdfFilePath' => $pdfFilePath,
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

        Log::info("sendReportApi URL: {$url}, payload: " . json_encode($data) . ", response: {$response}, error: {$error}");
        return $response;
    }
}
