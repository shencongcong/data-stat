<?php
/**
 * Created by PhpStorm.
 * User: danielshen
 * Date: 2018/12/14
 * Time: 下午3:00
 */

namespace script;

use \common\GetConfigs;
use \common\DbOperate;
require dirname(__DIR__).'/Autoload.php';

class ActiveUser extends Basic{
    public $script_name = 'active_user';

    public  function start($date)
    {
        $start_time = getDateStr($date .' 00:00:00');
        $end_time = getDateStr($date .' 23:59:59');

        $bill_statistics_joyo_1 = GetConfigs::get('bill_statistics_joyo_1','db');
        $bill_statistics_joyo_1['database'] = $bill_statistics_joyo_1['database']."_".str_replace('-','_',$date);
        $db_bill_statistics_joyo_1 = DbOperate::getDbInstance($bill_statistics_joyo_1);

        $sql1 = sprintf("SELECT count(distinct(uin)) active_user from  CBillLogin where bill_time>'%s' and bill_time<'%s' ",$start_time,$end_time);
        $res1 = $db_bill_statistics_joyo_1->dbSelect($sql1);
        //var_dump($res1);exit;
        $active_user = $res1[0]['active_user'];
        $insert_arr = [];
        $insert_arr['stat_day'] = $date;
        $insert_arr['game_id'] = GetConfigs::get('game_id','common');
        $insert_arr['active_user'] = $active_user;
        $insert_arr = json_decode(str_replace('null',0,json_encode($insert_arr)));
        $hero_data_stat =  GetConfigs::get('hero_data_stat','db');
        $res = DbOperate::getDbInstance($hero_data_stat)->dbDelAndInsert('active_user',$insert_arr,$date);

        return $res;
    }
}

$action = new ActiveUser();

$action->run();