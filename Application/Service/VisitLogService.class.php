<?php
namespace Service;

/**
 * 出入登记
 */
class VisitLogService
{
    //题目类型 1-单选题, 2-多选题, 3-填空题
    const TOPIC_TYPE_SINGLE_CHOICE = 1;
    const TOPIC_TYPE_MULTI_CHOICE  = 2;
    const TOPIC_TYPE_COMPLETION    = 3;

    private $TopicModel;
    private $VisitLogModel;

    public function __construct()
    {
        $this->TopicModel = D('topic');
        $this->VisitLogModel = D('visit_log');
    }

    /**
     * 按问卷id获取出入登记题目
     *
     * @param int $ques_id
     * @return array
     */
    public function getTopic(int $ques_id = 0)
    {
        $topic_list = $this->TopicModel->where(['ques_id' => $ques_id])->order('id asc')->select();
        foreach ($topic_list as $key => $value) {
            $topic_list[$key]['option'] = json_decode($value['option'], true);
            //处理下，小程序方便循环展示
            switch ($value['topic_type']) {
                case 1://单选
                    $topic_list[$key]['answer'] = $topic_list[$key]['option'][0]['index'];//默认第一个为用户选择
                    break;
                case 3://输入框
                    $topic_list[$key]['answer'] = '';//默认空为用户答案
                    break;
            }
        }

        return $topic_list;
    }

    /**
     * 保存出入登记结果
     *
     * @param array $visit_log
     * @return bool
     */
    public function save(int $user_id, int $shop_id, array $visit_log)
    {
        //问卷调查结果： 题目id => 结果
        // -- 身体状况良好 是否 
        // -- 最近两周内是否有境外（包括香港，澳门，台湾）居住或旅行历史 是否 
        // -- 详细的国家或地区 非必填
        // -- 最近两周内是否有湖北或其他有病例报告社区的旅行史或居住史 有无 
        // -- 最近两周内曾接触过新型冠状病毒感染者（核酸检测为阳性） 有无
        // -- 最近两周内曾接触过湖北或来着病例报告社区的发热或有呼吸道症状的患者 有无
        // -- 最近家庭成员是否有聚集性发病2人及以上 有无
        // -- 您近期7日内是否有异常症状 有无

        //问卷调查结果示例：题目id => 答案
        // 'answer' => "选项的index 或者 填空答案 (多选和多个填空, 使用||隔开)",
        // 'custom' => '保存自定义选项值',
        // $visit_log['results'] = [
        //     7  => ['answer' => '0', 'custom' => ''],
        //     8  => ['answer' => '1', 'custom' => ''],
        //     9  => ['answer' => '没有', 'custom' => ''],
        //     10 => ['answer' => '1', 'custom' => ''],
        //     11 => ['answer' => '1', 'custom' => ''],
        //     12 => ['answer' => '1', 'custom' => ''],
        //     13 => ['answer' => '1', 'custom' => ''],
        //     14 => ['answer' => '1', 'custom' => ''],
        // ];

        $data = [
            'ques_id'    => 0,  //问卷id， 暂无
            'shop_id'    => $shop_id,
            'user_id'    => $user_id,
            'name'       => $visit_log['name'],
            'age'        => $visit_log['age'],
            'sex'        => $visit_log['sex'],
            'mobile'     => $visit_log['mobile'],
            'id_card'    => $visit_log['id_card'],
            'results'    => json_encode($visit_log['results']),
            'created_at' => time(),
            'updated_at' => time(),
        ];

        return $this->VisitLogModel->add($data);
    }

    /**
     * 获取最后一次填写的出入登记结果 (可指定某天)
     *
     * @param integer $user_id
     * @param integer $ques_id
     * @param integer $shop_id
     * @param string $date 指定某天
     * @return array
     */
    public function getLastVisitLog($user_id, $ques_id = 0, $shop_id = 0, $date = '')
    {
        $where = [
            'user_id' => $user_id,
            'ques_id' => $ques_id,
        ];

        //获取指定店铺的登记
        if (!empty($shop_id)) {
            $where['shop_id'] = $shop_id;
        }

        //指定获取某天最后一条出入登记
        if (!empty($date) && strtotime($date)) {
            $date = date('Y-m-d', strtotime($date));
            $where['created_at'] = [
                'between',
                [strtotime($date), strtotime($date . ' 23:59:59')]
            ];
        }

        $last = $this->VisitLogModel->where($where)->order('id desc')->find();
        if (!empty($last['results'])) {
            $last['results'] = json_decode($last['results'], true);
        }

        return $last;
    }

