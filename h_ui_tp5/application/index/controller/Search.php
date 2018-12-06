<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3 0003
 * Time: 11:23
 */

namespace app\index\controller;
use think\Db;
class Search extends Base
{
    public function index()
    {
//        接收搜索栏输入的关键字
        $searchKeyword = input('searchKeyword');
//        判断关键字是否为空
//
        if ($searchKeyword){
            $map['name'] = ['like','%'.$searchKeyword.'%'];
            $searchres = Db::table('user')->where($map)->find();
            $this->assign( 'list',array(
                'searchres'=>$searchres,
                '$earchKeyword'=>$searchKeyword
            ));
        }else{
            $this->assign( 'list',array(
                'searchres'=>null,
                '$searchKeyword'=>'暂无数据'
            ));
        }
var_dump($searchKeyword);
        var_dump($searchres);

        return $this->fetch('admin/admin_search');
    }
}