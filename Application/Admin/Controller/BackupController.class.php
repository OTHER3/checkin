<?php
namespace Admin\Controller;

use Think\Controller;

class BackupController extends AuthController
{
    public function index()
    {
        $file_name = date('Y_m_d') . '.sql';
        $backup_path = ROOT_PATH . '/DB_Backup/';
        if (!is_dir($backup_path)) {
            mkdir($backup_path, 0777);
        }
        if(is_file($backup_path . $file_name)){
            unlink($backup_path . $file_name);
        }

        sleep(1);
        $this->exportDB($backup_path . $file_name);
        $this->download($backup_path, $file_name);
    }

    /**
     * 导出数据库备份
     */
    public function exportDB($file_name)
    {
        $db_name = 'activation_code'; //C('DB_NAME');
        $info = "-- ----------------------------\r\n";
        $info .= "-- 日期：" . date("Y-m-d H:i:s", time()) . "\r\n";
        $info .= "-- MySQL - 5.1.73 : Database - " . $db_name . "\r\n";
        $info .= "-- ----------------------------\r\n\r\n";
        $info .= "CREATE DATABASE IF NOT EXISTS `" . $db_name . "` DEFAULT CHARACTER SET utf8 ;\r\n\r\n";
        $info .= "USE `" . $db_name . "`;\r\n\r\n";
        file_put_contents($file_name, $info, FILE_APPEND);

        $model = M();
        //查询所有表
        $sql = "show tables";
        $table_list = $model->query($sql);
        $table_list = array_column($table_list, 'tables_in_' . $db_name);
        foreach ($table_list as $table_name) {

            //查询表结构
            $sql_table = "show create table `" . $table_name . '`';
            $res = $model->query($sql_table);

            $info_table = "-- ----------------------------\r\n";
            $info_table .= "-- Table structure for `" . $table_name . "`\r\n";
            $info_table .= "-- ----------------------------\r\n\r\n";
            $info_table .= "DROP TABLE IF EXISTS `" . $table_name . "`;\r\n\r\n";
            $info_table .= $res[0]['create table'] . ";\r\n\r\n";

            //查询表数据
            $info_table .= "-- ----------------------------\r\n";
            $info_table .= "-- Data for the table `" . $table_name . "`\r\n";
            $info_table .= "-- ----------------------------\r\n\r\n";
            file_put_contents($file_name, $info_table, FILE_APPEND);

            $sql_data = "select * from `" . $table_name . '`';
            $data = $model->query($sql_data);
            $count = count($data);
            if ($count < 1) continue;

            foreach ($data as $key => $value) {
                $sqlStr = "INSERT INTO `" . $table_name . "` VALUES (";
                foreach ($value as $v_d) {
                    $v_d = str_replace("'", "\'", $v_d);
                    $sqlStr .= "'" . $v_d . "', ";
                }

                //需要特别注意对数据的单引号进行转义处理
                //去掉最后一个逗号和空格
                $sqlStr = substr($sqlStr, 0, strlen($sqlStr) - 2);
                $sqlStr .= ");\r\n";
                file_put_contents($file_name, $sqlStr, FILE_APPEND);
            }
            $info = "\r\n";
            file_put_contents($file_name, $info, FILE_APPEND);
        }
        return true;
    }

    public function download($path, $file_name){
        $full_path = $path . $file_name;
        if(!is_file($full_path)){
            echo '文件不存在！';
            die;
        }

        // 打开文件
        $file = fopen($full_path, "r");
        Header("Content-type:application/octet-stream"); 
        Header("Accept-Ranges:bytes");
        //header("Content-Type:application/msexcel");     //当下载的不是excel文件时，可以注释此行
        Header("Accept-Length:".filesize($full_path)); 
        Header("Content-Disposition:attachment;filename=".$file_name); 
        echo fread($file, filesize($full_path)); 
        fclose($file);
    }
}