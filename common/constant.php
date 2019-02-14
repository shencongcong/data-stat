<?php
/**
 * Created by PhpStorm.
 * User: danielshen
 * Date: 2018/12/11
 * Time: 下午4:10
 */

# 系统根目录
define('ROOT', dirname(dirname(__FILE__)));

# 各种日志的目录
define('LOG_PATH', ROOT.'/log');
if (!is_dir(LOG_PATH)) mkdir(LOG_PATH, 0777, true);

# 错误日志的目录
define('ERROR_LOG_PATH', LOG_PATH.'/error');
if (!is_dir(ERROR_LOG_PATH)) mkdir(ERROR_LOG_PATH, 0777, true);

# SQL日志的目录
define('SQL_LOG_PATH', LOG_PATH.'/sql');
if (!is_dir(SQL_LOG_PATH)) mkdir(SQL_LOG_PATH, 0777, true);

# 设置脚本的超时时间
define('TIMEOUT', 72000);
