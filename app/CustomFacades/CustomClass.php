<?php

namespace App\CustomFacades;

use App\Jobs\SendEmail;
use App\Jobs\SendLine;
use App\Models\Notice;
use App\Models\User;
use App\Presenters\NoticePresenter;

class CustomClass
{
    public static function viewWithTitle($viewWithParams, $title)
    {
        return $viewWithParams->with('head', $title)->with('title', $title.' - '.config('app.name'));
    }

    public static function sendTemplateNotice($userId, $type, $code, $targetId, $withEmail=null, $withLine=null, $content=null)
    {
        $notice = Notice::create([
            'user_id'=>$userId,
            'type'=>$type,
            'code'=>$code,
            'target_id'=>$targetId,
            'content'=>$content
        ]);

        $user = User::find($userId);

        if($withEmail !== null) {
            $emailContent['type'] = 'notice';
            $emailContent['user'] = $user;
            $emailContent['emailAddress'] = $user->email;
            $emailContent['notice'] = $notice;
            SendEmail::dispatch($emailContent);
        }

        if($withLine !== null) {
            $text = app(NoticePresenter::class)->transferTemplateNotice($notice);
            SendLine::dispatch(null, $user->id, null, 1, $text);
        }
    }
}
