<?php
namespace app\index\controller;
use app\index\model\User as UserModel;
use think\Request;
use think\Session;

class Index extends Base
{

    //渲染首页模板
    public function index()
    {
        //判断用户是否登录
        $this-> islogin();
        return  $this->view -> fetch('index');
    }

}

