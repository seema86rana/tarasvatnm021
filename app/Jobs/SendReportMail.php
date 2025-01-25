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

class SendReportMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reportType;
    protected $userId;

    public $timeout = 3600;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $reportType, $userId)
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
        $this->reportType = $reportType;
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
            $this->generateReport($this->reportType, $this->userId);
            Log::info("Report sent successfully for type: {$this->reportType}");
        } catch (Exception $e) {
            Log::error("Report sending failed: {$e->getMessage()}");
            throw $e;
        }
    }

    public function generateReport($filter, $userId)
    {
        $previousLabel = '';
        $currentLabel = '';
        $emailSubjectLabel = '';
        $previousDay = '';
        $currentDay = '';

        $userDetail = User::findOrFail($userId);
        
        $queryPrevious = MachineStatus::
                selectRaw("node_master.name, machine_status.user_id, machine_master.name, SUM(machine_status.speed) as speed, SUM(machine_status.efficiency) as efficiency, SUM(machine_status.no_of_stoppage) as no_of_stoppage, SUM(pick_calculations.shift_pick) as shift_pick")
                ->leftJoin('machine_master', 'machine_status.machine_id', '=', 'machine_master.id')
                ->leftJoin('node_master', 'machine_master.node_id', '=', 'node_master.id')
                ->leftJoin('pick_calculations', 'machine_status.id', '=', 'pick_calculations.machine_status_id')
                ->where('machine_status.user_id', $userId);

        $queryCurrent = clone $queryPrevious;

        switch ($filter) {
            case 'daily':
                $queryPrevious->whereDate('machine_status.created_at', '2024-12-10');
                $queryCurrent->whereDate('machine_status.created_at', '2024-12-11');
                // $queryPrevious->whereDate('machine_status.created_at', Carbon::yesterday());
                // $queryCurrent->whereDate('machine_status.created_at', Carbon::today());
    
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

        $previous = $queryPrevious->groupBy('machine_status.machine_id', 'node_master.name', 'machine_status.user_id', 'machine_master.name')->get();
        $current = $queryCurrent->groupBy('machine_status.machine_id', 'node_master.name', 'machine_status.user_id', 'machine_master.name')->get();

        $totalLoop = max(count($previous->toArray()), count($current->toArray()));
        if ($totalLoop <= 0) {
            return response()->json(['status' => false, 'message' => 'No report found, or the report data has been deleted.'], 404);
        }

        $resultArray = [];
        for ($i = 0; $i < $totalLoop; $i++) {
            
            $user = $previous[$i]->user_id ?? $current[$i]->user_id;
            $node = $previous[$i]->name ?? $current[$i]->name;
            $machineDisplayName = $previous[$i]->name ?? $current[$i]->n;
        
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
            try {
                // Generate HTML content
                $htmlData = view('report.pdf', compact('value', 'previousLabel', 'currentLabel'))->render();
                $fileName = time() . "-$filter-report-$userId.html";
                $filePath = public_path("reports/html/$fileName");
        
                // Save HTML file locally
                if (!file_put_contents($filePath, $htmlData)) {
                    throw new Exception("Failed to save HTML file locally at $filePath.");
                }
        
                // Generate HTML file URL
                $htmlFileUrl = env('LOCAL_BASE_URL') . "reports/html/$fileName";
        
                // Call the API to generate the PDF
                $generatePdfApi = $this->genratePdfApi($htmlFileUrl);
                $generatePdfApi = json_decode($generatePdfApi, true);
        
                Log::info("HTML File URL: {$htmlFileUrl}");
                Log::info("PDF URL from API: " . ($generatePdfApi['pdfUrl'] ?? 'Not Found'));
        
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
                $deletePdfApi = $this->deletePdfApi($pdfFileName);
                $deletePdfApi = json_decode($deletePdfApi, true);
        
                if ($deletePdfApi['status'] !== 'success' || !unlink($filePath)) {
                    throw new Exception("Failed to delete the HTML file or associated resources.");
                }
        
                // Send the PDF via email
                if ($this->sendOnEmail($emailSubjectLabel, $userDetail, $filter, $pdfFilePath, $previousDay, $currentDay)) {
                    unlink($pdfFilePath); // Remove PDF after successful email sending
                } else {
                    throw new Exception("Failed to send email with PDF attachment.");
                }
        
                // Send the PDF via WhatsApp
                $this->sendOnWhatsApp($emailSubjectLabel, $userDetail, $filter, $pdfFilePath, $previousDay, $currentDay);
        
            } catch (Exception $e) {
                Log::error("Error processing report for user ID: $userId, Filter: $filter. Message: " . $e->getMessage());
                throw new Exception("Error processing report for User ID: $userId - " . $e->getMessage());
            }
        }
        return true;
    }

    protected function genratePdfApi($fileUrl)
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

    protected function deletePdfApi($fileUrl)
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
        // Add WhatsApp sending logic here
        return true;
    }

    private function getValue($data, $index, $key, $default = 0) {
        return isset($data[$index]) ? $data[$index]->$key : $default;
    }
}
