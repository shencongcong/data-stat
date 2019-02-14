<?php
/**
 * Created by PhpStorm.
 * User: danielshen
 * Date: 2018/12/11
 * Time: 下午5:44
 */

namespace script;

use \common\GetConfigs;
use \common\DbOperate;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


abstract class Basic
{
    public $script_name = ''; # 脚步名称，见common/script.php

    // 主业务逻辑，被子类复写
    public abstract function start($date);

    public function run() {

        // 接收日期参数
        if (isset($_SERVER["argv"][1]) && strpos($_SERVER["argv"][1], '--date=') !== false) {
            $tmp  = explode('=', $_SERVER["argv"][1]);
            $date = date('Y-m-d', strtotime($tmp[1]));
        } elseif (isset($_GET['d'])) {
            $date = trim($_GET['d']);
        } else {
            $date = date('Y-m-d', strtotime('-1 days'));
        }
        //var_dump(getenv('ENVIRONMENT'));exit;
        // 脚本开始
        $script_start_time = microtime(true);
        $script_name       = GetConfigs::get($this->script_name, 'script');

        $logger = new Logger($script_name);
        $logger->pushHandler(new StreamHandler(LOG_PATH.DIRECTORY_SEPARATOR.date('Y-m-d').'.log'));
        $logger->addInfo('状态:start');

        $res = $this->start($date);

        # 脚本结束
        $res_msg         = $res ? '写入成功' : '写入失败';
        $script_end_time = microtime(true);
        $time_long       = get_subtraction($script_start_time, $script_end_time);

        $logger->addInfo(sprintf('状态:end||结果:\'%s\'||耗时:%s||日期:%s"', $res_msg, $time_long, $date));
    }


}