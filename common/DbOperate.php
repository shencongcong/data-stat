<?php
/**
 * Created by PhpStorm.
 * User: danielshen
 * Date: 2018/12/11
 * Time: 下午4:08
 */

namespace common;

class DbOperate {

    private $link;
    private static $instance;
    private static $db;

    private function __construct($config) {

        $this->link = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
        self::$db   = $config['database'];
        if (!$this->link) {
            return false;
        }
        mysqli_query($this->link, "set names utf8");

    }

    public static function getDbInstance($config) {

        if ((!(self::$instance instanceof self)) || self::$db != $config['database']) {
            self::$instance = new DbOperate($config);
        }

        return self::$instance;
    }

    public function dbSelect($sql) {

        if (getenv('SQL_LOG')) {
            $logger = new \Monolog\Logger('SQL');
            $logger->pushHandler(new \Monolog\Handler\StreamHandler(SQL_LOG_PATH.DIRECTORY_SEPARATOR.date('Y-m-d').'.log'));
            $logger->addInfo($sql);
            unset($logger);
        }

        $rs = mysqli_query($this->link, $sql);

        $rows = [];
        while ($row = mysqli_fetch_assoc($rs)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * 取一行结果集
     * @param $sql
     * @author 李小同
     * @date   2018-11-17 15:07:47
     * @return array|mixed
     */
    public function dbFind($sql) {

        $res = $this->dbSelect($sql);
        if (empty($res)) {
            return [];
        } else {
            return $res[0];
        }
    }

    /**
     * 获取结果集第一行的指定字段
     * 若只有一列，可以不指定列名
     * @param $sql
     * @param $column
     * @author 李小同
     * @date   2018-11-17 15:13:02
     * @return bool
     */
    public function dbGetColumn($sql, $column = '') {

        $res = $this->dbSelect($sql);
        if (!isset($res[0][$column])) {
            return false;
        } else {
            if ($column == '' && count($res[0]) == 1) {

                $rs = array_values($res[0]);

                return $rs[0];
            } else {
                return $res[0][$column];
            }
        }
    }

    public function dbDelete($table, $where) {

    }

    public function dbDelAndInsert($table, $insert, $date) {

        if (!empty($date)) {
            $sql = "delete from ".$table."  where stat_day="." '$date'";
            $this->query($sql);
        }

        $key_str = '';
        $v_str   = '';
        foreach ($insert as $key => $v) {
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
        $sql     = "insert into $table ($key_str) values ($v_str)";
        $res     = $this->query($sql);

        return $res;
    }

    public function dbInsert($table, $insert) {

        $key_str = '';
        $v_str   = '';
        foreach ($insert as $key => $v) {
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
        $sql     = "insert into $table ($key_str) values ($v_str)";
        $res     = $this->query($sql);

        return $res;
    }

    /**
     * 批量插入
     * @param $table
     * @param $rows
     * @param $date
     * @author 李小同
     * @date   2018-11-15 20:24:04
     * @return int 成功返回插入的行数，失败返回0
     */
    public function dbDelAndMultiInsert($table, $rows, $date) {

        if (!isset($rows{0})) return 0;

        if (!empty($date)) {
            $sql = 'DELETE FROM %s WHERE stat_day = \'%s\' AND platform = \'%s\'';
            $sql = sprintf($sql, $table, $date, $rows[0]['platform']);
            $this->query($sql);
        }

        $sql    = 'INSERT INTO '.$table.' (%s) VALUES ';
        $fields = [];
        foreach ($rows as $row) {
            $fields = array_keys($row);
            break;
        }
        if (!isset($fields{0})) return false;
        $sql    = sprintf($sql, implode(',', $fields));
        $values = [];
        foreach ($rows as $key => $row) {
            $values[$key] = '(\''.implode('\',\'', array_values($row)).'\')';
        }
        if (!isset($values{0})) return false;
        $sql .= implode(',', $values);
        $res = $this->query($sql);

        return $res ? count($rows) : 0;
    }

    public function query($sql) {

        if (getenv('SQL_LOG')) {
            $logger = new \Monolog\Logger('SQL');
            $logger->pushHandler(new \Monolog\Handler\StreamHandler(SQL_LOG_PATH.DIRECTORY_SEPARATOR.date('Y-m-d').'.log'));
            $logger->addInfo($sql);
            unset($logger);
        }
        $rs = mysqli_query($this->link, $sql);

        return $rs;
    }

    private function __clone() {
        // TODO: Implement __clone() method.
    }

    private function __wakeup() {
        // TODO: Implement __wakeup() method.
    }

}
