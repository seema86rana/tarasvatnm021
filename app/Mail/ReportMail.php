<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportMail extends Mailable
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
        date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));
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
        return $this->subject($this->subject)
                    ->view('emails.report')
                    ->attach($this->filePath, [
                        'as' => 'report.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
