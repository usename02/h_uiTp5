<?php
/**管理员
 */
namespace app\index\controller;
use think\paginator\driver\Bootstrap3;
use think\Request;
use app\index\model\User as UserModel;
use traits\model\SoftDelete;
use think\session;
use think\Db;
class User extends  Base
{
    //登录渲染
    public function login()
    {
        $this -> alreadyLogin();//判断是否已登录
        $this -> view ->assign('title','用户登录');
        return $this -> view -> fetch('index/login');
    }
    //登录判断
    public  function  checkLogin(Request $request)
    {   //设置初始值
        $status = 0;//检验登录状态 判断标志
        $result = '验证失败'; //失败提示信息
        $data = $request->param();
        //验证规则
        $rule = [
            'name|姓名' => 'require',
            'password|密码' => 'require',
        ];

        $result = $this->validate($data, $rule);
        if ($result===true) {
            //查询条件
            $map = [
                'name' => $data['name'],
                'password' =>md5($data['password'])
            ];
            //数据表查询,返回模型对象
            $user = UserModel::get($map);

            if ( $user===null ) {
                $result = '没有该用户，请检查';
                $status = 1;
            } else {
                $status = 1;
                $result = '验证通过,点击[确定]后进入后台';
                //创建2个session,用来检测用户登陆状态和防止重复登陆
                Session::set('user_id', $user->id);
                Session::set('user_info', $user->getData());
;
                //更新用户登录次数:自增1
                $user->setInc('login_count');
            }


        }
        return ['status' => $status, 'message' => $result, 'data' => $data];
    }

    //退出登录
    public function logout()
    {
        //退出前先更新登录时间字段,下次登录时就知道上次登录时间了
        UserModel::update(['login_time'=>time()],['id'=> Session::get('user_id')]);
        Session::delete('user_id');
        Session::delete('user_info');

        $this -> success('注销登陆,正在返回',url('user/login'));

    }
    //管理员列表
    public function  adminList()
    {
        $this -> view -> assign('title', '管理员列表');
        $this -> view -> assign('keywords','田雨微信会员管理系统' );
        $this -> view -> assign('desc','田雨后台');
//
        //获取列表数量
        $count = UserModel::count();
        $this -> view -> assign('count',$count);

        //判断当前是不是admin用户
        //先通过session获取到用户登陆名
        $userRole = Session::get('user_info.name');
//var_dump($userRole);
        if ($userRole =='admin') {
            $listRows = 3;//每页显示$page条数据
            $list = UserModel::paginate($listRows, false,[ 'type'=>'Bootstrap6' ]);
 //admin用户可以查看所有记录,数据要经过模型获取器处理
            //分页显示输出
            $page=$list->render();
            var_dump($page);
//           总页数
            $total = $list->total();

        } else {
            //为了共用列表模板,使用了all(),其实这里用get()符合逻辑,但有时也要变通
            //非admin只能看自己信息,数据要经过模型获取器处理
            $list = UserModel::all(['name'=>$userRole]);
        }
        $this -> view -> assign('list', $list);
//var_dump($page);
        $this-> view-> assign('page', $page);
        //渲染管理员列表模板
        return $this -> view -> fetch('admin/admin-list');
    }

    //管理员状态变更
    public function setStatus(Request $request)
    {
        $user_id = $request -> param('id');
        $result = UserModel::get($user_id);
        if($result->getData('status') == 1) {
            UserModel::update(['status'=>0],['id'=>$user_id]);
        } else {
            UserModel::update(['status'=>1],['id'=>$user_id]);
        }
    }

    //渲染编辑管理员界面
    public function adminEdit(Request $request)
    {

        $user_id = $request -> param('');
//        var_dump($user_id);
//        var_dump($request);

        $result = UserModel::get($user_id);
//        var_dump($result);
//        var_dump($result->getData());
        $this->view->assign('title','编辑管理员信息');
        $this->view->assign('user_info',$result);//获取对象原始数据
//        var_dump($request);//测试$request数据内容
//       var_dump($result);//测试数组内容
        return $this->fetch('admin/admin_edit');


    }

    //更新数据操作
    public function editUser(Request $request)
    {
        //获取表单返回的数据
        $data = $request -> param();
//        $param = $request -> param()
        //去掉表单中为空的数据,即没有修改的内容
        foreach ($data as $key => $value ){
            if (!empty($value)){
                $data[$key] = $value;
            }
        }
        $condition = ['name'=>$data['name']] ;
        $result = UserModel::update($data, $condition);
        //如果是admin用户,更新当前session中用户信息user_info中的角色role,供页面调用
        if (Session::get('user_info.name') == 'admin') {
            Session::set('user_info.role', $data['role']);
        }
        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败,请检查'];
        }
    }

    //删除操作
    public function deleteUser(Request $request)
    {
        $user_id = $request -> param('id');
        UserModel::update(['is_delete'=>1],['id'=> $user_id]);
        UserModel::destroy($user_id);

    }

    //恢复删除操作
    public function unDelete()
    {
        UserModel::update(['delete_time'=>NULL],['is_delete'=>1]);
    }

    //添加操作的界面
    public function  adminAdd()
    {
        $this->view->assign('title','添加管理员');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','田雨后台系统');
        return $this->view->fetch('admin/admin_add');
    }

    //检测用户名是否可用
    public function checkUserName(Request $request)
    {
        $userName = trim($request -> param('name'));
        $status = 1;
        $message = '用户名可用';
        if (UserModel::get(['name'=> $userName])) {
            //如果在表中查询到该用户名
            $status = 0;
            $message = '用户名重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

    //检测用户邮箱是否可用
    public function checkUserEmail(Request $request)
    {
        $userEmail = trim($request -> param('email'));
        $status = 1;
        $message = '邮箱可用';
        if (UserModel::get(['email'=> $userEmail])) {
            //查询表中找到了该邮箱,修改返回值
            $status = 0;
            $message = '邮箱重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

    //添加操作
    public function addUser(Request $request)
    {
        $data = $request -> param();
        $status = 1;
        $message = '添加成功';
        $rule = [
            'name|用户名' => "require|min:3|max:10",
            'password|密码' => "require|min:3|max:10",
            'email|邮箱' => 'require|email'
        ];
        //验证数据
        $result = $this -> validate($data, $rule);
        if ($result === true) {
            $user= UserModel::create($request->param());
            if ($user === null) {
                $status = 0;
                $message = '添加失败了呢···';
            }
        }
        return ['status'=>$status, 'message'=>$message];
    }
//    查询操作
    public  function  inquireUser(Request $request)
    {
        $this -> view -> assign('title', '管理员列表');
        $this -> view -> assign('keywords','田雨微信会员管理系统' );
        $this -> view -> assign('desc','田雨后台');
        //从提交表单中获取数据
        $data = $request -> param();
        var_dump($data);
        $list = Db::name('user')->where($data)->select();

        $this -> view -> assign('list', $list);

        //获取列表数量
        $count = UserModel::count();
        $this -> view -> assign('count',$count);
        return $this->view->fetch('admin/admin-list');
    }
//    模糊查询工作人员
    public  function  aaa($searchKeyword)
    {
        $db=connectDb();
        $result=$db->prepare("select 字段名 from 数据库名字 where 字段名 like ?");
        $result->bindParam(1,$keyword);//第一个问号的值
//        $result=>execute;

return $result->fetchAll(PDO::FETCH_ASSOC);
$keyword=$_GET['keyword'];//获取输入框的内容

$suggestion=test($keyword);

echo json_encode($suggestion);//输出查询的结果（json格式输出）
    }
//
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