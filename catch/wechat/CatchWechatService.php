<?php
namespace catchAdmin\wechat;

use catchAdmin\wechat\command\SyncUsersCommand;
use think\Service;

class CatchWechatService extends Service
{
    public function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
    }

    public function registerCommand()
    {
        $this->commands([
            SyncUsersCommand::class,
        ]);
    }
}