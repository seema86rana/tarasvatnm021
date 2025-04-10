<?php

namespace App\Jobs;

use Exception;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\MachineStatusMail;
use App\Mail\MachineStopMail;

class SendReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $userId;
    protected $filter;
    protected $previousDay;
    protected $currentDay;
    protected $emailSubjectLabel;
    protected $pdfFilePath;

    public $timeout = 3600;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $type, int $userId, string $filter, $previousDay = null, $currentDay = null, $emailSubjectLabel = null, string $pdfFilePath)
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
        $this->type = $type;
        $this->userId = $userId;
        $this->filter = $filter;
        $this->previousDay = $previousDay;
        $this->currentDay = $currentDay;
        $this->emailSubjectLabel = $emailSubjectLabel;
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
                $this->sendMachineStatusReports($this->userId, $this->filter, $this->previousDay, $this->currentDay, $this->emailSubjectLabel, $this->pdfFilePath);
            }
            if ($this->type == 'machine_stop') {
                $this->sendMachineStopReports($this->userId, $this->filter, $this->currentDay, $this->emailSubjectLabel, $this->pdfFilePath);
            }
            Log::info("Report sent successfully.");
        } catch (Exception $e) {
            Log::error("Report sending failed: {$e->getMessage()}");
            throw new Exception($e->getMessage());
        }
    }

    public function sendMachineStatusReports($userId, $filter, $previousDay, $currentDay, $emailSubjectLabel, $pdfFilePath)
    {
        $userDetail = User::findOrFail($userId);
        
        try {
            // Send the PDF via email
            if ($this->sendMachineStatusOnEmail($emailSubjectLabel, $userDetail, $filter, $pdfFilePath, $previousDay, $currentDay)) {
                // unlink($pdfFilePath); // Remove PDF after successful email sending
            } else {
                throw new Exception("Failed to send email with PDF attachment.");
            }
    
            // Send the PDF via WhatsApp
            if ($this->sendMachineStatusOnWhatsApp($emailSubjectLabel, $userDetail, $filter, $pdfFilePath, $previousDay, $currentDay)) {
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

    private function sendMachineStatusOnWhatsApp(string $subject, object $user, string $reportType, string $fileName, $previousDay, $currentDay)
    {
        $WHATSAPP_ACCESS_TOKEN = env('WHATSAPP_ACCESS_TOKEN', '');
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

    public function sendMachineStopReports($userId, $filter, $currentDay, $emailSubjectLabel, $pdfFilePath)
    {
        $userDetail = User::findOrFail($userId);
        
        try {
            // Send the PDF via email
            if ($this->sendMachineStopOnEmail($emailSubjectLabel, $userDetail, $filter, $pdfFilePath, $currentDay)) {
                // unlink($pdfFilePath); // Remove PDF after successful email sending
            } else {
                throw new Exception("Failed to send email with PDF attachment.");
            }
    
            // Send the PDF via WhatsApp
            if ($this->sendMachineStopReportOnWhatsApp($emailSubjectLabel, $userDetail, $filter, $pdfFilePath, $currentDay)) {
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

    private function sendMachineStopOnEmail(string $subject, object $user, string $reportType, string $filePath, $currentDay)
    {
        $mailData = [
            'companyName' => ucwords(str_replace("_", " ", config('app.name', 'TARASVAT Industrial Electronics'))),
            'reportType' => ucfirst($reportType),
            'reportDate' => now()->toDateString(),
            'userName' => $user->name,
            'userId' => $user->id,
            'subject' => $subject,
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

    private function sendMachineStopReportOnWhatsApp(string $subject, object $user, string $reportType, string $fileName, string $dateRange)
    {
        $WHATSAPP_ACCESS_TOKEN = env('WHATSAPP_ACCESS_TOKEN', '');
        $FROM_PHONE_NUMBER_ID = env('FROM_PHONE_NUMBER_ID');
        $TEMPLATE_NAME = 'machine_stop_report'; // Updated template name
        $LANGUAGE_AND_LOCALE_CODE = env('LANGUAGE_AND_LOCALE_CODE');

        $userId = $user->id;
        $userName = $user->name;
        $userPhone = $user->phone_number;

        $fileName = basename($fileName);
        $pdfFileName = "Machine_Stop_Report_{$userId}_" . now()->format('Y_m_d') . ".pdf";
        $pdfFileUrl = env('LOCAL_BASE_URL') . "reports/pdf/{$fileName}";

        Log::info("PDF File URL: {$pdfFileUrl}");

        if (empty($userName) || empty($userPhone) || empty($WHATSAPP_ACCESS_TOKEN) || empty($FROM_PHONE_NUMBER_ID) || empty($TEMPLATE_NAME) || empty($LANGUAGE_AND_LOCALE_CODE)) {
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
                                "text" => $userName,  // {{1}} - recipient name
                            ],
                            [
                                "type" => "text",
                                "text" => $dateRange, // {{2}} - date/time range
                            ],
                        ]
                    ]
                ],
            ],
        ];

        Log::info("WhatsApp Payload: " . json_encode($data));

        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
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
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $WHATSAPP_ACCESS_TOKEN,
                ],
            ]);

            $response = curl_exec($curl);
            curl_close($curl);

            $decodeResponse = json_decode($response, true);
            if (isset($decodeResponse['messages'][0]['message_status']) && $decodeResponse['messages'][0]['message_status'] == 'accepted') {
                Log::info("WhatsApp sent successfully to {$user->email} with response: {$response}");
                return true;
            }

            Log::warning("Failed to send WhatsApp to {$user->email}. Response: {$response}");
            return false;
        } catch (Exception $e) {
            Log::error("WhatsApp send error for {$user->phone_number}: " . $e->getMessage());
            throw new Exception("WhatsApp sending failed: " . $e->getMessage());
        }
    }
}
