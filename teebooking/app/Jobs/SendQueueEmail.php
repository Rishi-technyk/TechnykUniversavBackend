<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\OtpMail;
use App\Mail\SendBookingCancelEmail;
use App\Mail\SendTeeSendEmail;

class SendQueueEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $email;
    protected $subject;
    protected $emailType;

    public function __construct($data, $email, $subject, $emailType)
    {
        $this->data = $data;
        $this->email = $email;
        $this->subject = $subject;
        $this->emailType = $emailType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
{
    if ($this->emailType === 'booking_cancel') {
        SendBookingCancelEmail::dispatch($this->data, $this->email, $this->subject);
    } elseif ($this->emailType === 'otp') {
        //Mail::to($this->email)->send(new OtpMail($this->data, $this->subject));
        SendQueueEmail::dispatch($this->email)->send(new OtpMail($this->data, $this->subject));
        //OtpMail::dispatch($this->otp, $this->email);
    } elseif ($this->emailType === 'tee_send') {
        SendTeeSendEmail::dispatch($this->teeData, $this->email, $this->subject);
    }
}

}
