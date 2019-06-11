<?php

namespace app\api\controller\qiniu;

use think\Controller;
use think\Db;
use think\Request;
use think\Cache;
use app\api\controller\Send;
use app\api\controller\Base;
use app\api\validate\Bucket as validate;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use qiniu\QiniuSdk;

class Bucket extends Base
{
    /**
     * 构造方法
     * @param Request $request Request对象
     */
    public function __construct(Request $request){
        parent::__construct($request);
        $this->validate = new validate();
        $this->PHPMailer = new PHPMailer(true);
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        echo 'index';
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        echo "create";
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        dump($this->uid);
        echo "save";
    }

    /**
     * 显示指定的资源
     *
     * @param  string  $id
     * @return \think\Response
     */
    public function read($id)
    {
        var_dump('<pre>',$this->PHPMailer);exit;
        //参数验证
        if(!$this->validate->check(input(''))){
            return self::returnMsg(401,$this->validate->getError());
        }
        $config = array_merge(config('qiniu.'),['bucket'=>$id]);
        $qiniuSdk = new QiniuSdk($config);
        // 列出该用户下所有的仓库
        $arguments['shared'] = $config['shared'];
        $buckets = $qiniuSdk->buckets();
        if(!in_array($id, $buckets[0])){
            self::returnMsg(401, '该仓库不存在！');
        }
        // 要列取文件的公共前缀
        $arguments['prefix'] = '';
        if(input('prefix')) $arguments['prefix'] = input('prefix');
        // 上次列举返回的位置标记，作为本次列举的起点信息。
        $arguments['marker'] = '';
        if(input('marker')) $arguments['marker'] = input('marker');
        // 本次列举的条目数
        $arguments['limit'] = '';
        if(input('limit')) $arguments['limit'] = input('limit');
        // 分隔符
        $arguments['delimiter'] = '';
        if(input('delimiter')) $arguments['delimiter'] = input('delimiter');

        $chcheKey = md5(json_encode($arguments));
        $listFiles = cache('BucketReadListFiles_'.$chcheKey);
        if(!$listFiles){
            $listFiles = $qiniuSdk->listFiles($arguments);
            cache('BucketReadListFiles_'.$chcheKey, $listFiles, 3600*24);
        }
        if($listFiles){
            return self::returnMsg(200,'success',$listFiles);
        }else{
            return self::returnMsg(500,'fail',$listFiles);
        }
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        echo "edit";
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        echo "update";
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        echo "delete";
    }

}