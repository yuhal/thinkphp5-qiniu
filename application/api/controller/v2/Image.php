<?php

namespace app\api\controller\v2;

use think\Controller;
use think\Db;
use think\Request;
use think\Cache;
use app\api\controller\Send;
use app\api\controller\Base;
use Qiniu\Auth;
use Qiniu\Http\Client;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use app\api\validate\v2\Image as Validate;

class Image extends Base
{
    protected $auth = null;

    protected $bucketMgr = null;

    protected $client = null;

    protected $uploadMgr = null;

    /**
     * 构造方法
     * @param Request $request Request对象
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        
        $this->auth = new Auth(config('qiniu.accessKey'), config('qiniu.secretKey'));
        $this->bucketMgr = new BucketManager($this->auth);
        $this->client = new Client();
        $this->request = $request;
        $this->uploadMgr = new UploadManager();
        $this->validate = new Validate();
    }

    /**
     * 删除图片
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        // 参数验证
        if (!$this->validate->scene(request()->action())->check(input())) {
            return self::returnMsg(401, $this->validate->getError());
        }

        $url = "http://ai.qiniuapi.com/v1/image/group/".$id."/delete";
        $delete = qiniuPost($url, input());
        return self::returnMsg(200, 'success', $delete);
    }

    /**
     * 显示所有图片     
     *
     * @return \think\Response
     */
    public function index()
    {
        // 参数验证
        if (!$this->validate->scene(request()->action())->check(input())) {
            return self::returnMsg(401, $this->validate->getError());
        }

        $url = "http://ai.qiniuapi.com/v1/image/group/".input('group_id');
        $group = qiniuGet($url,$this->request->only('marker,limit'));
        return self::returnMsg(200, 'success', $group);
    }

    /**
     * 显示指定图片信息 
     *
     * @param  string  $id 图像库的唯一标识
     * @return \think\Response
     */
    public function read($id)
    {   
        // 参数验证
        if (!$this->validate->scene(request()->action())->check(input())) {
            return self::returnMsg(401, $this->validate->getError());
        }

        $url = "http://ai.qiniuapi.com/v1/image/group/".$id."/image";
        $image = qiniuGet($url,$this->request->only('image_id'));
        return self::returnMsg(200, 'success', $image);
    }

    /**
     * 析构方法
     * @param Request $request Request对象
     */
    public function __destruct()
    {
        $this->auth = null;
        $this->bucketMgr = null;
        $this->client = null;
        $this->request = null;
        $this->uploadMgr = null;
        $this->validate = null;
    }

}
