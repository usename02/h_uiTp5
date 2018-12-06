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
//关键字回复

        /**
         * 关键字处理
         * @param string $rule 关键字规则
         * @param bool $isLastReply 强制结束
         * @return bool|string
         * @throws \WeChat\Exceptions\InvalidDecryptException
         * @throws \WeChat\Exceptions\InvalidResponseException
         * @throws \WeChat\Exceptions\LocalCacheException
         * @throws \think\Exception
         * @throws \think\db\exception\DataNotFoundException
         * @throws \think\db\exception\ModelNotFoundException
         * @throws \think\exception\DbException
         * @throws \think\exception\PDOException
         */
        protected function keys($rule, $isLastReply = false)
    {
        list($table, $field, $value) = explode('#', $rule . '##');
        $info = Db::name($table)->where($field, $value)->find();
        if (empty($info['type']) || (array_key_exists('status', $info) && empty($info['status']))) {
            // 切换默认回复
            return $isLastReply ? false : $this->keys('wechat_keys#keys#default', true);
        }

        switch ($info['type']) {
            case 'customservice':
                return $this->sendMessage('customservice', ['content' => $info['content']]);
            case 'keys':
                $content = empty($info['content']) ? $info['name'] : $info['content'];
                return $this->keys("wechat_keys#keys#{$content}");
            case 'text':
                return $this->sendMessage('text', ['content' => $info['content']]);
            case 'news':
                list($news, $data) = [MediaService::getNewsById($info['news_id']), []];
                if (empty($news['articles'])) {
                    return false;
                }
                foreach ($news['articles'] as $vo) {
                    $url = url("@wechat/review", '', true, true) . "?content={$vo['id']}&type=article";
                    $data[] = ['url' => $url, 'title' => $vo['title'], 'picurl' => $vo['local_url'], 'description' => $vo['digest']];
                }
                return $this->sendMessage('news', ['articles' => $data]);
            case 'music':
                if (empty($info['music_url']) || empty($info['music_title']) || empty($info['music_desc'])) {
                    return false;
                }
                $media_id = empty($info['music_image']) ? '' : MediaService::uploadForeverMedia($info['music_image'], 'image');
                $data = ['title' => $info['music_title'], 'description' => $info['music_desc'], 'musicurl' => $info['music_url'], 'hqmusicurl' => $info['music_url'], 'thumb_media_id' => $media_id];
                return $this->sendMessage('music', $data);
            case 'voice':
                if (empty($info['voice_url']) || !($media_id = MediaService::uploadForeverMedia($info['voice_url'], 'voice'))) {
                    return false;
                }
                return $this->sendMessage('voice', ['media_id' => $media_id]);
            case 'image':
                if (empty($info['image_url']) || !($media_id = MediaService::uploadForeverMedia($info['image_url'], 'image'))) {
                    return false;
                }
                return $this->sendMessage('image', ['media_id' => $media_id]);
            case 'video':
                if (empty($info['video_url']) || empty($info['video_desc']) || empty($info['video_title'])) {
                    return false;
                }
                $videoData = ['title' => $info['video_title'], 'introduction' => $info['video_desc']];
                if (!($media_id = MediaService::uploadForeverMedia($info['video_url'], 'video', $videoData))) {
                    return false;
                }
                $data = ['media_id' => $media_id, 'title' => $info['video_title'], 'description' => $info['video_desc']];
                return $this->sendMessage('video', $data);
            default:
                return false;
        }
    }


//聊天记录
    public function chatHistory()
    {

    }
//自定义菜单
    public  function customMenu()
    {


    }
//
}