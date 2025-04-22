<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class WhatsappAPIService
{
    public function send_weeklyReport($user, $filepath, $reportDay)
    {
        $WHATSAPP_ACCESS_TOKEN = env('WHATSAPP_ACCESS_TOKEN', '');
        $FROM_PHONE_NUMBER_ID = env('FROM_PHONE_NUMBER_ID');
        $LANGUAGE_AND_LOCALE_CODE = env('LANGUAGE_AND_LOCALE_CODE');

        $userId = $user->id;
        $userName = $user->name;
        $userPhone = $user->phone_number;

        $fileName = basename($filepath);

        $pdfFileName =  "Machine-Performance-Report-{$userId}-{$reportDay}.pdf";
        $pdfFileUrl = env('LOCAL_BASE_URL') . "reports/pdf/{$fileName}";

        Log::info("PDF File URL: {$pdfFileUrl}");

        if(empty($userName) || empty($userPhone) || empty($WHATSAPP_ACCESS_TOKEN) || empty($FROM_PHONE_NUMBER_ID) || empty($LANGUAGE_AND_LOCALE_CODE)) {
            Log::error("WhatsApp configuration is incomplete", ['user' => $user->email]);
            return true;
        }

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $userPhone,
            "type" => "template",
            "template" => [
                "name" => 'weekly_report',
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
                                    // "link" => "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf",
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
                                "text" => $reportDay, // {{2}} in template
                            ],
                        ]
                    ]
                ],
            ],
        ];

        Log::info("send_weeklyReport data: " . json_encode($data));
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

    public function send_dailyReport($user, $filepath, $reportDay)
    {
        $WHATSAPP_ACCESS_TOKEN = env('WHATSAPP_ACCESS_TOKEN', '');
        $FROM_PHONE_NUMBER_ID = env('FROM_PHONE_NUMBER_ID');
        $LANGUAGE_AND_LOCALE_CODE = env('LANGUAGE_AND_LOCALE_CODE');

        $userId = $user->id;
        $userName = $user->name;
        $userPhone = $user->phone_number;

        $fileName = basename($filepath);

        $pdfFileName =  "Machine-Performance-Report-{$userId}-{$reportDay}.pdf";
        $pdfFileUrl = env('LOCAL_BASE_URL') . "reports/pdf/{$fileName}";

        Log::info("PDF File URL: {$pdfFileUrl}");

        if(empty($userName) || empty($userPhone) || empty($WHATSAPP_ACCESS_TOKEN) || empty($FROM_PHONE_NUMBER_ID) || empty($LANGUAGE_AND_LOCALE_CODE)) {
            Log::error("WhatsApp configuration is incomplete", ['user' => $user->email]);
            return true;
        }

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $userPhone,
            "type" => "template",
            "template" => [
                "name" => 'daily_report',
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
                                    // "link" => "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf",
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
                                "text" => $reportDay, // {{2}} in template
                            ],
                        ]
                    ]
                ],
            ],
        ];

        Log::info("send_dailyReport data: " . json_encode($data));
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

    public function send_machineStopReport($user, $filepath, $reportDay)
    {
        $WHATSAPP_ACCESS_TOKEN = env('WHATSAPP_ACCESS_TOKEN', '');
        $FROM_PHONE_NUMBER_ID = env('FROM_PHONE_NUMBER_ID');
        $LANGUAGE_AND_LOCALE_CODE = env('LANGUAGE_AND_LOCALE_CODE');

        $userId = $user->id;
        $userName = $user->name;
        $userPhone = $user->phone_number;

        $fileName = basename($filepath);

        $pdfFileName =  "Machine-Stop-Report-{$userId}-{$reportDay}.pdf";
        $pdfFileUrl = env('LOCAL_BASE_URL') . "reports/pdf/{$fileName}";

        Log::info("PDF File URL: {$pdfFileUrl}");

        if(empty($userName) || empty($userPhone) || empty($WHATSAPP_ACCESS_TOKEN) || empty($FROM_PHONE_NUMBER_ID) || empty($LANGUAGE_AND_LOCALE_CODE)) {
            Log::error("WhatsApp configuration is incomplete", ['user' => $user->email]);
            return true;
        }

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $userPhone,
            "type" => "template",
            "template" => [
                "name" => 'machine_stop_report',
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
                                    // "link" => "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf",
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
                                "text" => $reportDay, // {{2}} in template
                            ],
                        ]
                    ]
                ],
            ],
        ];

        Log::info("send_machineStopReport data: " . json_encode($data));
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

    public function send_machineStopAlert($user, $machineName, $lastStopTime, $downtime)
    {
        $WHATSAPP_ACCESS_TOKEN = env('WHATSAPP_ACCESS_TOKEN', '');
        $FROM_PHONE_NUMBER_ID = env('FROM_PHONE_NUMBER_ID');
        $LANGUAGE_AND_LOCALE_CODE = env('LANGUAGE_AND_LOCALE_CODE', 'en');

        $userName = $user->name;
        $userPhone = $user->phone_number;

        if (empty($userName) || empty($userPhone) || empty($WHATSAPP_ACCESS_TOKEN) || empty($FROM_PHONE_NUMBER_ID) || empty($LANGUAGE_AND_LOCALE_CODE)) {
            Log::error("WhatsApp configuration is incomplete", ['user' => $user->email]);
            return false;
        }

        $data = [
            "messaging_product" => "whatsapp",
            "to" => $userPhone,
            "type" => "template",
            "template" => [
                "name" => "machine_stop_alert",
                "language" => [
                    "code" => $LANGUAGE_AND_LOCALE_CODE,
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => $userName,      // {{1}}
                            ],
                            [
                                "type" => "text",
                                "text" => $machineName,   // {{2}}
                            ],
                            [
                                "type" => "text",
                                "text" => $lastStopTime,  // {{3}}
                            ],
                            [
                                "type" => "text",
                                "text" => $downtime,      // {{4}}
                            ],
                        ]
                    ]
                ]
            ]
        ];

        Log::info("send_machineStopAlert data: " . json_encode($data));

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

            if (isset($decodeResponse['messages'][0]['message_status']) && $decodeResponse['messages'][0]['message_status'] === 'accepted') {
                Log::info("WhatsApp machine stop alert sent to {$user->email} with response: {$response}");
                return true;
            }

            Log::warning("WhatsApp alert not accepted for {$user->email}. Response: {$response}");
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp machine stop alert to {$user->phone_number}. Error: " . $e->getMessage());
            return false;
        }
    }
}
