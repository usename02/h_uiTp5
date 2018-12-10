<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/20 0020
 * Time: 11:08
 */

namespace app\index\controller;


class Wechat
{
    /**
     * 微信配置服务
     * Class ConfigHandler
     * @package app\wechat\handler
     * @author Anyon <zoujingli@qq.com>
     */

    /**
     * 获取当前公众号配置
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getConfig()
    {
        $this->checkInit();
        $info = Db::name('WechatConfig')->where(['authorizer_appid' => $this->appid])->find();
        if (empty($info)) {
            return false;
        }
        unset($info['id']);
        return $info;
    }

    /**
     * 设置微信接口通知URL地址
     * @param string $notifyUri 接口通知URL地址
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function setApiNotifyUri($notifyUri)
    {
        $this->checkInit();
        if (empty($notifyUri)) {
            throw new Exception('请传入微信通知URL', '401');
        }
        list($where, $data) = [['authorizer_appid' => $this->appid], ['appuri' => $notifyUri]];
        return Db::name('WechatConfig')->where($where)->update($data) !== false;
    }

    /**
     * 更新接口Appkey(成功返回新的Appkey)
     * @return bool|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateApiAppkey()
    {
        $this->checkInit();
        list($where, $data) = [['authorizer_appid' => $this->appid], ['appkey' => md5(uniqid())]];
        Db::name('WechatConfig')->where($where)->update($data);
        return $data['appkey'];
    }

    /**
     * 客户端SOAP基础接口
     * Class BasicHandler
     * @package app\wechat\handler
     * @author Anyon <zoujingli@qq.com>
     */

    /**
     * 当前微信配置
     * @var array
     */
    protected $config;

    /**
     * 当前微信APPID
     * @var string
     */
    protected $appid;

    /**
     * 错误消息
     * @var string
     */
    protected $message;

    /**
     * ConfigService constructor.
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->appid = empty($config['authorizer_appid']) ? '' : $config['authorizer_appid'];
    }

    /**
     * 检查微信配置服务初始化状态
     * @return bool
     * @throws \think\Exception
     */
    public function checkInit()
    {
        if (empty($this->config)) {
            throw new Exception('Wechat Please bind Wechat first', '304');
        }
        return true;
    }

//关键字
    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'WechatKeys';

    /**
     * 显示关键字列表
     * @return array|string
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        // 关键字二维码显示
        if ($this->request->get('action') === 'qrc') {
            $wechat = WechatService::WeChatQrcode();
            $result = $wechat->create($this->request->get('keys', ''));
            $this->redirect($wechat->url($result['ticket']));
        }
        // 显示关键字列表
        $this->title = '微信关键字管理';
        $db = Db::name($this->table)->whereNotIn('keys', ['subscribe', 'default']);
        return $this->_list($db->order('sort asc,id desc'));
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_data_filter(&$data)
    {
        try {
            $types = [
                'keys'  => '关键字', 'image' => '图片', 'news' => '图文',
                'music' => '音乐', 'text' => '文字', 'video' => '视频', 'voice' => '语音',
            ];
            foreach ($data as &$vo) {
                $vo['qrc'] = url('@wechat/keys/index') . "?action=qrc&keys={$vo['keys']}";
                $vo['type'] = isset($types[$vo['type']]) ? $types[$vo['type']] : $vo['type'];
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
    /**
     * 添加关键字
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add()
    {
        $this->title = '添加关键字规则';
        return $this->_form($this->table, 'form');
    }
    /**
     * 编辑关键字
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        $this->title = '编辑关键字规则';
        return $this->_form($this->table, 'form');
    }
    /**
     * 删除关键字
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("关键字删除成功！", '');
        }
        $this->error("关键字删除失败，请稍候再试！");
    }
    /**
     * 关键字禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("关键字禁用成功！", '');
        }
        $this->error("关键字禁用失败，请稍候再试！");
    }
    /**
     * 关键字禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("关键字启用成功！", '');
        }
        $this->error("关键字启用失败，请稍候再试！");
    }

    /**
     * 关注默认回复
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function subscribe()
    {
        $this->assign('title', '编辑默认回复');
        return $this->_form($this->table, 'form', 'keys', [], ['keys' => 'subscribe']);

    }
    
    /**
     * 无配置默认回复
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function defaults()
    {
        $this->assign('title', '编辑无配置默认回复');
        return $this->_form($this->table, 'form', 'keys', [], ['keys' => 'default']);
    }
//
    /**
     * 添加数据处理
     * @param array $data
     */
    protected function _form_filter(array &$data)
    {
        if ($this->request->isPost() && isset($data['keys'])) {
            $db = Db::name($this->table)->where('keys', $data['keys']);
            !empty($data['id']) && $db->where('id', 'neq', $data['id']);
            $db->count() > 0 && $this->error('关键字已经存在，请使用其它关键字！');
        }
    }

    /**
     * 编辑结果处理
     * @param $result
     */
    protected function _form_result($result)
    {
        if ($result !== false) {
            list($url, $keys) = ['', $this->request->post('keys')];
            if (!in_array($keys, ['subscribe', 'default'])) {
                $url = url('@admin') . '#' . url('wechat/keys/index') . '?spm=' . $this->request->get('spm');
            }
            $this->success('恭喜, 关键字保存成功!', $url);
        }
        $this->error('关键字保存失败, 请稍候再试!');
    }

    /**
     * 微信媒体文件管理
     * Class MediaService
     * @package app\wechat\service
     */

