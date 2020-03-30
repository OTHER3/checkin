CREATE DATABASE `ques` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

use ques;

/* 后台账号表 */
CREATE TABLE `account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint unsigned NOT NULL default '1' COMMENT '帐号类型：1-普通账号，2-管理员',
  `account` varchar(255) NOT NULL COMMENT '帐号',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `account` (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台账号表';

/* 初始化用户 */
insert into account value(null, 2, 'admin', '21232f297a57a5a743894a0e4a801fc3', 1584278134, 1584278134);

/* 用户表 */
create table user (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT comment 'id',
  `type` tinyint unsigned not null default 1 comment '用户类型: 1-普通访客, 2-员工',
  shop_id int unsigned not null default 0 comment '所属门店',
  open_id varchar(255) not null default '' comment '微信open_id',
  name varchar(255) not null default '' comment '用户名',
  age tinyint not null default 0 comment '年龄',
  sex tinyint not null default 1 comment '性别: 1-男, 2-女',
  mobile varchar(100) not null default '' comment '手机号码',
  id_card varchar(100) not null default '' comment '身份证号码',
  status tinyint not null default 1 comment '状态: 1-正常, 2-已离职',
  created_at int unsigned not null default 0,
  updated_at int unsigned not null default 0,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`),
  KEY `open_id` (`open_id`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

/* 门店表 */
CREATE TABLE `shop` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(255) NOT NULL COMMENT '门店名称',
  `account` varchar(255) NOT NULL COMMENT '帐号',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `manager` varchar(255) NOT NULL COMMENT '负责人',
  `mobile` varchar(100) NOT NULL COMMENT '联系电话',
  `address` varchar(255) NOT NULL default '' COMMENT '地址',
  `business_license` varchar(255) NOT NULL default '' COMMENT '营业执照图片',
  `status` tinyint unsigned not null default 1 comment '账号状态: 1-待审核, 2-正常, 3-禁用',
  `creator_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建人',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `account` (`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='门店表';

/* 员工体温表 */
CREATE TABLE `user_temperature` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL COMMENT '用户id',
  `temperature` varchar(10) NOT NULL COMMENT '体温',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='员工体温表';

/* 出入登记表 */
create table visit_log (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT comment 'id',
  ques_id int unsigned not null default 0 comment '所属问卷id',
  shop_id int unsigned not null default 0 comment '门店id',
  user_id int unsigned not null default 0 comment '用户id',
  `name` varchar(255) not null default '' comment '姓名',
  age tinyint not null default 0 comment '年龄',
  sex tinyint not null default 1 comment '性别: 1-男, 2-女',
  mobile varchar(100) not null default '' comment '手机号码',
  id_card varchar(100) not null default '' comment '身份证号码',
  results text comment '问卷结果, 保存json数据: {"topic_id":{"answer":"选项的index 或者 填空答案 (多选和多个填空, 使用||隔开)","custom":"保存自定义选项值"}}',
  created_at int unsigned not null default 0,
  updated_at int unsigned not null default 0,
  PRIMARY KEY (`id`),
  KEY `ques_id` (`ques_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='出入登记表';

/* 问卷题目表 */
create table topic (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT comment '题目id',
  topic_type int unsigned not null default 0 comment '题目类型 1-单选题, 2-多选题, 3-填空题',
  title varchar(1000) not null comment '题目标题',
  comment varchar(1000) not null default '' comment '详细说明(非必填)',
  `option` varchar(1000) not null default '' comment '选项json数据: [{"type": "1-默认, 2-自定义选项","index": "选项键","value": "选项值","comment": "选项说明",}]',
  ques_id int unsigned not null default 0 comment '所属问卷id',
  created_at int unsigned not null default 0,
  updated_at int unsigned not null default 0,
  PRIMARY KEY (`id`),
  KEY `ques_id` (`ques_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问卷题目表';

/* 题目初始化 */
insert into topic values 
(null, 1, "身体状况良好", "", '[{"type":1,"index":0,"value":"是","comment":""},{"type":1,"index":1,"value":"否","comment":""}]', 0, 0, 0),
(null, 1, "最近两周内是否有境外（包括香港，澳门，台湾）居住或旅行历史", "", '[{"type":1,"index":0,"value":"是","comment":""},{"type":1,"index":1,"value":"否","comment":""}]', 0, 0, 0),
(null, 3, "详细的国家或地区", "", '', 0, 0, 0),
(null, 1, "最近两周内是否有湖北或其他有病例报告社区的旅行史或居住史", "", '[{"type":1,"index":0,"value":"有","comment":""},{"type":1,"index":1,"value":"无","comment":""}]', 0, 0, 0),
(null, 1, "最近两周内曾接触过新型冠状病毒感染者（核酸检测为阳性）", "", '[{"type":1,"index":0,"value":"有","comment":""},{"type":1,"index":1,"value":"无","comment":""}]', 0, 0, 0),
(null, 1, "最近两周内曾接触过湖北或来着病例报告社区的发热或有呼吸道症状的患者", "", '[{"type":1,"index":0,"value":"有","comment":""},{"type":1,"index":1,"value":"无","comment":""}]', 0, 0, 0),
(null, 1, "最近家庭成员是否有聚集性发病2人及以上", "", '[{"type":1,"index":0,"value":"有","comment":""},{"type":1,"index":1,"value":"无","comment":""}]', 0, 0, 0),
(null, 1, "您近期7日内是否有异常症状", "", '[{"type":1,"index":0,"value":"有","comment":""},{"type":1,"index":1,"value":"无","comment":""}]', 0, 0, 0);

-- 身体状况良好 是否 
-- 最近两周内是否有境外（包括香港，澳门，台湾）居住或旅行历史 是否 
-- 详细的国家或地区 非必填
-- 最近两周内是否有湖北或其他有病例报告社区的旅行史或居住史 有无 
-- 最近两周内曾接触过新型冠状病毒感染者（核酸检测为阳性） 有无
-- 最近两周内曾接触过湖北或来着病例报告社区的发热或有呼吸道症状的患者 有无
-- 最近家庭成员是否有聚集性发病2人及以上 有无
-- 您近期7日内是否有异常症状 有无

/* 二维码表 */
create table qr_code (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT comment '题目id',
  shop_id int unsigned not null default 0 comment '门店id',
  ques_id int unsigned not null default 0 comment '题目id',
  identity tinyint unsigned not null default 0 comment '类型 1-访客 2-员工',
  path varchar(1000) not null default '' comment '二维码路径',
  created_at int unsigned not null default 0,
  updated_at int unsigned not null default 0,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`),
  KEY `ques_id` (`ques_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='二维码表';