    /**
     * 处理出入登记参数
     * @param $shop_id
     * @param $ques_id
     * @param $param
     * @return bool
     * @throws \Exception
     */
    public function dealAddParam($shop_id, $ques_id, $param)
    {
        $results = [];
        $needle = 'topic_';

        //填写地址特殊处理，后续干掉
        if (empty($param[$needle . '2']) && empty($param[$needle . '3'])) {
            throw new \Exception('请填写详细的国家或地区');
        }

        foreach ($param as $key => $value) {
            if (strpos($key, $needle) !== false) {
                $index = substr($key, strlen($needle));
                $results[$index] = [
                    'answer' => $value,
                    'custom' => '',
                ];
            }
        }
        $visit_log = [
            'ques_id'    => $ques_id,
            'name'       => $param['name'],
            'age'        => $param['age'],
            'sex'        => $param['sex'],
            'mobile'     => $param['mobile'],
            'id_card'    => $param['id_card'],
            'results'    => $results,
        ];
        $user_id = getUserId();

        if (empty($visit_log['name'])) {
            throw new \Exception('姓名必填');
        }

        if (empty((int)$visit_log['age'])) {
            throw new \Exception('年龄必填');
        }

        if ($visit_log['age'] < 0 || $visit_log['age'] > 127) {
            throw new \Exception('年龄错误');
        }

        if (!in_array($param['sex'], [1, 2])) {
            throw new \Exception('性别参数错误');
        }

        if (!preg_match_all("/^1[34578]\d{9}$/", $param['mobile'])) {
            throw new \Exception('手机号格式错误');
        }

        if (!preg_match_all( "/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $param['id_card'])) {
            throw new \Exception('身份证格式错误');
        }

        $result = $this->save($user_id, $shop_id,  $visit_log);
        if ($result) {
            $user = M('user')->find($user_id);
            if (empty($user['name'])) {//只记录一次用户信息
                $data = [
                    'name' => $param['name'],
                    'age' => $param['age'],
                    'sex' => $param['sex'],
                    'mobile' => $param['mobile'],
                ];
                M('user')->where("id={$user_id}")->save($data);
            }
        }
        return $result;
    }

    /**
     * 获取用户最后一次登记
     * @param $user_id
     * @param $shop_id
     * @param $ques_id
     * @return array
     * @throws \Exception
     */
    public function userTopic($user_id, $shop_id, $ques_id)
    {
        //step1 获取用户信息，查看用户是员工还是访客
        $userInfo = M('user')->find($user_id);
        if (empty($userInfo)) {
            throw new \Exception('用户不存在');
        }

        //如果用户是员工身份，且获取的是自己门店的出入登记，则获取历史的记录即可
        if ($userInfo == 2 && $shop_id == $userInfo['shop_id']) {
            //返回员工在该门店登记的信息
            $log = $this->getLastVisitLog($user_id, $ques_id, $shop_id);
        } else {
            //其余的都是访客身份，获取当天的记录信息
            $log = $this->getLastVisitLog($user_id, $ques_id, $shop_id, date('Y-m-d', time()));
        }

        //如果已有填写记录，将问卷信息获取到
        if (!empty($log)) {
            $topic = $this->getTopic($ques_id);

            /**
             * 格式为：
             *  [
             *      ques_id => [
             *          answer => 答案,
             *          custom => 备注,暂无用
             *      ]
             *  ]
             */
            $user_from = $log['results'];//用户的问卷填写内容

            foreach ($topic as $key => $value) {
                $id = $value['id'];
                if (isset($user_from[$id])) {//如果有对应答案记录，则填充进去
                    $topic[$key]['answer'] = $user_from[$id]['answer'];
                }
            }

            $log['topic'] = $topic;
            $log['created_at'] = date('Y-m-d H:i:s', $log['created_at']);
            $log['updated_at'] = date('Y-m-d H:i:s', $log['updated_at']);
        }

        return $log;
    }

    /**
     * 示例
     */
    public function example()
    {
        //获取题目
        $topic = $this->getTopic();

        //记录出入登记结果
        $visit_log = [
            'name'       => '凌凌义',
            'age'        => 18,
            'sex'        => 1,
            'mobile'     => '155155',
            'id_card'    => '888999777',
        ];
        $visit_log['results'] = [
            7  => ['answer' => '0', 'custom' => ''],
            8  => ['answer' => '1', 'custom' => ''],
            9  => ['answer' => '我是非洲回来的', 'custom' => ''],
            10 => ['answer' => '1', 'custom' => ''],
            11 => ['answer' => '1', 'custom' => ''],
            12 => ['answer' => '1', 'custom' => ''],
            13 => ['answer' => '1', 'custom' => ''],
            14 => ['answer' => '1', 'custom' => ''],
        ];
        $this->save(2, 1, $visit_log);

        //获取最后一次登记结果
        $visit_log = $this->getLastVisitLog(1);

        foreach ($topic as $value) {
            $result = $visit_log['results'][$value['id']];
            $answer_array = explode('||', $result['answer']);

            switch ($value['topic_type']) {
                //单选题
                case VisitLogService::TOPIC_TYPE_SINGLE_CHOICE:
                    p('单选题: ' . $value['title']);
                    foreach ($value['option'] as $key => $option) {
                        $checked = '';
                        if (in_array($option['index'], $answer_array)) {
                            $checked = 'checked';
                        }
                        p("{$option['index']} - {$option['value']} - {$checked}");
                    }
                    break;
                //多选题
                case VisitLogService::TOPIC_TYPE_MULTI_CHOICE:
                    p('多选题: ' . $value['title']);
                    foreach ($value['option'] as $key => $option) {
                        $checked = '';
                        if (in_array($option['index'], $answer_array)) {
                            $checked = 'checked';
                        }
                        p("{$option['index']} - {$option['value']} - {$checked}");
                    }
                    break;
                //填空题
                case VisitLogService::TOPIC_TYPE_COMPLETION:
                    p('填空题: ' . $value['title']);
                    p("结果: " . current($answer_array));
                    break;
            }
        }
    }

    /**
     * 更新出入登记
     *
     * @param integer $id
     * @param array $data
     * @return bool
     */
    public function updateVisitLog(int $id, array $data)
    {
        //@todo 数据校验
        return $this->VisitLogModel->where(['id' => $id])->save($data);
    }

    public function delete(int $id)
    {
        $detail = $this->VisitLogModel->where(array('id'=>$id))->find();
        if(!$detail || empty($detail)){
            throw new \Exception("删除失败！数据不存在！");
        }
        $result = $this->VisitLogModel->where(array('id'=>$id))->delete();
        if(!$result){
            throw new \Exception("删除失败！请检查后重试！");
        }

        return true;
    }
}