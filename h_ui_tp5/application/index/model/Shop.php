<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/5 0005
 * Time: 11:40
 */

namespace app\index\model;

use traits\model\SoftDelete;
use think\Model;
class Shop extends Model
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

    // 定义自动完成的属性
    protected $insert = ['status' => 1];






}