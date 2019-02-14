<?php
/**
 * Created by PhpStorm.
 * User: danielshen
 * Date: 2018/12/11
 * Time: 下午4:01
 */

/**
 * 自动加载脚本
 * */

function my_autoload($classname) {

    $classname = str_replace('\\', '/', $classname);
    require ROOT.'/'.$classname.'.php';
}

include 'vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(ROOT);
$dotenv->load();

// 引入自己封装的函数
require_once(ROOT.'/common/function.php');

spl_autoload_register('my_autoload');
