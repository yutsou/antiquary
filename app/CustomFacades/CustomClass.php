<?php

namespace App\CustomFacades;

use App\Jobs\SendEmail;
use App\Models\Notice;
use App\Models\User;

class CustomClass
{
    public static function viewWithTitle($viewWithParams, $title)
    {
        return $viewWithParams->with('head', $title)->with('title', $title.' - '.config('app.name'));
    }

    public static function sendTemplateNotice($userId, $type, $code, $targetId, $withEmail=null, $content=null)
    {
        $notice = Notice::create([
            'user_id'=>$userId,
            'type'=>$type,
            'code'=>$code,
            'target_id'=>$targetId,
            'content'=>$content
        ]);

        if($withEmail !== null) {
            $user = User::find($userId);
            $emailContent['type'] = 'notice';
            $emailContent['user'] = $user;
            $emailContent['emailAddress'] = $user->email;
            $emailContent['notice'] = $notice;
            SendEmail::dispatch($emailContent);
        }
    }
}
