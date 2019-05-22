<?php

namespace app\api\controller\qiniu;

use think\Controller;
use think\Db;
use think\Request;
use app\api\controller\Send;
use app\api\controller\Base;
use qiniu\QiniuSdk;

class File extends Base
{

	public function __construct(){
		$this->QiniuSdk = new QiniuSdk(config('qiniu.'));
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
     * @param  string  $bucket
     * @return \think\Response
     */
    public function read($id)
    {
        echo "read";
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