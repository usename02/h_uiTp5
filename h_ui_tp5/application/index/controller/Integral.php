<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/4 0004
 * Time: 9:34
 */
//积分管理
namespace app\index\controller;


class Integral extends Base
{
//    积分列表
    public  function IntegralList()
    {
        $this -> view -> assign('title', '会员积分列表');
        $this -> view -> assign('keywords','田雨微信会员管理系统' );
        $this -> view -> assign('desc','田雨后台');
        //获取列表数量
        $count =Integral::count();
        $this -> view -> assign('count',$count);
        $listRows = 2;//每页显示$page条数据
        $list = Integral::paginate($listRows, $simple = false);
            //分页显示输出
            $page=$list->render();
//           总页数
            $total = $list->total();
        $this -> view -> assign('list', $list);
        $this->assign('page', $page);
        //渲染管理员列表模板
        return $this -> view -> fetch('Integral/Integral-list');
    }
//    渲染会员个人积分管理
    public function integralEdit(Request $request)
    {
        $cardno = $request -> param('');
        $result = integralModel::get($cardno);
        //设置当前页面的seo模板变量
        $this->view->assign('title','编辑会员积分信息');
        $this->view->assign('shop_info',$result);//获取对象原始数据
        return $this->view->fetch('integral/integral-edit');
    }



//  会员积分说明
    public  function integralEescription()
    {
        //设置当前页面的seo模板变量
        $this->view->assign('title','会员积分说明');
        return $this->view->fetch('integral/integral-description');
    }
//  开卡送积分设置
    public function integralSet()
    {

    }
//  发布会员的积分公告
    public function information()
    {

    }
//    生日送积分设置
    public function B_set()
    {

    }
//    生日券发送时间设置
    public function B_timeSet()
    {

    }
}