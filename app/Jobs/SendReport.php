<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\MachineStopMail;
use Illuminate\Bus\Queueable;
use App\Mail\MachineStatusMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\WhatsappAPIService;

class SendReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $userId;
    protected $reportType;
    protected $pdfFilePath;

    public $timeout = 3600;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $type, int $userId, string $reportType, string $pdfFilePath)
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
        $this->type = $type;
        $this->userId = $userId;
        $this->reportType = $reportType;
        $this->pdfFilePath = $pdfFilePath;
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
                $this->sendMachineStatusReports($this->userId, $this->reportType, $this->pdfFilePath);
            }
            if ($this->type == 'machine_stop') {
                $this->sendMachineStopReports($this->userId, $this->reportType, $this->pdfFilePath);
            }
            Log::info("Report sent successfully.");
        } catch (Exception $e) {
            Log::error("Report sending failed: {$e->getMessage()}");
            throw new Exception($e->getMessage());
        }
    }

    public function sendMachineStatusReports($userId, $reportType, $pdfFilePath)
    {
        $previousDay = '';
        $currentDay = '';
        $emailSubjectLabel = '';
        $userDetail = User::findOrFail($userId);

        switch ($reportType) {
            case 'daily':
                $curDay = Carbon::yesterday()->subDay();
                $preDay = $curDay->copy()->subDay();
                $emailSubjectLabel = "Daily Comparison Report - [" . $preDay->format('d/m/Y') . " to " . $curDay->format('d/m/Y') . "]";

                $previousDay = $preDay->format('d/m/Y');
                $currentDay = $curDay->format('d/m/Y');
                break;

            case 'weekly':
                $emailSubjectLabel = "Weekly Comparison Report - [" . Carbon::now()->subWeek()->startOfWeek()->format('d/m/Y') . " to " . Carbon::now()->endOfWeek()->format('d/m/Y') . "]";
                
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
                $emailSubjectLabel = "Monthly Comparison Report - [" . Carbon::now()->subMonth()->format('m/Y') . " to " . Carbon::now()->format('m/Y') . "]";

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
                $emailSubjectLabel = "Yearly Comparison Report - [" . Carbon::now()->subYear()->year . " to " .  Carbon::now()->year . "]";

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
                $emailSubjectLabel = "Weekly Comparison Report - [" . Carbon::now()->subWeek()->startOfWeek()->format('d/m/Y') . " to " . Carbon::now()->endOfWeek()->format('d/m/Y') . "]";
                
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
        
        try {
            // Send the PDF via email
            if ($this->sendMachineStatusOnEmail($emailSubjectLabel, $userDetail, $reportType, $pdfFilePath, $previousDay, $currentDay)) {
                //
            } else {
                Log::alert("Failed to send machine status report via email");
                throw new Exception("Failed to send email with PDF attachment.");
            }
    
            // Send the PDF via WhatsApp
            if ($this->sendMachineStatusOnWhatsApp($reportType, $userDetail, $pdfFilePath, $previousDay, $currentDay)) {
                //
            } else {
                Log::alert("Failed to send machine status report via WhatsApp");
                throw new Exception("Failed to send whatsapp with PDF link.");
            }
    
        } catch (Exception $e) {
            Log::error("Error processing report for user ID: $userId, Filter: $reportType. Message: " . $e->getMessage());
            throw new Exception("Error processing report for User ID: $userId - " . $e->getMessage());
        }

        return true;
    }

    private function sendMachineStatusOnEmail(string $subject, object $user, string $reportType, string $filePath, $previousDay, $currentDay)
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
            Mail::to($user->email)->send(new MachineStatusMail($mailData, $filePath, $subject));
            Log::info("Email sent successfully to {$user->email} with subject: {$subject}");
            return true;
        } catch (Exception $e) {
            Log::error("Failed to send email to {$user->email}. Error: " . $e->getMessage());
            throw new Exception("Email sending failed: " . $e->getMessage());
        }
    }

    private function sendMachineStatusOnWhatsApp(string $reportType, object $user, string $filepath, $previousDay, $currentDay)
    {
        $reportDay = $previousDay . " - " . $currentDay;
        $whatsappAPIService = new WhatsAppAPIService();

        switch($reportType) {
            case 'daily':
                $response = $whatsappAPIService->send_dailyReport($user, $filepath, $reportDay);
                return $response;
                break;
                
            case 'weekly':
                $response = $whatsappAPIService->send_weeklyReport($user, $filepath, $reportDay);
                return $response;
                break;

            default:
                $response = $whatsappAPIService->send_weeklyReport($user, $filepath, $reportDay);
                return $response;
                break;

        }
    }

    public function sendMachineStopReports($userId, $reportType, $pdfFilePath)
    {
        $previousDay = '';
        $currentDay = '';
        $emailSubjectLabel = '';
        $userDetail = User::findOrFail($userId);

        switch ($reportType) {
            case 'daily':
                $preDay = Carbon::yesterday()->subDay();
                $curDay = $preDay->copy()->addDay();
                $emailSubjectLabel = "Daily Machine Stop Report - [" . $preDay->format('d/m/Y') . " to " . $curDay->format('d/m/Y') . "]";

                $previousDay = $preDay->format('d/m/Y');
                $currentDay = $curDay->format('d/m/Y');
                break;

            case 'weekly':
                $emailSubjectLabel = "Weekly Machine Stop Report - [" . Carbon::now()->subWeek()->startOfWeek()->format('d/m/Y') . " to " . Carbon::now()->endOfWeek()->format('d/m/Y') . "]";
                
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
                $emailSubjectLabel = "Monthly Machine Stop Report - [" . Carbon::now()->subMonth()->format('m/Y') . " to " . Carbon::now()->format('m/Y') . "]";

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
                $emailSubjectLabel = "Yearly Machine Stop Report - [" . Carbon::now()->subYear()->year . " to " .  Carbon::now()->year . "]";

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
                $emailSubjectLabel = "Weekly Machine Stop Report - [" . Carbon::now()->subWeek()->startOfWeek()->format('d/m/Y') . " to " . Carbon::now()->endOfWeek()->format('d/m/Y') . "]";
                
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

        try {
            // Send the PDF via email
            if ($this->sendMachineStopOnEmail($emailSubjectLabel, $userDetail, $reportType, $pdfFilePath, $previousDay, $currentDay)) {
                //
            } else {
                log::alert("Failed to send machine stop report via email");
                throw new Exception("Failed to send email with PDF attachment.");
            }
    
            // Send the PDF via WhatsApp
            if ($this->sendMachineStopReportOnWhatsApp($reportType, $userDetail, $pdfFilePath, $previousDay, $currentDay)) {
                //
            } else {
                log::alert("Failed to send machine stop report via WhatsApp");
                throw new Exception("Failed to send whatsapp with PDF link.");
            }

            return true;
    
        } catch (Exception $e) {
            Log::error("Error processing report for user ID: $userId, Filter: $reportType. Message: " . $e->getMessage());
            throw new Exception("Error processing report for User ID: $userId - " . $e->getMessage());
        }

        return true;
    }

    private function sendMachineStopOnEmail(string $subject, object $user, string $reportType, string $filePath, $previousDay, $currentDay)
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
            Mail::to($user->email)->send(new MachineStopMail($mailData, $filePath, $subject));
            Log::info("Email sent successfully to {$user->email} with subject: {$subject}");
            return true;
        } catch (Exception $e) {
            Log::error("Failed to send email to {$user->email}. Error: " . $e->getMessage());
            throw new Exception("Email sending failed: " . $e->getMessage());
        }
    }

    private function sendMachineStopReportOnWhatsApp(string $reportType, object $user, string $filepath, $previousDay, $currentDay)
    {
        $reportDay = $previousDay . " - " . $currentDay;
        $whatsappAPIService = new WhatsAppAPIService();

        switch ($reportType) {
            case 'daily':
                $response = $whatsappAPIService->send_machineStopReport($user, $filepath, $reportDay);
                return $response;
                break;
            
            default:
                $response = $whatsappAPIService->send_machineStopReport($user, $filepath, $reportDay);
                return $response;
                break;
        }
    }
}
