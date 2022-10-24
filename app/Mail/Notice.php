<?php

namespace App\Mail;

use App\Presenters\NoticePresenter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Notice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $emailContent;

    public function __construct($emailContent)
    {
        $this->emailContent = $emailContent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $noticePresenter = new NoticePresenter();
        $transferedNotice = $noticePresenter->transferTemplateNotice($this->emailContent['notice']);
        return $this->view('emails.notice')
            ->with(
                [
                    'user'=>$this->emailContent['user'],
                    'content'=>$transferedNotice
                ]
            )
            ->subject($transferedNotice[0]);
    }
}
