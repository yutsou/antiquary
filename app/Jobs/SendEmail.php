<?php

namespace App\Jobs;

use App\Mail\Notice;
use App\Mail\PasswordResetConfirm;
use App\Mail\VerifyCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $emailContent;

    public function __construct($emailContent)
    {
        $this->emailContent = $emailContent;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch($this->emailContent['type'])
        {
            case 'verifyCode':
                $email = new VerifyCode($this->emailContent);
                Mail::to($this->emailContent['emailAddress'])->send($email);
                break;
            case 'passwordResetConfirm':
                $email = new PasswordResetConfirm($this->emailContent);
                Mail::to($this->emailContent['emailAddress'])->send($email);
                break;
            case 'notice':
                $email = new Notice($this->emailContent);
                Mail::to($this->emailContent['emailAddress'])->send($email);
                break;
        }
    }
}
