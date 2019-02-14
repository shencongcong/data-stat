#! /bin/bash
#
date=$(date -d "-1 day" +"%F")

#日志管理
php /data/crontab/hero-data-stat/script/LogManage.php --date=$date


#计算统计数据
php /data/crontab/hero-data-stat/script/ActiveUser.php --date=$date