<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/1 0001
 * Time: 14:23
 */

namespace app\index\controller;
use think\Request;
use app\index\model\Member as MemberModel;
use think\Db;
use think\session;
class Member extends Base
{
//    会员列表
    public function  memberList()
    {
        //获取所有商铺表member数据
        $result = MemberModel::all(
            function ($query){
                $query->where('ID')->find();
            }
        );
        $count = Db::name('member')->count();
//        遍历member表
        foreach ($result as $value){
            $data = [
                'ID' => $value->ID,  //主键
                'NAME' => $value->NAME,  //店仓名
                'password'=>$value->password,//密码
                'status'=>$value->status,//判断值
                'delete_time'=> $value->delete_time,//删除时间
                'is_delete' => $value->is_delete,   //判断删除状态
            ];
            //,保存到数组   $MenberList中
            $MemberList[] = $data;
        }
        $page = 5;
        $list = MemberModel::paginate($page);
        //分页显示输出
        $page=$list->render();
//        var_dump($shopList);
        $this -> view -> assign('list', $list);
//        var_dump($shopList);
        $this -> view -> assign('member', $MemberList);
        $this -> view -> assign('count', $count);
        //设置当前页面的seo模板变量
        $this->view->assign('title','编辑会员');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','田雨微信管理客户端');
        //测试数据
//        var_dump($shopList);
//        var_dump($count);
//        dump($result);
        return $this -> view -> fetch('member/member-list');
    }
    //渲染商铺编辑界面
    public function memberEdit(Request $request)
    {
//去处要编辑的数据的id
        $member_id = $request -> param('');

//        var_dump($Shop_id);
        //根据ID进行查询(通过模型方法)
        //**模型方法取不到 一直是空值 所以直接调用数据库Db的方法*
        $result = MemberModel::get($member_id);
//        $result =  Db::table('member')->where('id',1)->find();
//      var_dump($result);
        //tp5中通过模型select返回的是对象 通过db select返回的是数组
        //设置当前页面的seo模板变量
        $this->view->assign('title','编辑会员信息');
        $this->view->assign('member_info',$result);//获取对象原始数据
        //渲染当前的编辑模板
        return $this->view->fetch('member/member-edit');
    }
    //商铺状态变更
    public function setStatus(Request $request)
    {
        $member_id = $request -> param('ID');
//        $result = ShopModel::get($shop_id);
        $result = Db::table('shop')->where('ID',1)->find();
        if($result->getData('STATUS') == 1) {
            MemberModel::update(['STATUS'=>0],['ID'=>$member_id]);
        } else {
            MemberModel::update(['STATUS'=>1],['ID'=>$member_id]);
        }
    }
    //会员更新
    public function doEdit(Request $request)
    {
        //$data存储ajax的发送的数据
        $data = $request-> param();
        //去掉表单中为空的数据,即没有修改的内容
        foreach ($data as $key => $value ){
            if (!empty($value)){
                $data[$key] = $value;
            }
        }
        //设置更新条件
        $condition = ['name'=>$data['NAME']];
        //更新当前记录
        $result = MemberModel::update($data,$condition);
        //设置返回数据
        $status = 0;
        $message = '更新失败,请检查';
        //检测更新结果,将结果返回给shop_edit模板中的ajax提交回调处理
        if (true == $result) {
            $status = 1;
            $message = '恭喜, 更新成功!!!';
        }
        return ['status'=>$status, 'message'=>$message];

    }
//    修改密码 界面渲染
    public  function MemberPw(Request $request)
    {
        $member_id = $request-> param('ID');
        $result = MemberModel::get($member_id);
        $this->view->assign('title','添加会员');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','田雨后台');
        $this->view->assign('member_info',$result);
        return $this->view->fetch('member/member-pw');
    }
//    修改密码
    public function doPw(Request $request)
    {
        //$data存储ajax的发送的数据
        $member_id = $request-> param('ID');
        $result = MemberModel::get($member_id);
        //去掉表单中为空的数据,即没有修改的内容
        foreach ($data as $key => $value ){
            if (!empty($value)){
                $data[$key] = $value;
            }
        }
        //设置更新条件
        $condition = ['name'=>$data['NAME']];

        //更新当前记录
        $result = MemberModel::update($data,$condition);
        //设置返回数据
        $status = 0;
        $message = '更新失败,请检查';
        //检测更新结果,将结果返回给shop_edit模板中的ajax提交回调处理
        if (true == $result) {
            $status = 1;
            $message = '恭喜, 更新成功!!!';
        }
        return ['status'=>$status, 'message'=>$message];

    }
    //渲染 会员添加界面
    public function MemberAdd()
    {
        $this->view->assign('title','添加会员');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','田雨后台');
        return $this->view->fetch('member_add');
    }
//    添加商铺
    public function   addMember(Request $request)
    {
        $data = $request -> param();
        $status = 1;
        $message = '添加成功';
//验证规则（数组）
        $rule = [

        ];
        //验证数据
        $result = $this -> validate($data, $rule);
//验证判断
        $user=1;
        if ($result === true) {
            $user= MemberModel::create($request->param());
            if ($user === null) {
                $status = 0;
                $message = '添加失败了呢···';
            }
        }
        return ['status'=>$status, 'message'=>$message];
    }

    //添加会员
    public  function doAdd(Request $request)
    {
        //从提交表单中获取数据
        $data = $request -> param();

        //向表中新增记录
        $result = ShopModel::create($data);

        //设置返回数据
        $status = 0;
        $message = '添加失败,请检查';

        //检测更新结果,将结果返回给shop_add模板中的ajax提交回调处理
        if (true == $result) {
            $status = 1;
            $message = '恭喜, 添加成功~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }
    //渲染已删除会员界面
    public  function doDel(){
        $this->view->assign('title','删除会员');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','田雨后台');
//$result = ShopModel::get('is_detele'==1);//取到的是空值 null
//        $result =  Db::table('shop')->where('is_Delete','=','1');
        $result = MemberModel::all(
            function ($query){
                $query->where('ID')->find();
            }
        );

        foreach ($result as $value){
            $data = [
                'ID' => $value->ID,  //主键
                'NAME' => $value->NAME,  //店仓名
                'delete_time'=> $value->delete_time,//删除时间
                'is_delete' => $value->is_delete,   //判断删除状态
            ];
            //,保存到数组   $shopList中
            $MemberList[] = $data;
        }

        //将所有数据赋值给当前模板
        $this->view->assign('shop', $MemberList);
        return $this->view->fetch('Member_del');
    }
    //软删除操作
    public function deleteMember(Request $request)
    {
        $Member_id = $request -> param('id');
        ShopModel::update(['is_delete'=>1],['id'=> $Member_id]);
        ShopModel::destroy($Member_id);

    }
    //恢复删除操作
    public  function unDel()
    {
        ShopModel::update(['delete_time'=>NULL],['is_delete'=>0]);
    }
    //恢复删除操作
    public function unDelete()
    {
        ShopModel::update(['delete_time'=>NULL],['is_delete'=>0]);
    }
}