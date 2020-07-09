<?php
// +----------------------------------------------------------------------
// | CatchAdmin [Just Like ～ ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017~2020 http://catchadmin.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://github.com/yanwenwu/catch-admin/blob/master/LICENSE.txt )
// +----------------------------------------------------------------------
// | Author: JaguarJack [ njphper@gmail.com ]
// +----------------------------------------------------------------------
namespace catcher\library\crontab;

trait Store
{
    /**
     * 存储 pid
     *
     * @time 2020年07月05日
     * @param $pid
     * @return false|int
     */
    public function storeMasterPid($pid)
    {
        $path = $this->getMasterPidPath();

        return file_put_contents($path, $pid);
    }

    /**
     * 存储信息
     *
     * @time 2020年07月07日
     * @param array $status
     * @return void
     */
    public function storeStatus(array $status)
    {
        $workersStatus = $this->getProcessesStatus();

        if (empty($workersStatus)) {
            $this->writeStatusToFile([$status]);
        } else {
            // ['PID',, 'START_AT', 'STATUS', 'DEAL_TASKS', 'ERRORS', 'running_time', 'memory'];
            $pids = array_column($workersStatus, 'pid');

            if (!in_array($status['pid'], $pids)) {
                $workersStatus = array_merge($workersStatus, $status);
            } else {
                foreach ($workersStatus as &$workerStatus) {
                    if ($workersStatus['pid'] == $status['pid']) {
                        $workersStatus = $status;
                        break;
                    }
                }
            }
            $this->writeStatusToFile($workersStatus);
        }
    }

    /**
     * 获取进程间信息
     *
     * @time 2020年07月08日
     * @return mixed
     */
    protected function getProcessesStatus()
    {
        return \json_decode(file_get_contents($this->getProcessStatusPath()), true);
    }

    /**
     * 清除退出的 worker 信息
     *
     * @time 2020年07月08日
     * @param $pid
     * @return void
     */
    protected function unsetWorkerStatus($pid)
    {
        $workers = $this->getProcessesStatus();

        foreach ($workers as $k => $worker) {
            if ($worker['pid'] == $pid) {
                unset($workers[$k]);
            }
        }

        $this->writeStatusToFile($workers);
    }

    /**
     * 写入文件
     *
     * @time 2020年07月08日
     * @param $status
     * @return void
     */
    protected function writeStatusToFile($status)
    {
        $this->writeContentToFile($this->getProcessStatusPath(), \json_encode($status));
    }

    /**
     * 写入内容
     *
     * @time 2020年07月09日
     * @param $path
     * @param $content
     * @return void
     */
    protected function writeContentToFile($path, $content)
    {
        $file = new \SplFileObject($path, 'rw+');
        $file->flock(LOCK_EX);
        $file->fwrite($content);
        $file->flock(LOCK_UN);
    }

    /**
     * 输出
     *
     * @time 2020年07月07日
     * @return false|string
     */
    public function output()
    {
        // 等待信号输出
        sleep(1);

        return $this->getProcessStatusInfo();
    }

    /**
     * 获取 pid
     *
     * @time 2020年07月05日
     * @return int
     */
    public function getMasterPid()
    {
        $pid = file_get_contents($this->getMasterPidPath());

        return intval($pid);
    }

    /**
     * 获取配置地址
     *
     * @time 2020年07月05日
     * @return string
     */
    protected function getMasterPidPath()
    {
        return  $this->schedulePath() . 'master.pid';
    }

    /**
     * 创建任务调度文件夹
     *
     * @time 2020年07月09日
     * @return string
     */
    protected function schedulePath()
    {
        $path = runtime_path('schedule' . DIRECTORY_SEPARATOR);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
    }

    /**
     * 获取 worker 状态存储地址
     *
     * @time 2020年07月07日
     * @return string
     */
    protected function getProcessStatusPath()
    {
        return $this->schedulePath() . 'worker-status.json';
    }

    /**
     * 进程状态文件
     *
     * @time 2020年07月09日
     * @return string
     */
    protected function getSaveProcessStatusFile()
    {
        return $this->schedulePath() . '.worker-status';
    }

    /**
     *  保存进程状态
     *
     * @time 2020年07月09日
     * @return void
     */
    protected function saveProcessStatus()
    {
        file_put_contents($this->getSaveProcessStatusFile(), $this->renderProcessesStatusToString());
    }

    /**
     * 获取进程状态
     *
     * @time 2020年07月09日
     * @return false|string
     */
    protected function getProcessStatusInfo()
    {
        $this->saveProcessStatus();

        return file_get_contents($this->getSaveProcessStatusFile());
    }
}