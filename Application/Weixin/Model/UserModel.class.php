<?php

namespace Weixin\Model;

use Think\Model;

class UserModel extends Model
{
    protected $tableName = 'user';
    protected $tablePrefix = '';

    const TYPE_VISITOR = 1;     //访客
    const TYPE_STAFF   = 2;     //员工
}