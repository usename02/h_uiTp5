<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/1 0001
 * Time: 14:23
 */

namespace app\index\controller;
use think\Controller;
use think\Session;
use think\Request;
class Base extends Controller
{
    //初始化操作
        protected function _initialize()
    {
        parent::_initialize();//继承父类的构造参数，::是调用类中的静态方法或者常量,属性的符号

        define('USER_ID', Session::has('user_id') ? Session::get('user_id'):null);//定义全局常量，值为session值
    }
    /**
     * 检测用户是否登录，未登录跳转到登录页面
     */
    protected function islogin()
    {
        if(is_null(USER_ID))
    {
        $this  -> error('用户未登录，无权访问',url('user/login'));
    }
    }

    /**
     * 检测用户是否重复登录，如重复跳转到登录页面
     */
    protected  function  alreadyLogin()
    {
        if(!is_null(USER_ID)){//is_null() 为空将返回TRUE，其它的情况就是FALSE
            $this -> error('用户已经登陆,请勿重复登陆,即将返回首页',url('index/index'));
        }
    }

}