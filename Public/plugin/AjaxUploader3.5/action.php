<?php
header("Content-Type:text/html; charset=utf-8;");
include_once('UploadApi.class.php');
$upload = new UploadApi();
if( !isset($_POST['action']) ){
	die('非法访问！');
}
//上传
if($_POST['action'] == 'upload' ){
	echo $upload->uploadFile();
	die;
}
//合并
if($_POST['action'] == 'merge' ){
	echo $upload->mergeFile();
	die;
}
echo '参数错误！';
die;