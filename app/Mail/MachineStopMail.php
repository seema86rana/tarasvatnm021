<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MachineStopMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    public $filePath;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData, $filePath, $subject)
    {
        date_default_timezone_set((config('app.timezone') ?? 'Asia/Kolkata'));
        $this->mailData = $mailData;
        $this->filePath = $filePath;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $userId = $this->mailData['userId'];
        $currentDay = str_replace("/", "-", $this->mailData['currentDay']);
        $currentDay = str_replace(" - ", "_to_", $currentDay);
        
        return $this->subject($this->subject)
                    ->view('emails.report.machine_stop')
                    ->attach($this->filePath, [
                        'as' => "Machine_Stop_Report{$userId}_{$currentDay}.pdf",
                        'mime' => 'application/pdf',
                    ]);
    }
}
