<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/5 0005
 * Time: 11:39
 */

namespace app\index\controller;
use app\index\model\Shop as ShopModel;
use think\session;
use traits\model\SoftDelete;
use think\Request;
use think\Db;
class Shop extends Base
{
    //商铺列表
    public function  shopList()
    {
        //获取所有商铺表shop数据
        $result = ShopModel::all(
            function ($query){
                $query->where('ID')->find();
            }
        );
//    var_dump($result);
    //测试是否从数据库读取到数据
        //        var_dump(Db::table('shop')->where('id',1)->find());
        //获取记录数量
//        获取到的值为0 不知道错误在哪只能换一个方法
        $count = ShopModel::count();

//            $count = Db::name('shop')->count();

//        遍历shop表
        foreach ($result as $value){
            $data = [
                'ID' => $value->ID,  //主键
                'storeID' => $value->storeID,  //商铺id
                'CODE' => $value->CODE  ,  //系统编号
                'NAME' => $value->NAME,  //店仓名
                'TYPE' => $value->TYPE,  //店仓类型
                'ISRETAIL' => $value->ISRETAIL,  //是否允许零售
                'CITY' => $value->CITY,  //城市
                'STATUS' => $value->STATUS,      //店铺状态
                'CUSTOMER' => $value->CUSTOMER,  //所属经销商
                'QRCODE' => $value->QRCODE,      //二维码
                'SUPPLEMENT'=>$value->SUPPLEMENT,//细节补充说明
                'create_time'=> $value->create_time,//创建时间
                'update_time'=> $value->update_time,//更新时间
                'delete_time'=> $value->delete_time,//删除时间
                'is_delete' => $value->is_delete,   //判断删除状态
            ];
            //,保存到数组   $shopList中
            $shopList[] = $data;
        }
        $listRows = 5;
        $list = ShopModel::paginate($listRows , $simple = false,[
                    'type'=>'Bootstrap6'
                                                            ]);
        //分页显示输出
        $page=$list->render();
        $this -> view -> assign('page', $page);
        $this -> view -> assign('list', $list);
        $this -> view -> assign('shop', $shopList);
        $this -> view -> assign('count', $count);//总字段数
        //设置当前页面的seo模板变量
        $this->view->assign('title','编辑商铺');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','田雨微信管理客户端');
        //测试数据
//        var_dump($shopList);
//        var_dump($count);
//        dump($result);
        return $this -> view -> fetch('shop_list');
    }

    //渲染商铺编辑界面
    public function shopEdit(Request $request)
    {
//去处要编辑的数据的id
        $Shop_id = $request -> param('');

//        var_dump($Shop_id);
        //根据ID进行查询(通过模型方法)
        //**模型方法取不到 一直是空值 所以直接调用数据库Db的方法,注：取不到值的原因是数据表主键属性没有自增，无法依靠id获取数据 Db调用指定的字段可以使用功能正常*
        $result = ShopModel::get($Shop_id);
//        $result =  Db::table('shop')->where('id',2)->find();
//      var_dump($result);
       //tp5中通过模型select返回的是对象 通过db select返回的是数组


        //设置当前页面的seo模板变量
        $this->view->assign('title','编辑商铺信息');
        $this->view->assign('shop_info',$result);//获取对象原始数据

        //渲染当前的编辑模板
        return $this->view->fetch('shop/shop_edit');
    }
    //商铺状态变更
    public function setStatus(Request $request)
    {
        $shop_id = $request -> param('ID');
//        $result = ShopModel::get($shop_id);
        $result = Db::table('shop')->where('ID',1)->find();
        if($result->getData('STATUS') == 1) {
            ShopModel::update(['STATUS'=>0],['ID'=>$Shop_id]);
        } else {
            ShopModel::update(['STATUS'=>1],['ID'=>$Shop_id]);
        }
    }
    //商铺更新
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
        $result = ShopModel::update($data,$condition);
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

    //渲染 商铺添加界面
    public function ShopAdd()
    {
        $this->view->assign('title','添加商铺');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','田雨后台');
        return $this->view->fetch('Shop_add');
    }
//    添加商铺
    public function   addShop(Request $request)
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

        if ($result === true) {
            $user= ShopModel::create($request->param());
            if ($user === null) {
                $status = 0;
                $message = '添加失败了呢···';
            }
        }
        return ['status'=>$status, 'message'=>$message,];
    }
    //检测用户名是否可用
    public function checkShopName(Request $request)
    {
        $userName = trim($request -> param('name'));
        $status = 1;
        $message = '用户名可用';
        if (ShopModel::get(['name'=> $userName])) {
            //如果在表中查询到该用户名
            $status = 0;
            $message = '用户名重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }
    //添加商铺
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
    //渲染已删除商铺界面
    public  function doDel(){
        $this->view->assign('title','添加商铺');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','田雨后台');
//$result = ShopModel::get('is_detele'==1);//取到的是空值 null
//        $result =  Db::table('shop')->where('is_Delete','=','1');
        $result = ShopModel::all(
            function ($query){
            $query->where('ID')->find();
        }
        );

        foreach ($result as $value){
            $data = [
                'ID' => $value->ID,  //主键
                'storeID' => $value->storeID,  //商铺id
                'CODE' => $value->CODE  ,  //系统编号
                'NAME' => $value->NAME,  //店仓名
                'TYPE' => $value->TYPE,  //店仓类型
                'ISRETAIL' => $value->ISRETAIL,  //是否允许零售
                'CITY' => $value->CITY,  //城市
                'STATUS' => $value->STATUS,      //店铺状态
                'CUSTOMER' => $value->CUSTOMER,  //所属经销商
                'QRCODE' => $value->QRCODE,      //二维码
                'SUPPLEMENT'=>$value->SUPPLEMENT,//细节补充说明
                'create_time'=> $value->create_time,//创建时间
                'update_time'=> $value->update_time,//更新时间
                'delete_time'=> $value->delete_time,//删除时间
                'is_delete' => $value->is_delete,   //判断删除状态
            ];
            //,保存到数组   $shopList中
            $shopList[] = $data;
        }

        //将所有数据赋值给当前模板
        $this->view->assign('shop', $shopList);
        return $this->view->fetch('Shop_del');
    }
    //软删除操作
    public function deleteShop(Request $request)
    {
        $Shop_id = $request -> param('id');
        ShopModel::update(['is_delete'=>1],['id'=> $Shop_id]);
        ShopModel::destroy($Shop_id);

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
//                            _ooOoo_
//                           o8888888o
//                           88" . "88
//                           (| -_- |)
//                           O\  =  /O
//                        ____/`---'\____
//                      .'  \\|     |//  `.
//                     /  \\|||  :  |||//  \
//                    /  _||||| -:- |||||-  \
//                    |   | \\\  -  /// |   |
//                    | \_|  ''\---/''  |   |
//                    \  .-\__  `-`  ___/-. /
//                  ___`. .'  /--.--\  `. . __
//               ."" '<  `.___\_<|>_/___.'  >'"".
//              | | :  `- \`.;`\ _ /`;.`/ - ` : | |
//              \  \ `-.   \_ __\ /__ _/   .-` /  /
//         ======`-.____`-.___\_____/___.-`____.-'======
//                            `=---='
//        ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
//                      Buddha Bless, No Bug !
}