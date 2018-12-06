<?php
namespace app\index\controller;

use app\index\model\Test as t;

use think\Db;

class Test extends Base
{
    public function test(){

        var_dump( Db::table('user')->where('id',1)->select());
        return $this->view->fetch('index/test');

    }

}