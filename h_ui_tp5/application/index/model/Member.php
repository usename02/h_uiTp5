<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/1 0001
 * Time: 14:27
 */

namespace app\index\model;


use think\Model;
use traits\model\SoftDelete;
class Member extends Model
{
    //引用软删除方法集
    use SoftDelete;
//自动过滤掉不存在的字段
    protected $field = true;
    //设置当前表默认日期时间显示格式
    protected $dateFormat = 'Y/m/d';

    //定义表中的删除时间字段,来记录删除时间
    protected $deleteTime = 'delete_time';

    // 开启自动写入时间戳
    protected $autoWriteTimestamp = true;

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    protected $type = [
        'hiredate'=>'timestamp'
    ];
//定义表中的 服务号OPENID
    protected $OPENID_FFH = 'OPENID_FFH';
    // 定义自动完成的属性
    protected $insert = ['status' => 1];
}