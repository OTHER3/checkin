<?php
return [
    'DB_TYPE'   => 'mysql', // 数据库类型
	'DB_HOST'   => '192.168.43.200', // 服务器地址
	'DB_NAME'   => 'ques', // 数据库名
	'DB_USER'   => 'root', // 用户名
	'DB_PWD'    => 'dadi123456', // 密码
	'DB_PORT'   => 3306, // 端口
	'DB_PREFIX' => '', // 数据库表前缀 
    'DB_CHARSET'=> 'utf8', // 字符集
    
	'DEFAULT_MODULE' => 'Admin',
	'TMPL_PARSE_STRING' => array(     
		'__PUBLIC__' => WEB_PATH.'Public', // 更改默认的/Public 替换规则
	),

    //小程序配置
    'appId' => 'wxb2a50ce1f58b2910',
    'secret' => 'a3c5c2cd06f7790a17c1b8c46ac9fccf',
];