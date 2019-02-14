<?php
/**
 * Created by PhpStorm.
 * User: danielshen
 * Date: 2018/12/11
 * Time: 下午4:01
 */

// 公共的方法

// 设置时区
date_default_timezone_set(getenv('TIMEZONE'));

// 设置脚本的超时时间
set_time_limit(TIMEOUT);

# 错误记录
set_error_handler('myErrorHandler');//开启自定义错误日志


//if (getenv('ENVIRONMENT') == 'test') $date = '2018-11-12';

/**
 * @param  $date 日期例如 2018-11-09
 * @return  获取时间戳
 * */
function getDateStr($date) {

    return strtotime($date);
}

/**
 * 获取monolog对象
 * @param $script_name
 * @param $log_path
 * @author 李小同
 * @date   2018-11-15 16:06:23
 * @return \Monolog\Logger
 */
function getLogger($script_name, $log_path = LOG_PATH) {

    $logger = new Monolog\Logger($script_name);
    $logger->pushHandler(new Monolog\Handler\StreamHandler($log_path.DIRECTORY_SEPARATOR.date('Y-m-d').'.log'));

    return $logger;
}

/**
 * @param $res 日志的内容
 *             通用日志记录
 * */
function writeLog($res) {

    if (!is_dir(ROOT.'/log')) mkdir(ROOT.'/log/', '0777');
    $file_name = ROOT.'/log/cron.log';
    file_put_contents($file_name, $res."\r\n", 8);
}

/**
 * @param $script_name 传入脚本的名字
 *                     脚本开始的日志
 * */
function writeLogStart($script_name) {

    $res = str_replace("'", "", sprintf("'%s'||统计脚本:'%s'||状态:start", date("Y-m-d H:i:s", time()), $script_name));
    if (!is_dir(ROOT.'/log')) mkdir(ROOT.'/log/', '0777');
    $file_name = ROOT.'/log/cron.log';

    file_put_contents($file_name, $res."\r\n", 8);
}

/**
 * @param $script_name 传入脚本的名字
 * @param $res_msg     脚本执行结果
 * @param $time_long   脚本执行时间
 * @param $date        执行那一天的脚本数据
 * */
function writeLogEnd($script_name, $res_msg, $time_long, $date) {

    $res = str_replace("'", "", sprintf("'%s'||统计脚本:'%s'||状态:end||结果:'%s'||耗时:%s||日期:%s", date("Y-m-d H:i:s", time()), $script_name, $res_msg, $time_long, $date));
    if (!is_dir(ROOT.'/log')) mkdir(ROOT.'/log/', '0777');
    $file_name = ROOT.'/log/cron.log';

    file_put_contents($file_name, $res."\r\n", 8);
}

/**
 * @param $start_time 开始时间
 * @param $end_time   结束时间
 * @return 返回时间差
 * */
function get_subtraction($start_time, $end_time) {

    return sprintf("%.2f", ($end_time - $start_time)).'s';
}

/**
 *  查询
 * */
function dbSelect($conn, $sql) {

    $rs = mysqli_query($conn, $sql);

    $rows = [];
    while ($row = mysqli_fetch_assoc($rs)) {
        $rows[] = $row;
    }

    return $rows;
}

// 插入多条数据
function dbDelAndInsertArr($conn, $table, $datas, $date = '') {

    // 先将该天内的数据删除 在进行插入数据
    if ($date) {
        $sql = "delete from ".$table."  where stat_day="." '$date'";
        mysqli_query($conn, $sql);
    }

    foreach ($datas as $data) {
        $key_str = '';
        $v_str   = '';
        foreach ($data as $key => $v) {
            if (!isset($v)) {
                die("error");
            }
            //$key的值是每一个字段s一个字段所对应的值
            $key_str .= $key.',';
            $v_str .= "'$v',";
        }
        $key_str = trim($key_str, ',');
        $v_str   = trim($v_str, ',');
        //判断数据是否为空
        $sql = "insert into $table ($key_str) values ($v_str)";

        mysqli_query($conn, $sql);
    }

    mysqli_close($conn);
}

// 插入单条数据
function dbDelAndInsert($conn, $table, $data, $date = '') {

    // 先将该天内的数据删除 在进行插入数据
    if (!empty($date)) {
        $sql = "delete from ".$table."  where stat_day="." '$date'";
        $res = mysqli_query($conn, $sql);
        //var_dump($res);
    }
    //var_dump($data);
    $key_str = '';
    $v_str   = '';
    foreach ($data as $key => $v) {
        if (!isset($v)) {
            die("error");
        }
        //$key的值是每一个字段s一个字段所对应的值
        if (!empty($key)) {
            $key_str .= $key.',';
            $v_str .= "'$v',";
        }

    }
    $key_str = trim($key_str, ',');
    $v_str   = trim($v_str, ',');
    //var_dump($key_str);
    //判断数据是否为空
    $sql = "insert into $table ($key_str) values ($v_str)";
    $res = mysqli_query($conn, $sql);
    //var_dump($res);
    mysqli_close($conn);

    return $res;
}

/**
 * 自定义记录错误日志，update from Internet
 * @param int    $errno   错误代码
 * @param string $errstr  错误消息内容
 * @param string $errfile 出错文件
 * @param int    $errline 出错行号
 * @author 李小同
 * @date   2018-11-14 15:18:02
 * @return bool
 */
function myErrorHandler($errno, $errstr, $errfile, $errline) {

    $log_file = ERROR_LOG_PATH.'/'.date('Y-m-d').'.log';
    $template = '['.date('Y-m-d H:i:s').'] ';

    switch ($errno) {
        case E_USER_ERROR:
            $template .= "用户ERROR级错误，必须修复 错误编号[$errno] $errstr ";
            $template .= "错误位置:$errfile:$errline\n";
            $log_file = sprintf($log_file, 'error');
            exit(1);//系统退出
            break;

        case E_USER_WARNING:
            $template .= "用户WARNING级错误，建议修复 错误编号[$errno] $errstr ";
            $template .= "错误位置:$errfile:$errline\n";
            $log_file = sprintf($log_file, 'warning');
            break;

        case E_USER_NOTICE:
            $template .= "用户NOTICE级错误，不影响系统，可不修复 错误编号[$errno] $errstr ";
            $template .= "错误位置:$errfile:$errline\n";
            $log_file = sprintf($log_file, 'notice');
            break;

        default:
            $template .= "未知错误类型: 错误编号[$errno] $errstr  ";
            $template .= "错误位置:$errfile:$errline\n";
            $log_file = sprintf($log_file, 'unknown');
            break;
    }
    file_put_contents($log_file, $template, FILE_APPEND);

    return true;
}

/**
 * 模拟post进行url请求
 * @param string       $url
 * @param string|array $post_data
 * @param array        $header
 * @return mixed
 */
function request_post($url = '', $post_data = [], $header = []) {

    $url = trim($url);
    if (empty($url)) return false;

    if (is_array($post_data)) $post_data = http_build_query($post_data);

    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
//	curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);

    return $data;
}


/**
 *
 * @param $config 配置
 * @param $date 执行脚本的天
 * @param 第n天的数据
 * */
function dbSwitch($n,$date,$config)
{
    $now_date = date ("Y-m-d", strtotime("+{$n} days", strtotime($date)));

    $config['database'] = $config['database']."_".str_replace('-','_',$now_date);

    return $config;
}
