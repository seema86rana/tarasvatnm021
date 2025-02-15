<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\ReportMail;
use App\Models\MachineStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Barryvdh\DomPDF\Facade\Pdf;

class SendReportMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportType;
    protected $reportFormat;
    protected $userId;

    public $timeout = 3600;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $reportType, string $reportFormat, int $userId)
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
        $this->reportType = $reportType;
        $this->reportFormat = $reportFormat;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->generateReport($this->reportType, $this->reportFormat, $this->userId);
            Log::info("Report sent successfully for type: {$this->reportType}");
        } catch (Exception $e) {
            Log::error("Report sending failed: {$e->getMessage()}");
            throw new Exception($e->getMessage());
        }
    }

    public function generateReport($filter, $format, $userId)
    {
        $previousLabel = '';
        $currentLabel = '';
        $emailSubjectLabel = '';
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

        switch ($filter) {
            case 'daily':
                // $queryPrevious->whereDate('machine_status.created_at', '2025-01-27');
                // $queryCurrent->whereDate('machine_status.created_at', '2025-01-28');
                $queryPrevious->whereDate('machine_status.created_at', Carbon::yesterday());
                $queryCurrent->whereDate('machine_status.created_at', Carbon::today());
    
                $previousLabel = "Yesterday " . Carbon::yesterday()->format('d M Y');
                $currentLabel = "Today " . Carbon::today()->format('d M Y');
                $emailSubjectLabel = "Daily Comparison Report - [" . Carbon::yesterday()->format('d M Y') . " to " . Carbon::today()->format('d M Y') . "]";
                $previousDay = Carbon::yesterday()->format('d M Y');
                $currentDay = Carbon::today()->format('d M Y');
                break;
    
            case 'weekly':
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    
                $previousLabel = "Last Week " . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->subWeek()->endOfWeek()->format('d M Y');
                $currentLabel = "Current Week " . Carbon::now()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y');
                $emailSubjectLabel = "Weekly Comparison Report - [" . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y') . "]";
                $previousDay = Carbon::now()->subWeek()->startOfWeek()->format('d M Y');
                $currentDay = Carbon::now()->endOfWeek()->format('d M Y');
                break;
    
            case 'monthly':
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
    
                $previousLabel = "Last Month " . Carbon::now()->subMonth()->format('M Y');
                $currentLabel = "Current Month " . Carbon::now()->format('M Y');
                $emailSubjectLabel = "Monthly Comparison Report - [" . Carbon::now()->subMonth()->format('M Y') . " to " . Carbon::now()->format('M Y') . "]";
                $previousDay = Carbon::now()->subMonth()->format('M Y');
                $currentDay = Carbon::now()->format('M Y');
                break;
    
            case 'yearly':
                $queryPrevious->whereYear('machine_status.created_at', Carbon::now()->subYear()->year);
                $queryCurrent->whereYear('machine_status.created_at', Carbon::now()->year);
    
                $previousLabel = "Last Year " . Carbon::now()->subYear()->year;
                $currentLabel = "Current Year " . Carbon::now()->year;
                $emailSubjectLabel = "Yearly Comparison Report - [" . Carbon::now()->subYear()->year . " to " .  Carbon::now()->year . "]";
                $previousDay = Carbon::now()->subYear()->year;
                $currentDay = Carbon::now()->year;
                break;
    
            default:
                $queryPrevious->whereBetween('machine_status.created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
                $queryCurrent->whereBetween('machine_status.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    
                $previousLabel = "Last Week " . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->subWeek()->endOfWeek()->format('d M Y');
                $currentLabel = "Current Week " . Carbon::now()->startOfWeek()->format('d M Y') . " to " . Carbon::now()->endOfWeek()->format('d M Y');
                $emailSubjectLabel = "Weekly Comparison Report - [" . Carbon::now()->subWeek()->startOfWeek()->format('d M Y') . " to " .  Carbon::now()->endOfWeek()->format('d M Y') . "]";
                $previousDay = Carbon::now()->subWeek()->startOfWeek()->format('d M Y');
                $currentDay = Carbon::now()->endOfWeek()->format('d M Y');
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
            return response()->json(['status' => false, 'message' => 'No report found, or the report data has been deleted.'], 404);
        }

        $filter = ($filter == 'daily') ? 'day' : $filter;

        if ($format == env('REPORT_FORMAT', 'table')) {
            switch ($filter) {
                case 'daily':
                    $previousDay = Carbon::parse($previousDay)->format('d/m/Y');
                    $currentDay = Carbon::parse($currentDay)->format('d/m/Y');
                    break;
        
                case 'weekly':
                    $firstDayOfWeekPrevious = Carbon::parse($previousDay)->startOfWeek()->format('d/m/Y');
                    $lastDayOfWeekPrevious = Carbon::parse($previousDay)->endOfWeek()->format('d/m/Y');
                    $previousDay = $firstDayOfWeekPrevious . " - " . $lastDayOfWeekPrevious;
        
                    $firstDayOfWeekCurrent = Carbon::parse($currentDay)->startOfWeek()->format('d/m/Y');
                    $lastDayOfWeekCurrent = Carbon::parse($currentDay)->endOfWeek()->format('d/m/Y');
                    $currentDay = $firstDayOfWeekCurrent . " - " . $lastDayOfWeekCurrent;
                    break;
        
                case 'monthly':
                    $firstDayOfMonthPrevious = Carbon::parse($previousDay)->startOfMonth()->format('d/m/Y');
                    $lastDayOfMonthPrevious = Carbon::parse($previousDay)->endOfMonth()->format('d/m/Y');
                    $previousDay = $firstDayOfMonthPrevious . " - " . $lastDayOfMonthPrevious;
        
                    $firstDayOfMonthCurrent = Carbon::parse($currentDay)->startOfMonth()->format('d/m/Y');
                    $lastDayOfMonthCurrent = Carbon::parse($currentDay)->endOfMonth()->format('d/m/Y');
                    $currentDay = $firstDayOfMonthCurrent . " - " . $lastDayOfMonthCurrent;
                    break;
        
                case 'yearly':
                    $firstDayOfYearPrevious = Carbon::parse($previousDay)->startOfYear()->format('d/m/Y');
                    $lastDayOfYearPrevious = Carbon::parse($previousDay)->endOfYear()->format('d/m/Y');
                    $previousDay = $firstDayOfYearPrevious . " - " . $lastDayOfYearPrevious;
        
                    $firstDayOfYearCurrent = Carbon::parse($currentDay)->startOfYear()->format('d/m/Y');
                    $lastDayOfYearCurrent = Carbon::parse($currentDay)->endOfYear()->format('d/m/Y');
                    $currentDay = $firstDayOfYearCurrent . " - " . $lastDayOfYearCurrent;
                    break;
        
                default:
                    $firstDayOfWeekPrevious = Carbon::parse($previousDay)->startOfWeek()->format('d/m/Y');
                    $lastDayOfWeekPrevious = Carbon::parse($previousDay)->endOfWeek()->format('d/m/Y');
                    $previousDay = $firstDayOfWeekPrevious . " - " . $lastDayOfWeekPrevious;
        
                    $firstDayOfWeekCurrent = Carbon::parse($currentDay)->startOfWeek()->format('d/m/Y');
                    $lastDayOfWeekCurrent = Carbon::parse($currentDay)->endOfWeek()->format('d/m/Y');
                    $currentDay = $firstDayOfWeekCurrent . " - " . $lastDayOfWeekCurrent;
                    break;
            }
        }

        if ($format == env('REPORT_FORMAT', 'table')) {
            $reportData = $this->generateReportTable($previous, $current, $totalLoop);
            $htmlFile = view('report.table', compact('reportData', 'filter', 'firstRec', 'previousDay', 'currentDay', 'userDetail'))->render();
        }
        else {
            $reportData = $this->generateReportChart($filter, $previous, $current, $totalLoop);
            $htmlFile = view('report.chart', compact('reportData', 'previousLabel', 'currentLabel'))->render();
        }

        try {
            // Generate HTML content
            $fileName = time() . "-{$filter}-{$format}-report-$userId.html";
            $filePath = public_path("reports/html/$fileName");
    
            // Save HTML file locally
            if (!file_put_contents($filePath, $htmlFile)) {
                throw new Exception("Failed to save HTML file locally at $filePath.");
            }

            // Generate HTML file URL
            $htmlFileUrl = env('LOCAL_BASE_URL') . "reports/html/$fileName";

            if ($format == env('REPORT_FORMAT', 'table')) {
                $pdfFilePath = $this->generateTablePdf($filePath);
            }
            else {
                $pdfFilePath = $this->generateChartPdf($filePath, $htmlFileUrl);
            }
    
            Log::info("HTML File URL: {$htmlFileUrl}");
            Log::info("PDF URL from API: " . ($pdfFilePath ?? 'Not Found'));
    
            // Send the PDF via email
            if ($this->sendOnEmail($emailSubjectLabel, $userDetail, $filter, $pdfFilePath, $previousDay, $currentDay)) {
                // unlink($pdfFilePath); // Remove PDF after successful email sending
            } else {
                throw new Exception("Failed to send email with PDF attachment.");
            }
    
            // Send the PDF via WhatsApp
            if ($this->sendOnWhatsApp($emailSubjectLabel, $userDetail, $filter, $pdfFilePath, $previousDay, $currentDay)) {
                // unlink($pdfFilePath); // Remove PDF after successful email sending
            } else {
                throw new Exception("Failed to send whatsapp with PDF link.");
            }
    
        } catch (Exception $e) {
            Log::error("Error processing report for user ID: $userId, Filter: $filter. Message: " . $e->getMessage());
            throw new Exception("Error processing report for User ID: $userId - " . $e->getMessage());
        }

        return true;
    }

    public function generateReportTable($previous, $current, $totalLoop)
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

    public function generateReportChart($filter, $previous, $current, $totalLoop)
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
            $groupedData[$node][$shiftName]['label'] = $node . ' (' . ucwords($filter) . ')' . ' (' . ($previous[$i]->shift_start ?? $current[$i]->shift_start) . ' - ' . ($previous[$i]->shift_end ?? $current[$i]->shift_end) . ')';
            $groupedData[$node][$shiftName]['speed'][] = $speed;
            $groupedData[$node][$shiftName]['efficiency'][] = $efficiency;
            $groupedData[$node][$shiftName]['no_of_stoppage'][] = $no_of_stoppage;
            $groupedData[$node][$shiftName]['shift_pick'][] = $shift_pick;
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

    public function generateChartPdf($filePath, $fileUrl)
    {
        $generatePdfApi = $this->genrateChartPdfApi($fileUrl);
        $generatePdfApi = json_decode($generatePdfApi, true);

        // Validate the PDF URL
        if (empty($generatePdfApi['pdfUrl'])) {
            throw new Exception("PDF URL not found in the API response.");
        }

        $pdfFileName = basename($generatePdfApi['pdfUrl']); // Get the last part of the URL
        $pdfFilePath = public_path("reports/pdf/$pdfFileName");

        // Store the PDF file locally
        if (!file_put_contents($pdfFilePath, file_get_contents($generatePdfApi['pdfUrl']))) {
            throw new Exception("Failed to store the generated PDF locally at $pdfFilePath.");
        }

        // Delete the HTML file using the API
        $deletePdfApi = $this->deleteChartPdfApi($pdfFileName);
        $deletePdfApi = json_decode($deletePdfApi, true);

        if ($deletePdfApi['status'] !== 'success' || !unlink($filePath)) {
            throw new Exception("Failed to delete the HTML file or associated resources.");
        }

        return $pdfFilePath;
    }

    protected function genrateChartPdfApi($fileUrl)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('GENERATE_PDF_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(["url" => $fileUrl]),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    protected function deleteChartPdfApi($fileUrl)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('DELETE_PDF_URL'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => json_encode(["filename" => $fileUrl]),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * Send report via email.
     */
    private function sendOnEmail(string $subject, object $user, string $reportType, string $filePath, $previousDay, $currentDay)
    {
        $mailData = [
            'companyName' => ucwords(str_replace("_", " ", config('app.name', 'TARASVAT Industrial Electronics'))),
            'reportType' => ucfirst($reportType),
            'reportDate' => now()->toDateString(),
            'userName' => $user->name,
            'userId' => $user->id,
            'subject' => $subject,
            'previousDay' => $previousDay,
            'currentDay' => $currentDay,
        ];
        
        try {
            // Send the email
            Mail::to($user->email)->send(new ReportMail($mailData, $filePath, $subject));
            Log::info("Email sent successfully to {$user->email} with subject: {$subject}");
            return true;
        } catch (Exception $e) {
            Log::error("Failed to send email to {$user->email}. Error: " . $e->getMessage());
            throw new Exception("Email sending failed: " . $e->getMessage());
        }
    }

    /**
     * Placeholder for sending report via WhatsApp.
     */
    private function sendOnWhatsApp(string $subject, object $user, string $reportType, string $fileName, $previousDay, $currentDay)
    {
        $WHATSAPP_ACCESS_TOKEN = env('WHATSAPP_ACCESS_TOKEN');
        $FROM_PHONE_NUMBER_ID = env('FROM_PHONE_NUMBER_ID');
        $TEMPLATE_NAME = env('TEMPLATE_NAME');
        $LANGUAGE_AND_LOCALE_CODE = env('LANGUAGE_AND_LOCALE_CODE');

        $userId = $user->id;
        $userName = $user->name;
        $userPhone = $user->phone_number;

        $fileName = basename($fileName);

        $pdfFileName =  "Machine_Performance_Report{$userId}_{$currentDay}.pdf";
        $pdfFileUrl = env('LOCAL_BASE_URL') . "reports/pdf/{$fileName}";

        Log::info("PDF File URL: {$pdfFileUrl}");

        if(empty($userName) || empty($userPhone) || empty($WHATSAPP_ACCESS_TOKEN) || empty($FROM_PHONE_NUMBER_ID) || empty($TEMPLATE_NAME) || empty($LANGUAGE_AND_LOCALE_CODE)) {
            Log::error("WhatsApp configuration is incomplete", ['user' => $user->email]);
            return false;
        }

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $userPhone,
            "type" => "template",
            "template" => [
                "name" => $TEMPLATE_NAME,
                "language" => [
                    "code" => $LANGUAGE_AND_LOCALE_CODE,
                ],
                "components" => [
                    [
                        "type" => "header",
                        "parameters" => [
                            [
                                "type" => "document",
                                "document" => [
                                    "link" => $pdfFileUrl,
                                    "filename" => $pdfFileName,
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $userName,  // {{1}} in template
                            ],
                            [
                                "type" => "text",
                                "text" => $currentDay, // {{2}} in template
                            ],
                        ]
                    ]
                ],
            ],
        ];        

        Log::info("data: " . json_encode($data));
        // return true;

        try {
            // Send the WhatsApp
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://graph.facebook.com/v16.0/{$FROM_PHONE_NUMBER_ID}/messages",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $WHATSAPP_ACCESS_TOKEN,
            ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);

            $decodeResponse = json_decode($response, true);
            if (isset($decodeResponse['messages'][0]['message_status']) && $decodeResponse['messages'][0]['message_status'] == 'accepted') {
                Log::info("WhatsApp sent successfully to {$user->email} with response: {$response}");
                return true;
            }

            Log::info("Failed to send WhatsApp to {$user->email} with response: {$response}");
            return false;
        } catch (Exception $e) {
            Log::error("Failed to send WhatsApp to {$user->phone_number}. Error: " . $e->getMessage());
            throw new Exception("WhatsApp sending failed: " . $e->getMessage());
        }
    }

    private function getValue($data, $index, $key, $default = 0) {
        return isset($data[$index]) ? $data[$index]->$key : $default;
    }
}
