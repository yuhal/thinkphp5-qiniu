<?php
namespace qiniu;

require 'php-sdk/autoload.php';
use Qiniu\Auth;
use Qiniu\Http\Client;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;

class QiniuSdk{

	public $sdk_info;

	function __construct($info){
		$this->sdk_info = $info;
		$this->Auth = new Auth($this->sdk_info['accessKey'],$this->sdk_info['secretKey']);
		$this->Client = new Client();
		$this->uploadMgr = new UploadManager();
		$this->bucketMgr = new BucketManager($this->Auth);
	}
	
	public function __call($name, $arguments=null){
		$arguments = current($arguments);
		if(method_exists($this->bucketMgr, $name)){
			switch ($name) {
				case 'buckets':
	    			return $this->bucketMgr->buckets($arguments['shared']);
	    		break;
	    		case 'domains':
	    			return $this->bucketMgr->domains($this->sdk_info['bucket']);
	    		break;
				case 'listFiles':
		            return $this->bucketMgr->listFiles($this->sdk_info['bucket'],$arguments['prefix'],$arguments['marker'],$arguments['limit'],$arguments['delimiter']);
	    		break;
	    		case 'rename':
	    			return $this->bucketMgr->rename($this->sdk_info['bucket'],$arguments['oldname'],$arguments['newname']);
	    		break;
	    		case 'delete':
	    			return $this->bucketMgr->delete($this->sdk_info['bucket'],$arguments['oldname']);
	    		break;
	    		case 'move':
	    			return $this->bucketMgr->move($arguments['from_bucket'], $arguments['from_key'], $arguments['to_bucket'], $arguments['to_key']);
	    		break;
	    		case 'copy':
	    			return $this->bucketMgr->copy($arguments['from_bucket'], $arguments['from_key'], $arguments['to_bucket'], $arguments['to_key']);
	    		break;
	    		case 'fetch':
	    			return $this->bucketMgr->fetch($arguments['url'],$this->sdk_info['bucket']);
	    		break;
	    		case 'stat':
	    			return $this->bucketMgr->stat($this->sdk_info['bucket'],$arguments['oldname']);
	    		break;
	    		case 'buildBatchMove':
	    			$opts = $this->bucketMgr->buildBatchMove($arguments['bucket'], $arguments['keyPairs'], $arguments['destBucket'], true);
	    			return $this->bucketMgr->batch($opts);
	    		break;
	    	}	
		}elseif(method_exists($this->uploadMgr, $name)){
			switch ($name) {
				case 'putFile':
					$arguments['token'] = $this->Auth->uploadToken($this->sdk_info['bucket']);
					$re = $this->uploadMgr->putFile($arguments['token'],$arguments['file'],$arguments['filepath']);
					if(isset($re[0]['key'])){
						return $re[0]['key'];
					}else{
						return false;
					}
	    		break;
	    	}
		}elseif(method_exists($this->Auth, $name)){
			switch ($name) {
				case 'uploadToken':
					$token = $this->Auth->uploadToken($this->sdk_info['bucket']);
					if($token){
						return $token;
					}else{
						return false;
					}
	    		break;
	    		case 'privateDownloadUrl':
					$token = $this->Auth->privateDownloadUrl($arguments['baseUrl']);
					if($token){
						return $token;
					}else{
						return false;
					}
	    		break;
	    	}
		}else{
			switch ($name) {
				case 'faceGroupInfo':
	    			$url = "http://ai.qiniuapi.com/v1/face/group/{$arguments['group_id']}/info";
	    			return $this->_get($url);
	    		break;
	    		case 'listFaceGroup':
	    			$url = "http://ai.qiniuapi.com/v1/face/group";
	    			return $this->_get($url);
	    		break;
	    		case 'newFaceGroup':
	    			$url = "http://ai.qiniuapi.com/v1/face/group/{$arguments['group_id']}/new";
	    			$arr['data'][0]['uri'] = $arguments['uri'];
	    			return $this->_post($url,$arr);
	    		break;
	    		case 'updateFaceGroup':
	    			$url = "http://ai.qiniuapi.com/v1/face/group/{$arguments['group_id']}/add";
	    			$arr['data'][0]['uri'] = $arguments['uri'];
	    			return $this->_post($url,$arr);
	    		break;
	    		case 'faceGroupSearch':
	    			$url = "http://ai.qiniuapi.com/v1/face/groups/search";
	    			$arr['data']['uri'] = $arguments['uri'];
	    			$arr['params']['groups'] = $arguments['groups'];
	    			return $this->_post($url,$arr);
	    		break;
	    	}
	    	return false;
		}
    }

    function _get($url){
    	$method = "GET";
		$host = "ai.qiniuapi.com";
		$headers = $this->Auth->authorizationV2($url, $method);
		$headers['Host'] = $host;
		$response = $this->Client::get($url, $headers);
		return json_decode($response->body,true);
    }

    function _post($url,$arr){
		$method = "POST";
		$host = "ai.qiniuapi.com";
		$body = json_encode($arr);
		$contentType = "application/json";
		$headers = $this->Auth->authorizationV2($url, $method, $body, $contentType);
		$headers['Content-Type'] = $contentType;
		$headers['Host'] = $host;
		$response = $this->Client::post($url, $body, $headers);
		return json_decode($response->body,true);
    }
}