    /**
     * 通过图文ID读取图文信息
     * @param int $id 本地图文ID
     * @param array $where 额外的查询条件
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getNewsById($id, $where = [])
    {
        $data = Db::name('WechatNews')->where(['id' => $id])->where($where)->find();
        $article_ids = explode(',', $data['article_id']);
        $articles = Db::name('WechatNewsArticle')->whereIn('id', $article_ids)->select();
        $data['articles'] = [];
        foreach ($article_ids as $article_id) {
            foreach ($articles as $article) {
                if (intval($article['id']) === intval($article_id)) {
                    unset($article['create_by'], $article['create_at']);
                    $data['articles'][] = $article;
                }
            }
        }
        return $data;
    }

    /**
     * 上传图片到微信服务器
     * @param string $local_url 图文地址
     * @return string
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function uploadImage($local_url)
    {
        $map = ['md5' => md5($local_url)];
        if (($media_url = Db::name('WechatNewsImage')->where($map)->value('media_url'))) {
            return $media_url;
        }
        $info = WechatService::WeChatMedia()->uploadImg(self::getServerPath($local_url));
        if (strtolower(sysconf('wechat_type')) === 'thr') {
            WechatService::wechat()->rmFile($local_url);
        }
        $data = ['local_url' => $local_url, 'media_url' => $info['url'], 'md5' => $map['md5']];
        DataService::save('WechatNewsImage', $data, 'md5');
        return $info['url'];
    }

    /**
     * 上传图片永久素材，返回素材media_id
     * @param string $local_url 文件URL地址
     * @param string $type 文件类型
     * @param array $video_info 视频信息
     * @return string|null
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function uploadForeverMedia($local_url, $type = 'image', $video_info = [])
    {
        $map = ['md5' => md5($local_url), 'appid' => WechatService::getAppid()];
        if (($media_id = Db::name('WechatNewsMedia')->where($map)->value('media_id'))) {
            return $media_id;
        }
        $result = WechatService::WeChatMedia()->addMaterial(self::getServerPath($local_url), $type, $video_info);
        if (strtolower(sysconf('wechat_type')) === 'thr') {
            WechatService::wechat()->rmFile($local_url);
        }
        $data = ['md5' => $map['md5'], 'type' => $type, 'appid' => $map['appid'], 'media_id' => $result['media_id'], 'local_url' => $local_url];
        isset($result['url']) && $data['media_url'] = $result['url'];
        DataService::save('WechatNewsMedia', $data, 'md5', ['appid' => $map['appid'], 'type' => $type]);
        return $data['media_id'];
    }

    /**
     * 文件位置处理
     * @param string $local
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    protected static function getServerPath($local)
    {
        switch (strtolower(sysconf('wechat_type'))) {
            case 'api':
                if (file_exists($local)) {
                    return $local;
                }
                return FileService::download($local)['file'];
            case 'thr':
                return WechatService::wechat()->upFile(base64_encode(file_get_contents($local)), $local)['file'];
            default:
                return $local;
        }
    }
    /**
     * 微信推送消息处理
     *
     * @author Anyon <zoujingli@qq.com>
     * @date 2016/10/27 14:14
     */

    /**
     * 事件初始化
     * @param string $appid
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public static function handler($appid)
    {
        $service = WechatService::WeChatReceive($appid);
        // 验证微信配置信息
        $config = Db::name('WechatConfig')->where(['authorizer_appid' => $appid])->find();
        if (empty($config) || empty($config['appuri'])) {
            Log::error(($message = "微信{$appid}授权配置验证无效"));
            return $message;
        }
        try {
            list($data, $openid) = [$service->getReceive(), $service->getOpenid()];
            (isset($data['EventKey']) && is_object($data['EventKey'])) && $data['EventKey'] = (array)$data['EventKey'];
            HttpService::post($config['appuri'], ['appid' => $appid, 'receive' => serialize($data), 'openid' => $openid], ['timeout' => 1]);
        } catch (\Exception $e) {
            Log::error("微信{$appid}接口调用异常，" . $e->getMessage());
        }
        return 'success';
    }


    /**
     * 授权数据过滤转换处理
     * @param array $info
     * @return mixed
     */
    public static function filter(array $info)
    {
        if (isset($info['func_info'])) {
            $info['func_info'] = join(',', array_map(function ($tmp) {
                return $tmp['funcscope_category']['id'];
            }, $info['func_info']));
        }
        $info['verify_type_info'] = join(',', $info['verify_type_info']);
        $info['service_type_info'] = join(',', $info['service_type_info']);
        $info['business_info'] = json_encode($info['business_info'], JSON_UNESCAPED_UNICODE);
        // 微信类型:  0 代表订阅号, 2 代表服务号, 3 代表小程序
        $info['service_type'] = intval($info['service_type_info']) === 2 ? 2 : 0;
        if (!empty($info['MiniProgramInfo'])) {
            // 微信类型:  0 代表订阅号, 2 代表服务号, 3 代表小程序
            $info['service_type'] = 3;
            // 小程序信息
            $info['miniprograminfo'] = json_encode($info['MiniProgramInfo'], JSON_UNESCAPED_UNICODE);
        }
        unset($info['MiniProgramInfo']);
        // 微信认证: -1 代表未认证, 0 代表微信认证
        $info['verify_type'] = intval($info['verify_type_info']) !== 0 ? -1 : 0;
        return $info;
    }


}