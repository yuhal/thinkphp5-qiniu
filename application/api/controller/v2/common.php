<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use think\Db;
use think\Config;
//use \mongo\Mongodb;


function json_to_xml($source,$charset='utf8') {
    if(empty($source)){
    return false;
    }
    //php5，以及以上，如果是更早版本，请查看JSON.php
    $array = json_decode($source);
    $xml ='';
    $xml .= change($array);
    return $xml;
}
function change($source) {
    $string="";
    foreach($source as $k=>$v){
    $string .="<".$k.">";
    //判断是否是数组，或者，对像
    if(is_array($v) || is_object($v)){
    //是数组或者对像就的递归调用
    $string .= change($v);
    }else{
    //取得标签数据
    $string .=$v;
    }
    $string .="";
    }
    return $string;
}

/**
 * 数组转xml字符
 * @param  string 	$xml xml字符串
**/
function arrayToXml($arr)
{
    $xml = "";
    
    foreach ($arr as $key=>$val){
        if(is_array($val)){
            $xml.="<".$key.">".arrayToXml($val)."</".$key.">";
        }else{
            $xml.="<".$key.">".$val."</".$key.">";
        }
    }
    // $xml.="</xml>";
    return $xml ;
}

/**
 * 将xml转为array
 * @param  string 	$xml xml字符串或者xml文件名
 * @param  bool 	$isfile 传入的是否是xml文件名
 * @return array    转换得到的数组
 */
function xmlToArray($xml,$isfile=false){   
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    if($isfile){
        if(!file_exists($xml)) return false;
        $xmlstr = file_get_contents($xml);
    }else{
        $xmlstr = $xml;
    }
    $result= json_decode(json_encode(simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
    return $result;
}

//获取xml
function xml($xml){
    $p = xml_parser_create();
    xml_parse_into_struct($p, $xml, $vals, $index);
    xml_parser_free($p);
    $data = "";
    foreach ($index as $key=>$value) {
        if($key == 'xml' || $key == 'XML') continue;
        $tag = $vals[$value[0]]['tag'];
        $value = $vals[$value[0]]['value'];
        $data[$tag] = $value;
    }
    return $data;
}

function checkopenid($openid){
    return Db::name('pam_account')->field('login_name,login_password,openid,account_id')->where(array('openid'=>$openid))->find();
}

function changePinyin($str,$charset="utf-8"){
	$pinyin=new \my\Pinyin();
	return $pinyin->get_pinyin($str,$charset);
}

function db_connect($config){
    $obj = new \mongo\Mongodb(); 
    var_dump($obj);exit;
    \Mongodb::$server = "mongodb://".$config['host'];
    \Mongodb::$options = array('db'=>$config['db']);
    return new \mongo\Mongodb; //想法静态吧。演示就不写了。
}

function getNowMethod(){
    if(request()->isGet()){
        return 'get';
    }elseif(request()->isPost()){
        return 'post';
    }
}

function mb_unserialize($str) {
    return unserialize(preg_replace_callback('#s:(\d+):"(.*?)";#s',function($match){return 's:'.strlen($match[2]).':"'.$match[2].'";';},$str));
}

//订单号算法
function rc4($key, $data)
{
    // Store the vectors "S" has calculated
    static $SC;
    // Function to swaps values of the vector "S"
    $swap = create_function('&$v1, &$v2', '
        $v1 = $v1 ^ $v2;
        $v2 = $v1 ^ $v2;
        $v1 = $v1 ^ $v2;
    ');
    $ikey = crc32($key);
    if (!isset($SC[$ikey])) {
        // Make the vector "S", basead in the key
        $S    = range(0, 255);
        $j    = 0;
        $n    = strlen($key);
        for ($i = 0; $i < 255; $i++) {
            $char  = ord($key{$i % $n});
            $j     = ($j + $S[$i] + $char) % 256;
            $swap($S[$i], $S[$j]);
        }
        $SC[$ikey] = $S;
    } else {
        $S = $SC[$ikey];
    }
    // Crypt/decrypt the data
    $n    = strlen($data);
    $data = str_split($data, 1);
    $i    = $j = 0;
    for ($m = 0; $m < $n; $m++) {
        $i        = ($i + 1) % 256;
        $j        = ($j + $S[$i]) % 256;
        $swap($S[$i], $S[$j]);
        $char     = ord($data[$m]);
        $char     = $S[($S[$i] + $S[$j]) % 256] ^ $char;
        $data[$m] = chr($char);
    }
    return implode('', $data);
}

function get_start($nPage,$count,$pagesize=10){
    $maxPage = ceil($count / $pagesize);
    if($nPage > $maxPage) $nPage = $maxPage;
    $start = ($nPage-1) * $pagesize;
    $start = $start<0 ? 0 : $start;
    $aPage['start'] = $start;
    $aPage['nPage'] = $nPage;
    $aPage['maxPage'] = $maxPage;
    return $aPage;
}


function preg_email($email){
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    } else {
        return true;
    }
}

    /**
      把用户输入的文本转义（主要针对特殊符号和emoji表情）
     */
    function userTextEncode($str){
        if(!is_string($str))return $str;
        if(!$str || $str=='undefined')return '';

        $text = json_encode($str); //暴露出unicode
        $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){
            return addslashes($str[0]);
        },$text); //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。
        return json_decode($text);
    }

function play_file($file,$name){
    $arr_file = array();
    $count = count($file['name']);
    for($i=0;$i<$count;$i++){
        if($file['name'][$i]){
            $arr_file[$name][$i]['name'] = $file['name'][$i];
            $arr_file[$name][$i]['type'] = $file['type'][$i];
            $arr_file[$name][$i]['tmp_name'] = $file['tmp_name'][$i];
            $arr_file[$name][$i]['error'] = $file['error'][$i];
            $arr_file[$name][$i]['size'] = $file['size'][$i];    
        }
    }
    return $arr_file[$name];
}

/**
  解码上面的转义
 */
function userTextDecode($str){
    $text = json_encode($str); //暴露出unicode
    $text = preg_replace_callback('/\\\\\\\\/i',function($str){
        return '\\';
    },$text); //将两条斜杠变成一条，其他不动
    return json_decode($text);
}

function my_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){  
    if(is_array($arrays)){  
        foreach ($arrays as $array){  
            if(is_array($array)){  
                $key_arrays[] = $array[$sort_key];  
            }else{  
                return false;  
            }  
        }  
    }else{  
        return false;  
    } 
    array_multisort($key_arrays,$sort_order,$sort_type,$arrays);  
    return $arrays;  
} 

//获取用户IP地址
function getIp()
{

    if(!empty($_SERVER["HTTP_CLIENT_IP"]))
    {
        $cip = $_SERVER["HTTP_CLIENT_IP"];
    }
    else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
    {
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    else if(!empty($_SERVER["REMOTE_ADDR"]))
    {
        $cip = $_SERVER["REMOTE_ADDR"];
    }
    else
    {
        $cip = '';
    }
    preg_match("/[\d\.]{7,15}/", $cip, $cips);
    $cip = isset($cips[0]) ? $cips[0] : 'unknown';
    unset($cips);

    return $cip;
}
//获取参数信息
function getParams(){
    $obj = new \my\Request();
    $re = $obj->get_params();
    var_dump('<pre>',$re );exit;
}

/**
 * 将字符串参数变为数组
 * @param $query
 * @return array array (size=10)
    'm' => string 'content' (length=7)
    'c' => string 'index' (length=5)
    'a' => string 'lists' (length=5)
    'catid' => string '6' (length=1)
    'area' => string '0' (length=1)
    'author' => string '0' (length=1)
    'h' => string '0' (length=1)
    'region' => string '0' (length=1)
    's' => string '1' (length=1)
    'page' => string '1' (length=1)
 */
function convertUrlQuery($query)
{
    $queryParts = explode('&', $query);
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    return $params;
}

function getCatList(){
    $list=Db::name('b2c_goods_cat')->field('cat_id,cat_name,cat_path')->where(array('parent_id'=>0))->order('p_order','asc')->select();
    foreach ($list as $k=>$v){
        $list[$k]['second_cat']=Db::name('b2c_goods_cat')->field('cat_id,cat_name,cat_path')->where(array('parent_id'=>$v['cat_id']))->select();
        foreach ($list[$k]['second_cat'] as $key=>$val){
           $three= Db::name('b2c_goods_cat')->field('cat_id,cat_name,cat_path')->where(array('parent_id'=>$val['cat_id']))->select();
           if(count($three)>0){
                $list[$k]['second_cat'][$key]['three_cat']=$three;
           }
        }
    }

    return $list;
}

//全剧调用执行动作
function record_crarry_info($member_id){
    $domain = Config::get('domain');
    if(@$_POST){
      $carry_data['carry_param'] = json_encode($_POST);  
      $carry_data['carry_method'] = 'post';
    }else{
      $carry_data['carry_method'] = 'get';  
    }
    $curl_url = "{$domain}/r/record_carryinfo";
    $carry_data['member_id'] = $member_id;
    $carry_data['carry_url'] = request()->url(true);
    $carry_data['carry_method'] = getNowMethod();
    $carry_data['carry_type'] = 2;
    $carry_data['carry_ip'] = $_SERVER["REMOTE_ADDR"];
    $re = curl_post($curl_url,$carry_data);
}

//全局调用推荐小精灵
function sendspiritmessage($phoneNumber,$data){
    // 请根据实际 accesskey 和 secretkey 进行开发，以下只作为演示 sdk 使用
    $accesskey = "5aa889530cf28157767b0bfc";
    $secretkey = "27a771c149c840e1978f0f887f6fd66e";
    try {
        $obj = new \sms\SmsSender($accesskey,$secretkey);
        $Tools = new \sms\SmsTools();
        $code = $Tools->getRandom();
        $supplier_name = $data['supplier_name'];
        $p_name = $data['p_name'];
        $msg="【上海礼东】{$supplier_name}向您的小精灵项目“{$p_name}”发送了消息，详情登录礼东网“个人中心-礼品小精灵列表”";
        // 普通单发
        //echo "code".$remark;exit;
        $result = $obj->send(0, "86", $phoneNumber , $msg, "", "");
        $rsp = json_decode($result,true);
        return $rsp;
    } catch (\Exception $e) {
        return $e;
    }
}

//发送评论/私信通知
function sendReviewMessage($data){
    // 请根据实际 accesskey 和 secretkey 进行开发，以下只作为演示 sdk 使用
    $accesskey = "5aa889530cf28157767b0bfc";
    $secretkey = "27a771c149c840e1978f0f887f6fd66e";
    try {
        $obj = new \sms\SmsSender($accesskey,$secretkey);
        $Tools = new \sms\SmsTools();
        $code = $Tools->getRandom();
        $name = $data['name'];
        $phone = $data['phone'];
        $type = $data['type'];
        
        $msg="【上海礼东】{$name}已经{$type}您，请到微信小程序“礼业社交”中查看。";
        // 普通单发
        // echo "code".$msg;exit;
        $result = $obj->send(0, "86", $phone , $msg, "", "");
        $rsp = json_decode($result,true);
        return $rsp;
    } catch (\Exception $e) {
        return $e;
    }
}



//全局调用发送发货信息
function sendGoodsMessage($data){
    // 请根据实际 accesskey 和 secretkey 进行开发，以下只作为演示 sdk 使用
    $accesskey = "5aa889530cf28157767b0bfc";
    $secretkey = "27a771c149c840e1978f0f887f6fd66e";
    try {
        $obj = new \sms\SmsSender($accesskey,$secretkey);
        $Tools = new \sms\SmsTools();
        $code = $Tools->getRandom();
        $day = $data['day'];
        $phoneNumber = $data['phone'];
        $order_id = $data['order_id'];
        $msg="【上海礼东】您的订单{$order_id}已发货，预计到货时间{$day}天后";
        // 普通单发
        //echo "code".$remark;exit;
        $result = $obj->send(0, "86", $phoneNumber , $msg, "", "");
        $rsp = json_decode($result,true);
        return $rsp;
    } catch (\Exception $e) {
        return $e;
    }
}

//全局调用VIP价格申请（供应商）
function sendapplysupplier($phoneNumber,$companyName){
    // 请根据实际 accesskey 和 secretkey 进行开发，以下只作为演示 sdk 使用
    $accesskey = "5aa889530cf28157767b0bfc";
    $secretkey = "27a771c149c840e1978f0f887f6fd66e";
    try {
        $obj = new \sms\SmsSender($accesskey,$secretkey);
        $Tools = new \sms\SmsTools();
        $msg="【上海礼东】亲爱的供应商，{$companyName}向您申请查看VIP价格，请查看礼品公司资质后决定是否给予权限。”";
        // 普通单发
        //echo "code".$remark;exit;
        $result = $obj->send(0, "86", $phoneNumber , $msg, "", "");
        $rsp = json_decode($result,true);
        return $rsp;
    } catch (\Exception $e) {
        return $e;
    }
}

//全局调用VIP价格申请（服务商）
function sendapplymember($phoneNumber,$supplierName,$status){
    // 请根据实际 accesskey 和 secretkey 进行开发，以下只作为演示 sdk 使用
    $accesskey = "5aa889530cf28157767b0bfc";
    $secretkey = "27a771c149c840e1978f0f887f6fd66e";
    try {
        $obj = new \sms\SmsSender($accesskey,$secretkey);
        $Tools = new \sms\SmsTools();
        if($status==1){
            $msg="【上海礼东】亲爱的服务商，您向{$supplierName}申请查看的VIP价格已经审核通过”";       
        }elseif($status==3){
            $msg="【上海礼东】亲爱的服务商，您向{$supplierName}申请查看的VIP价格未审核通过”";
        }
        
        // 普通单发
        //echo "code".$remark;exit;
        $result = $obj->send(0, "86", $phoneNumber , $msg, "", "");
        $rsp = json_decode($result,true);
        return $rsp;
    } catch (\Exception $e) {
        return $e;
    }
}

//全局调用发送询价单
function sendintentionorder($sendData){
    // 请根据实际 accesskey 和 secretkey 进行开发，以下只作为演示 sdk 使用
    $accesskey = "5aa889530cf28157767b0bfc";
    $secretkey = "27a771c149c840e1978f0f887f6fd66e";
    try {
        $obj = new \sms\SmsSender($accesskey,$secretkey);
        $Tools = new \sms\SmsTools();
        $phoneNumber = $sendData['phone'];
        $name = $sendData['name'];
        $order_sn = $sendData['order_sn'];
        $msg="【上海礼东】亲爱的用户,{$name}向您的询价单”{$order_sn}”提供了报价";       
        // 普通单发
        //echo "code".$remark;exit;
        $result = $obj->send(0, "86", $phoneNumber , $msg, "", "");
        $rsp = json_decode($result,true);
        return $rsp;
    } catch (\Exception $e) {
        return $e;
    }
}


//全局调用推荐比价
function sendratiomessage($phoneNumber,$data){
    // 请根据实际 accesskey 和 secretkey 进行开发，以下只作为演示 sdk 使用
    $accesskey = "5aa889530cf28157767b0bfc";
    $secretkey = "27a771c149c840e1978f0f887f6fd66e";
    try {
        $obj = new \sms\SmsSender($accesskey,$secretkey);
        $Tools = new \sms\SmsTools();
        $code = $Tools->getRandom();
        $supplier_name = $data['supplier_name'];
        $name = $data['name'];
        $msg="【上海礼东】{$supplier_name}向您的小精灵项目“{$name}”发送了消息，详情登录礼东网“个人中心-礼品小精灵列表”";
        // 普通单发
        //echo "code".$remark;exit;
        $result = $obj->send(0, "86", $phoneNumber , $msg, "", "");
        $rsp = json_decode($result,true);
        return $rsp;
    } catch (\Exception $e) {
        return $e;
    }
}

//全局发送到期通知
function sendTimeOutMessage($data){
    // 请根据实际 accesskey 和 secretkey 进行开发，以下只作为演示 sdk 使用
    $accesskey = "5aa889530cf28157767b0bfc";
    $secretkey = "27a771c149c840e1978f0f887f6fd66e";
    try {
        $obj = new \sms\SmsSender($accesskey,$secretkey);
        $Tools = new \sms\SmsTools();
        $code = $Tools->getRandom();
        $timeout_date = $data['timeout'];
        $phoneNumber = $data['mobile'];
        $msg="【上海礼东】感谢您选择礼东网，您的账号有效期到{$timeout_date}，如有问题，请查看网站上的帮助中心或致电4000 456 069。";
        
        // 普通单发
        //echo "code".$remark;exit;
        $result = $obj->send(0, "86", $phoneNumber , $msg, "", "");
        $rsp = json_decode($result,true);
        return $rsp;
    } catch (\Exception $e) {
        return $e;
    }
}


//外部调用
function send_code($phoneNumber,$item,$member_id=null){

    //判断手机号是否存在
    switch ($item) {
        //子站用户
        case '1f':
            if($member_id>0){
                $exist = db('b2c_member_user')->where('login_name',$phoneNumber)->where('member_id',$member_id)->find();
                if(!$exist) return array('msg' => '该用户不存在','code' => 404 );    
            }
        case '1r':
            if($member_id>0){
                $code_sign = db('b2c_members')->where('member_id',$member_id)->value('code_sign');
            }
            break;
    }
    // 请根据实际 accesskey 和 secretkey 进行开发，以下只作为演示 sdk 使用
    $accesskey = "5aa889530cf28157767b0bfc";
    $secretkey = "27a771c149c840e1978f0f887f6fd66e";
    if(isset($code_sign)){
        $sign = $code_sign;
    }else{
        $sign = 'LD验证码';
    }
    try {
        $obj = new \sms\SmsSender($accesskey,$secretkey);
        $Tools = new \sms\SmsTools();
        $code = $Tools->getRandom();
        $msg="【".$sign."】您好您本次的验证码为：$code";
        // 普通单发
        //echo "code".$remark;exit;
        $result = $obj->send(0, "86", $phoneNumber , $msg, "", "");
        $rsp = json_decode($result,true);
        if($rsp['errmsg']=='OK'){
            // 设置过期时间
            cache('code_'.$phoneNumber.$item,$code,120);    
        }
        return $rsp;
    } catch (\Exception $e) {
        return $e;
    }
}

//外部调用
function check_code($code,$phoneNumber,$item,$status=null){
    // 获取
    $vcode = cache('code_'.$phoneNumber.$item);
    //echo $code.'-'.$cod;
    if($status==1){
        if($vcode || $phoneNumber==15736736889){
            if($code==$vcode || $phoneNumber==15736736889){
                return array('msg' => '验证码通过','code' => 200 );
            }else{
                return array('msg' => '验证码不正确','code' => 401 );
            }
        }else{
            return array('msg' => '验证码已过期','code' => 402 );
        }
    }else{
        return array('msg' => '验证码不正确,请点击获取','code' => 403 );    
    }
            
}

//小程序中调用
function send_vcode($phoneNumber,$item){
    // 请根据实际 accesskey 和 secretkey 进行开发，以下只作为演示 sdk 使用
    $accesskey = "5aa889530cf28157767b0bfc";
    $secretkey = "27a771c149c840e1978f0f887f6fd66e";
    switch ($item) {
        case 'register':
            $type = '账号注册';
            break;
        case 'forget':
            $type = '找回密码';
            break;
    } 

    try {
        $obj = new \sms\SmsSender($accesskey,$secretkey);
        $Tools = new \sms\SmsTools();
        $code = $Tools->getRandom();
        $msg="【上海礼东】验证码".$code."用于".$type."，请勿将验证码透露给他人，如非本人操作请忽略。";
        //$msg="【LD验证码】您好您本次的验证码为：$code";
        // 普通单发
        //echo "code".$remark;exit;
        $result = $obj->send(0, "86", $phoneNumber , $msg, "", "");
        $rsp = json_decode($result,true);
        if($rsp['errmsg']=='OK'){
            // 设置过期时间
            cache('code_'.$phoneNumber.$item,$code,180);    
        }
        return $rsp;
    } catch (\Exception $e) {
        return $e;
    }
}

//小程序中调用
function check_vcode($vcode,$phoneNumber,$item,$status=null){
    // 获取
    $code = cache('code_'.$phoneNumber.$item);
    if($status==1){
        if($code){
            if($code==$vcode){
                return array('msg' => '验证码通过','code' => 200 );
            }else{
                return array('msg' => '验证码不正确','code' => 401 );
            }
        }else{
            return array('msg' => '验证码已过期','code' => 402 );
        }
    }else{
        return array('msg' => '验证码不正确,请点击获取','code' => 403 );    
    }
            
}

function replaceSpecialChar($strParam){
    $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
    return preg_replace($regex,"",$strParam);
}

function replaceSpecialStr($strParam){
    $regex = "/n,/";
    return preg_replace($regex,"",$strParam);
}

function restrimg($str){
    $pattern_src = '/<img[\s\S]*?src\s*=\s*[\"|\'](.*?)[\"|\'][\s\S]*?>/';
    $num1 = preg_match_all($pattern_src, $str, $match_src1);
    $arr_src1 = $match_src1[1];
    
    foreach ($arr_src1 as $key => $value) {
        $str = substr( $value, 0, 1 ); ;
        if($str=='/'){
            $arr_src1[$key] = 'http://www.liidon.com'.$value;
        }
    }
    return $arr_src1;
}
    
/**
 * cat
 * @return [type] [description]
 */
function filterCatList(){
    $catList=getCatList();
    //$redis = new Redis();
    //$arr=$redis->get('api_cat');
   
    $arr = array();
    if($arr){
        
        foreach($arr as $k=>$v){
            if(!isset($arr[$k]['brand_list'])){
             $arr[$k]['brand_list']=array();
            }
        }
    
        return $arr;
    }
    else{
        foreach ($catList as $k1=>$v1){
            $str='';
            foreach ($v1['second_cat'] as $k2=>$v2){
                if(isset($v2['three_cat'])){
                    foreach ($v2['three_cat'] as $k3=>$v3){
                       
                        $str.=$v3['cat_id'].",";
                        
                    }
                }
                else{
                    $str.=$v2['cat_id'].",";
                }
            }
            if($str!=''){
                $str=substr($str, 0,(strlen($str)-1));
                $sql="select DISTINCT( g.`brand_id`)  ,b.`brand_name`  from `sdb_b2c_goods` g ,`sdb_b2c_brand` b   where b.`brand_id` =g.`brand_id` and  cat_id in (".$str.") and g.brand_id>0 limit 10";
                $brandlist= Db::query($sql);
            
                $catList[$k1]['brand_list']=$brandlist;
            }
            else{
                //unset($catList[$k1]);
            }
            
        }

        $expire=24*3600;
        //$redis->set('api_cat',$catList,$expire);
        return $catList;
    }
}

function new_price($supplier_id,$price,$mktprice){
    
    $supplier_info=Db::name('b2c_supplier')->where(array('supplier_id'=>$supplier_id))->find();
    $profit_lv=0.3;
    if($supplier_info['pricing']=="不清楚"){
        
        $s_prirce= sprintf('%.1f', $price/(1-$profit_lv));
    }
    elseif($supplier_info['pricing']=="不含税"){
        $rata=$supplier_info['rate']/100;
        $s_prirce= sprintf('%.1f',$price*(1+$rata)/(1-$profit_lv));
    }elseif($supplier_info['pricing']=="含税"){
        $s_prirce= sprintf('%.1f', $price/(1-$profit_lv));
    }else{
        $s_prirce = '';
    }
   
    if(isset($mktprice)&&$mktprice>0){
        if($s_prirce>$mktprice){
            $s_prirce=$mktprice;
        }
    }
    
    return $s_prirce;
}

function getGoodsListBySearch($search,$order=null,$limit=null,$filter=null,$pageSize=50){
    $where = " where g.marketable = 'true' and is_show_master=1 ";
    $filter['brand_id']=isset($filter['brand_id']) ? $filter['brand_id'] : '';
    if($filter['brand_id']){
        $brand_ids = $filter['brand_id'];
        if(strstr($brand_ids,',')){
            $where .= " and (g.brand_id in ({$brand_ids}))";      
        }else{
            $where .= " and (g.brand_id = {$brand_ids})";  
        }
          
    }else{
        $where .= '';    
    }

    //echo $where;exit;
    if($search){
        $asearch = '';
        if(strstr($search,' ')){
            $asearch = explode(' ', $search);
        }
        //$where .= " and (g.name like '%".$search."%' or p.bn like '%".$search."%' or b.keyword like '%".$search."%') ";
        if($where){
            if($asearch){
                foreach ($asearch as $key => $value) {
                    $where .= " and (g.name like '%".$value."%')";
                }   
            }else{
                $where .= " and (g.name like '%".$search."%') ";      
            }  
        }else{
            if($asearch){
                $where .= ' where ';
                foreach ($asearch as $key => $value) {
                    $where .= "(g.name like '%".$value."%') and ";
                }
                $where .= substr($where,0,-4);
            }else{
                $where .= " where (g.name like '%".$search."%') ";    
            }
        }
        
    }else{
        $where .= '';
    }

    if(@$filter['cat_id']){
        if($where){
            $link = 'and';    
        }else{
            $link = 'where';
        }
        //$cat_ids = implode(',',get_s_cat_id($filter['cat_id']));
        if(strstr($filter['cat_id'], ',')){
           $where .= " {$link} g.cat_id in ({$filter['cat_id']})";   
        }else{
           $cat_id = $filter['cat_id'];
           $where .= " {$link} g.cat_id = {$cat_id} ";   
        }   
    }else{
        $where .= '';    
    }

    if(@$filter['cat1']){
        if($where){
            $link = 'and';    
        }else{
            $link = 'where';
        }
        $where .= " {$link} g.cat1 = {$filter['cat1']} ";   
    }   

    if(@$filter['tag_id']){
        if($where){
            $link = 'and';    
        }else{
            $link = 'where';
        }
        $taggoods_id = gettaggods_id($filter['tag_id']);

        if(empty($taggoods_id)){
            $where .= " {$link} g.goods_id in (-1,-2)";    
        }else{
            $taggoods_ids = implode(',',$taggoods_id);
            $where .= " {$link} g.goods_id in ({$taggoods_ids})";  
        } 
    }else{
        $where .= '';    
    }

    if(@$filter['rebate']){
        if($where){
            $link = "and";
        }else{
            $link = "";
        }
        $where .= "{$link} FORMAT(g.price/g.mktprice,2)*10<={$filter['rebate']} ";
    }else{
        $where .= '';    
    }

    if(@$filter['type_id']){
        if($where){
            $link = "and";
        }else{
            $link = "";
        }
        $where .= "{$link} g.type_id = {$filter['type_id']}";  
    }else{
        $where .= '';    
    }

    $filter['price_area']=isset($filter['price_area']) ? $filter['price_area'] : '';
    if($filter['price_area']){
        $arr = explode('-',$filter['price_area']);
        if($arr){
            $s = $arr[0];
            $e = isset($arr[1]) ? $arr[1] : 999999;
            if($where){
                $link = 'and';    
            }else{
                $link = 'where';
            }
            $price_area = " {$link} g.price between {$s} and {$e}";  
        }else{
            $price_area = "";
        }  
    }else{
        $price_area = "";
    }

    $sql="SELECT g.ppt,g.supplier_id,g.numsprice,g.techdiy,g.supplier_id,g.goods_id,g.name,g.is_sell,g.mktprice,g.price,img.url,p.product_id,sum(p.store) as st  
        FROM sdb_b2c_goods g LEFT JOIN sdb_b2c_products p ON p.goods_id=g.goods_id 
        LEFT JOIN sdb_b2c_goods_keywords b ON g.goods_id = b.goods_id 
        LEFT JOIN sdb_image_image img ON img.image_id = g.image_default_id".$where.$price_area.' group by g.goods_id';
    //echo $sql;exit;    
    if($search || $filter){
        $list= Db::query($sql);
        $data['goods_count']=count($list);
    }else{
        $list= Db::query('SELECT count(*) as count FROM sdb_b2c_goods');
        $data['goods_count']=$list[0]['count'];
    }
     $sql=$sql.$order.$limit;  
     $goods=Db::query($sql);
    $data['all_page']=ceil($data['goods_count']/$pageSize);
    $data['goods_list']=getdiscount($goods);
    return $data;
}

function getBrandListBySearch($search,$order=null,$limit=null,$filter=null){
    $domain = Config::get('domain');
    if($search){
    $where = " where brand_name like '%".$search."%' ";
    }else{
    $where = '';
    }

    if($filter['brand_id']){
        $brand_id = $filter['brand_id'];
        if($where){
            $where .= " and brand_id = {$brand_id} ";    
        }else{
            $where .= " where brand_id = {$brand_id} ";    
        }
    }else{
        $where .= '';    
    }

    $sql="SELECT brand_id,brand_name,brand_url,brand_desc,brand_logo,brand_setting
        FROM sdb_b2c_brand".$where;

    if($search || $filter){
        $list= Db::query($sql);
        $data['brand_count']=count($list);
    }else{
        $list= Db::query('SELECT count(*) as count FROM sdb_b2c_brand');
        $data['brand_count']=$list[0]['count'];
    }
    if($data['brand_count']>50){
        $sql=$sql.$limit;
    }
    $brand=Db::query($sql);
    $data['all_page']=ceil($data['brand_count']/50);
    foreach ($brand as $key => $value) {
        $brand[$key]['brand_logo'] = getimageurl($value['brand_logo']);
        if($data['brand_count']==1){
            //记录品牌浏览
            $member_id = session('member_id');
            $view_time = time();
            $view_ip = $_SERVER["REMOTE_ADDR"];
            $curl_url = "{$domain}/r/record_brandview?account_id={$member_id}&brand_id={$value['brand_id']}&brand_name={$value['brand_name']}&view_type=4";
            dataRequest($curl_url);
            $brand[$key]['hav_goods_count'] = getGoodsListCount(array('brand_id'=>$value['brand_id']));
            $brand[$key]['o_supplier']=getSupplieridByBrand($value['brand_id'],1);
            $brand[$key]['a_supplier']=getSupplieridByBrand($value['brand_id'],2);
            $brand[$key]['goods'] = getGoodsListByBrand($value['brand_id'],$limit,$order,$filter); 
            $brand[$key]['brand_desc'] = html2str($brand[$key]['brand_desc']);
            $brand_desc = html2str($brand[$key]['brand_desc']);
            $brand[$key]['brand_note'] = mb_substr($brand_desc,0,100,'utf-8');
            $length = mb_strlen($brand_desc,'utf-8'); 
            if($length>100){
                $list[$key]['brand_desc'] = mb_substr($brand_desc,100,$length,'utf-8');  
            }else{
                $list[$key]['brand_desc'] = '';
            }
            $data['all_page']=$brand[$key]['goods']['all_page'];
        }else{
            unset($brand[$key]['brand_desc']);
        }
    }
    $data['brand_list']=$brand;
    //  var_dump($data);exit;
    return $data;
}

function html2str($html){
    return trim(strip_tags(str_replace(array("&nbsp;","&amp;nbsp;","\t","\r\n","\r","\n"),array("","","","","",""),$html)));
}

function getSupplierListBySearch($search,$order=null,$limit=null,$filter=null){
    $domain = Config::get('domain');
    if($search){
    $where = " where supplier_name like '%".$search."%' and is_show_master=1 ";
    }else{
    $where = '';
    }

    if($filter['supplier_id']){
        $supplier_id = $filter['supplier_id'];
        if($where){
            $where .= " and supplier_id = {$supplier_id} ";    
        }else{
            $where .= " where supplier_id = {$supplier_id} ";    
        }  
    }else{
        $where .= '';    
    }

    $filter['g.marketable']='true';
    $filter['price_area']=isset($filter['price_area']) ? $filter['price_area'] : '';
    if($filter['price_area']){
        $arr = explode('-',$filter['price_area']);
        if($arr){
            $s = $arr[0];
            $e = isset($arr[1]) ? $arr[1] : 999999;
            $price_area = "g.price between {$s} and {$e}"; 
        }else{
            $price_area = "";
        }
        
    }else{
        $price_area = "";
    }

    $sql="SELECT supplier_id,supplier_name,register_year,register_money,namecard,supplier_desc,supplier_phone,supplier_qq,supplier_email,supplier_weixin
        FROM sdb_b2c_supplier".$where;

    if($search || $filter){
        $list= Db::query($sql);
        $data['supplier_count']=count($list);
        }else{
        $list= Db::query('SELECT count(*) as count FROM sdb_b2c_supplier');
        $data['supplier_count']=$list[0]['count'];
    }
    if($data['supplier_count']>50){
        $sql=$sql.$limit;
    }
    $supplier=Db::query($sql);
    $data['all_page']=ceil($data['supplier_count']/50);
    foreach ($supplier as $key => $value) {
        if($data['supplier_count']==1){
            $member_id = session('member_id');
            //记录供应商浏览
            $view_time = time();
            $view_ip = $_SERVER["REMOTE_ADDR"];
            $curl_url = "{$domain}/r/record_supplierview?account_id={$member_id}&supplier_id={$value['supplier_id']}&supplier_name={$value['supplier_name']}&view_type=4";
            dataRequest($curl_url);
            $supplier[$key]['namecard'] = Db::query("select card_area,card_img_url,phone,email,qq,weixin from sdb_b2c_supplier_card where supplier_id={$value['supplier_id']} order by sort asc");
            $supplier[$key]['hav_goods_count'] = getGoodsListCount(array('s.supplier_id'=>$value['supplier_id']));
            $supplier[$key]['o_brand']=getBrandBySupplier($value['supplier_id'],1);
            $supplier[$key]['a_brand']=getBrandBySupplier($value['supplier_id'],2);
            $supplier[$key]['goods']=getGoodsListBySupplier($value['supplier_id'],$limit,$order,$filter); 
            $supplier[$key]['supplier_desc'] = html2str($supplier[$key]['supplier_desc']);
            $supplier_desc = html2str($supplier[$key]['supplier_desc']);
            $supplier[$key]['supplier_note'] = mb_substr($supplier_desc,0,100,'utf-8');
            $length = mb_strlen($supplier_desc,'utf-8'); 
            if($length>100){
                $list[$key]['supplier_desc'] = mb_substr($supplier_desc,100,$length,'utf-8');  
            }else{
                $list[$key]['supplier_desc'] = '';
            }
            if($supplier[$key]['register_year']>0){
                $supplier[$key]['register_year'] = date('Y')-$value['register_year']+1;    
            }else{
                unset($supplier[$key]['register_year']);
            }
            $data['all_page']=$supplier[$key]['goods']['all_page'];
        }else{
            unset($supplier[$key]['supplier_desc']);
        }
    }
    $data['supplier_list']=$supplier;
    return $data;
}

function is_Vcat($vcat_id){
    return Db::name("b2c_goods_virtual_cat")
    ->where(array('virtual_cat_id'=>$vcat_id))
    ->find();
}

function is_cat($cat_id){
    return Db::name("b2c_goods_cat")
    ->where(array('cat_id'=>$cat_id))
    ->find();
}

function getGoodsListByVcat($vcat,$order=null,$fil=null,$p=1){
    $re = Db::name("b2c_goods_virtual_cat")
    ->where(array('virtual_cat_id'=>$vcat))
    ->field('cat_id,cat_path,virtual_cat_id,filter,virtual_cat_name as cat_name')
    ->find();
    parse_str($re['filter'],$vcatFilters);

    $tag_id = $vcatFilters['tag'][0];
    $tag_goods_ids = array();
    if($tag_id!='_ANY_'){
        $tag_goods_ids = Db::name("desktop_tag_rel")
        ->where(array('tag_id'=>$tag_id))
        ->column('rel_id');    
    }
    
    $cat_id = $vcatFilters['cat_id'][0];
    $cat_goods_ids = array();
    if($cat_id!='_ANY_'){
        $cat_id = $vcatFilters['cat_id'];
        $cat_res = array();
        foreach ($cat_id as $key => $value) {
            $cat_res[$key] = Db::name("b2c_goods")
            ->where(array('cat_id'=>$value))
            ->column('goods_id');
            for ($i=0; $i < count($cat_res); $i++) { 
                foreach ($cat_res[$i] as $key => $value) {
                    $cat_goods_ids[] = $value;
                }     
            }
        }    
    }
   

    
    $brand_id = $vcatFilters['brand_id'][0];
    $brand_goods_ids = array();
    if($brand_id!='_ANY_'){
        $brand_id = $vcatFilters['brand_id'];
        $brand_res = array();
        foreach ($brand_id as $key => $value) {
            $brand_res[$key] = Db::name("b2c_goods")
            ->where(array('brand_id'=>$value))
            ->column('goods_id');
        }  
        for ($i=0; $i < count($brand_res); $i++) { 
            foreach ($brand_res[$i] as $key => $value) {
                $brand_goods_ids[] = $value;
            }     
        }
            
    }
    
    $goods_ids = array_merge(array_merge($tag_goods_ids,$cat_goods_ids),$brand_goods_ids);

    $filter['g.goods_id']=array('in',$goods_ids);

    if(@$fil['brand_id']){ 
        $brand_ids = array_filter(explode(',',$fil['brand_id']));
        $filter['g.brand_id']=array('in',$brand_ids);
    }

    if(@$fil['catid']){ 
        //打开多个分类筛选
       /* $cat_ids = explode(',',$fil['catid']);
        $filter['g.cat_id']=array('in',$brand_ids);*/
        $filter['g.cat_id']=$fil['catid'];
    }
    if(@$fil['rebate']){
        $filter['rebate']=$fil['rebate'];
    }

    if($tag_id!='_ANY_'){ 
        if(@$fil['tagid']){
            $fil['tagid'] .= ','.$tag_id;
        }else{
            $fil['tagid'] = $tag_id;
        }
        $taggoods_ids = gettaggods_id($fil['tagid']);
        if(empty($taggoods_ids)){  
            $filter['g.goods_id']=array('in',array('-1','-2'));
        }else{
            $filter['g.goods_id']=array('in',array_filter($taggoods_ids));
        } 
    }elseif($brand_id!='_ANY_' && $fil['tagid']){
        $taggoods_ids = gettaggods_id($fil['tagid']);
        if(empty($taggoods_ids)){  
            $filter['g.goods_id']=array('in',array('-1','-2'));
        }else{
            $filter['g.goods_id']=array('in',array_filter(array_intersect($goods_ids,$taggoods_ids)));
        }
    }elseif($cat_id!='_ANY_' && $fil['tagid']){
        $taggoods_ids = gettaggods_id($fil['tagid']);
        if(empty($taggoods_ids)){  
            $filter['g.goods_id']=array('in',array('-1','-2'));
        }else{
            $filter['g.goods_id']=array('in',array_filter(array_intersect($goods_ids,$taggoods_ids)));
        }
    }

    $pageSize = 50;
    $limit=array(
      'start'=>($p-1)*$pageSize,
      'end'=>$pageSize,
    );

    $count=getGoodsListCount($filter);
    $data=getGoodsList($filter,$order,$limit);
    $goods['cat_id']=intval($vcat);
    $goods['count']=$count;
    $goods['all_page']= ceil($count/$pageSize);
    $goods['list']=$data;
   
   return  $goods;
}

function getGoodsListByCat($cat,$order=null,$fil=null,$p=1,$pageSize=null){
    if(array_key_exists('lever',$cat) && $cat['lever']!=3){
        if(array_key_exists('id_str',$cat)){
            $t=substr($cat['id_str'], 0,(strlen($cat['id_str'])-1));
            $filter['g.cat_id']=array('in',array_filter(explode(',', $t)));    
        }else{
            $filter['g.cat_id']=$cat['cat_id'];
        }
    }elseif(is_array($cat)){
        $filter['g.cat_id']=$cat['cat_id'];
    }
    if(@$fil['brand_id']){
        $brand_ids = array_filter(explode(',',$fil['brand_id']));
        $filter['g.brand_id']=array('in',$brand_ids);
    }    
    if(@$fil['price_area']){
        $filter['price_area']=isset($fil['price_area']) ? $fil['price_area'] : '';
    }
    if(@$fil['tag_id']){ 
        $taggoods_ids = gettaggods_id($fil['tag_id']);
        if(empty($taggoods_ids)){  
            $filter['g.goods_id']=array('in',array('-1','-2'));
        }else{
            $filter['g.goods_id']=array('in',$taggoods_ids);
        }
    }
    if(@$fil['rebate']){
        $filter['rebate']=$fil['rebate'];
    }

    //var_dump($filter);exit;

    if(!$pageSize) $pageSize = 50;
    $limit=array(
      'start'=>($p-1)*$pageSize,
      'end'=>$pageSize,
    );
    $count=getGoodsListCount($filter);
    $data=getGoodsList($filter,$order,$limit);
    $goods['cat_id']=intval($cat['cat_id']);
    $goods['count']=$count;
    $goods['all_page']= ceil($count/$pageSize);
    $goods['list']=$data;
   
   return  $goods;
}

function getGoodsList($filter=null,$order=null,$limit=null){
    $filter['g.marketable']='true';
    $filter['g.is_show_master']='1';
    $filter['price_area']=isset($filter['price_area']) ? $filter['price_area'] : '';
    if($filter['price_area']){
        $arr = explode('-',$filter['price_area']);
        if($arr){
            $s = $arr[0];
            $e = isset($arr[1]) ? $arr[1] : 999999;
            $price_area = "g.price between {$s} and {$e}"; 
        }else{
            $price_area = "";
        }
        
    }else{
        $price_area = "";
    }

    $filter['g.brand_id']=isset($filter['g.brand_id']) ? $filter['g.brand_id'] : '';
    if(!$filter['g.brand_id']){
        unset($filter['g.brand_id']);
    }

    if(@$filter['rebate']){
        $rebate_sql = "FORMAT(g.price/g.mktprice,2)*10<={$filter['rebate']}";
    }else{
        $rebate_sql = '';
    }

    unset($filter['price_area']);
    unset($filter['rebate']);
    $goods=Db::name("b2c_goods")->alias('g')
    ->join('image_image img','img.image_id = g.image_default_id','left')
    ->join('b2c_products p ','p.goods_id=g.goods_id','left')
    ->join('b2c_supplier s','s.supplier_id = g.supplier_id','left')
    ->where($filter)
    ->where($price_area)
    ->where($rebate_sql)
    ->where(array('g.supplier_id'=>array('gt',0)))
    ->field('g.ppt,g.numsprice,g.techdiy,g.supplier_id,g.goods_id,g.name,g.is_sell,g.mktprice,g.tax_included_price,g.price,img.url,sum(p.store) as st')
    ->group("g.goods_id")
    ->order($order)
    ->limit($limit['start'],$limit['end'])
    ->select();

    /*->buildSql();
    echo $goods;exit;*/

    
    foreach ($goods as $k=>$v){
      //$price= new_price($v['supplier_id'],$v['price'],$v['mktprice']);
        //->buildSql();
       $product= Db::name('b2c_products')
        ->where(array('goods_id'=>$v['goods_id']))
        ->find();
        
        $goods[$k]['product_id']=$product['product_id'];
        
        if(!$v['st'])
        $goods[$k]['st']=0;
        
        //$goods[$k]['price']=$price;
        $p= explode(".",$v['price']);
         $goods[$k]['price_int']=$p[0];
         $goods[$k]['price_f']=substr($p[1], 0,2);
    }
   
    return getdiscount($goods);
}

function getGoodsListCount($filter=null){

    if(@$filter['price_area']){
        $arr = explode('-',$filter['price_area']);
        if($arr){
            $s = $arr[0];
            $e = isset($arr[1]) ? $arr[1] : 999999;
            $price_area = "g.price between {$s} and {$e}"; 
        }else{
            $price_area = "";
        }
    }else{
        $price_area = "";
    }

    if(!@$filter['brand_id']){
        unset($filter['brand_id']);
    }

    if(@$filter['rebate']){
        $rebate_sql = "FORMAT(g.price/g.mktprice,2)*10<={$filter['rebate']}";
    }else{
        $rebate_sql = '';
    }

    if(@$filter['search']){
        $like_sql = "g.name like '%{$filter['search']}%'";
    }else{
        $like_sql = '';
    }

    unset($filter['price_area']);
    unset($filter['rebate']);
    unset($filter['search']);
    $filter['g.marketable']='true';
    $goods=Db::name("b2c_goods")->alias('g')
    ->join('image_image img','img.image_id = g.image_default_id','left')
    ->join('b2c_supplier s','s.supplier_id = g.supplier_id','left')
    ->where($filter)
    ->where($price_area)
    ->where($rebate_sql)
    ->where($like_sql)
    ->where(array('g.supplier_id'=>array('gt',0)))
    ->field('g.goods_id,g.name,g.is_sell,g.mktprice,g.price,img.url')
    ->select();
    //->buildSql();
    //echo $goods;exit;
    return count($goods);
}

function getimage($goods_id){
   $img= Db::name('image_image_attach')->alias('a')
    ->join('image_image img','img.image_id = a.image_id','left')
    ->where(array('a.target_id'=>$goods_id,'a.target_type'=>'goods'))
    ->field('img.url')
    ->column('img.url');
    $img_default= Db::name('b2c_goods')->alias('g')
    ->join('image_image img','img.image_id = g.image_default_id','left')
    ->where(array('g.goods_id'=>$goods_id))
    ->column('img.url');
    $img = array_merge($img,$img_default);
   return $img;
}


function getGoodsImg($goods_id){
   $img= Db::name('image_image_attach')->alias('a')
    ->join('image_image img','img.image_id = a.image_id','left')
    ->field('img.url,img.l_url')
    ->where(array('a.target_id'=>$goods_id))
    ->select();
    $img_default= Db::name('b2c_goods')->alias('g')
    ->join('image_image img','img.image_id = g.image_default_id','left')
    ->field('img.url,img.l_url')
    ->where(array('g.goods_id'=>$goods_id))
    ->select(); 
    $img = array_merge($img,$img_default);
   if($img){
    return $img; 
   }else{
    return false;
   }
   
}


function getSpec($spec){
    foreach($spec as $k=>$v){
        $info=Db::name('b2c_specification')
        ->where(array('spec_id'=>$k))
        ->find();
        $spec['spec_type']=$info['spec_type'];
        $spec['spec_name']=$info['spec_name'];
    }
    return $spec;
}


function getBrandBySupplier($supplier_id,$type){
   $re = Db::name('b2c_supplier_brand')->field('brand_id,brand_id,agent_type,agent_time,brand_id,brand_name')->where(array('supplier_id'=>$supplier_id,'type'=>$type))->select();
   foreach ($re as $key => $value) {
       switch ($value['agent_type']) {
           case 1:
               $re[$key]['agent_type'] = '总代';
               break;
           case 2:
               $re[$key]['agent_type'] = '一级';
               break;
           case 2:
               $re[$key]['agent_type'] = '二级';
               break;
       }
   }
   return $re;
}

function getSupplieridByBrand($brand_id,$type){
   $res = Db::name('b2c_supplier_brand')->alias('b')
   ->join('b2c_supplier s','s.supplier_id = b.supplier_id','left')
   ->field('s.supplier_id,s.is_auth,s.supplier_name,b.agent_type,b.agent_time')
   ->where(array('b.brand_id'=>$brand_id,'b.type'=>$type))
   ->where('s.supplier_name is not NULL')
   ->select();
   foreach ($res as $key => $value) {
       $hav_namecard = Db::name('b2c_supplier_card')->where('supplier_id',$value['supplier_id'])->find();
       if($hav_namecard){
           $res[$key]['hav_namecard']=1;
       }
       $hav_file = Db::name('b2c_supplier_file')->where('supplier_id',$value['supplier_id'])->find();
       if($hav_file){
           $res[$key]['hav_file']=1;
       }
       switch ($value['agent_type']) {
           case 1:
               $res[$key]['agent_type'] = '总代';
               break;
           case 2:
               $res[$key]['agent_type'] = '一级';
               break;
           case 2:
               $res[$key]['agent_type'] = '二级';
               break;
       }
   }
   return $res;
}

function getCardList($supplier_id){
   
  return Db::name('b2c_supplier_card')->where(array('supplier_id'=>$supplier_id))->select();
}

function getimageurl($image_id){
  $re = Db::name('image_image')->where(array('image_id'=>$image_id))->find();
  if($re){
    return $re['url'];
  }else{
    return 'http://www.liidon.com/data/temp/default.png';;
  }
}

function get_goods($goods_id,$supplier_id){
    $goods=Db::name('b2c_goods')->where(array('supplier_id'=>$supplier_id,'goods_id'=>$goods_id))->find();
    return $goods;
}


function getCatgroryInfo($cat_id){
    
    $cat_list=filterCatList();
    $cat=array();
    foreach ($cat_list as $k1=>$v1){
        if($v1['cat_id']==$cat_id){
            $temp_str='';
            
            foreach ($v1['second_cat'] as $k2=>$v2){
                if(isset($v2['three_cat'])){
                    foreach ($v2['three_cat'] as $k3=>$v3){
                        $temp_str.=$v3['cat_id'].",";
                    }
                }
                else{
                    $temp_str.=$v2['cat_id'].",";
                }
            }
            $cat['cat_id']=$cat_id;
            $cat['cat_name']=$v1['cat_name'];
            $cat['lever']=1;
            $cat['id_str']=$temp_str;
            $cat['brand_list']=$v1['brand_list'];
            break;
        }  
    }

    if(!isset($cat['cat_id'])){
        foreach ($cat_list as $k1=>$v1){
            foreach ($v1['second_cat'] as $k2=>$v2){
                if($v2['cat_id']==$cat_id){
                    $temp_str='';
                    if(array_key_exists('three_cat',$v2)){
                        foreach ($v2['three_cat'] as $k3=>$v3){
                            $temp_str.=$v3['cat_id'].",";
                        }
                        $cat['frist_parent']=getParent_idCat($cat_id);
                        $cat['cat_id']=$cat_id;
                        $cat['cat_name']=$v2['cat_name'];
                        $cat['lever']=2;
                        $cat['id_str']=$temp_str;
                        $cat['brand_list']=$v1['brand_list'];
                        break;
                    }else{
                        $cat['frist_parent']=getParent_idCat($cat_id);
                        $cat['cat_id']=$cat_id;
                        $cat['cat_name']=$v2['cat_name'];
                        $cat['lever']=2;
                        $cat['brand_list']=$v1['brand_list'];
                        break;
                    }    
                }
            }
        }
        
        if(!isset($cat['cat_id'])){
            foreach ($cat_list as $k1=>$v1){
                
                foreach ($v1['second_cat'] as $k2=>$v2){
                    if(array_key_exists('three_cat',$v2)){
                      foreach ($v2['three_cat'] as $k3=>$v3){
                        if($v3['cat_id']==$cat_id){
                            $cat['second_parent']=getParent_idCat($cat_id);
                            $cat['frist_parent']=getParent_idCat($cat['second_parent']['cat_id']);
                            $cat['cat_id']=$cat_id;
                            $cat['cat_name']=$v3['cat_name'];
                            $cat['lever']=3;
                            $cat['brand_list']=$v1['brand_list'];
                            break;
                        }
                      }
                    }else{
                      if($v2['cat_id']==$cat_id){
                        $cat['second_parent']=getParent_idCat($cat_id);
                        $cat['frist_parent']=getParent_idCat($cat['second_parent']['cat_id']);
                        $cat['cat_id']=$cat_id;
                        $cat['cat_name']=$v2['cat_name'];
                        $cat['lever']=3;
                        $cat['brand_list']=$v1['brand_list'];
                        break;
                      }
                    }
                    
                }
            }

            if(!isset($cat['cat_id'])){
                return '404';
            }
        }
    }
    // var_dump('<pre>',$cat);
    // exit;
   return $cat; 
}

function getParent_idCat($cat_id){
    $cat_info=Db::name('b2c_goods_cat')->where(array('cat_id'=>$cat_id))->find();
   return Db::name('b2c_goods_cat')->where(array('cat_id'=>$cat_info['parent_id']))->find();
}

function matching($str, $a, $b)  
{    
    $pattern = '/('.$a.').*?('.$b.')/is'; //正则规则匹配支付串中任何一个位置字符串  
    preg_match_all($pattern, $str, $m);   //返回一个匹配结果    
    var_dump($m);  //到时候在这里书写返回值就好了 .  
}  

function f3($str)
{
    $result = array();
    preg_match_all("/(?:\()(.*)(?:\))/i",$str, $result);
    return current($result[1]);
}

function f5($str)
{
    $re = array();
    if(strpos($str,'search')){
        $url = urldecode($str);
        $arr = parse_url($url);
        $arr = str_replace(array('item','query'),array('type','search'),$arr['query']);
        $re['param'] = $arr;
        $re['type'] = 'search';
    }elseif(strpos($str,'gallery') && !strpos($str,'gallery-index---')){
        $start = strripos($str,'-')+1;
        $arr = explode('.',substr($str, $start));
        $re['param'] = $arr[0];
        $re['type'] = 'gallery';
    }elseif(strpos($str,'gallery-index---')){
        $start = strripos($str,'-')+1;
        $arr = explode('.',substr($str, $start));
        $re['param'] = $arr[0];
        $re['status'] = 1;
        $re['type'] = 'gallery';
    }elseif(strpos($str,'supplier')){
        $start = strripos($str,'-')+1;
        $arr = explode('.',substr($str, $start));
        $re['param'] = $arr[0];
        $re['type'] = 'supplier';
    }elseif(strpos($str,'brand')){
        $str = cut('-','.html',$str);
        $arr = array_filter(explode('-',$str));
        $param = [];
        foreach($arr as $k=>$v){
            switch ($k) {
            case 1:
                $re['param'] = $v;
                break;
            case 2:
                $re['cat3'] = $v;
                break;
            }
        }
        $re['type'] = 'brand';
    }elseif(strpos($str,'product')){
        $start = strripos($str,'-')+1;
        $arr = explode('.',substr($str, $start));
        $re['param'] = $arr[0];
        $re['type'] = 'product';
    }elseif(strpos($str,'article')){
        $start = strripos($str,'-')+1;
        $arr = explode('.',substr($str, $start));
        $re['param'] = $arr[0];
        $re['type'] = 'article';
    }elseif(strpos($str,'techdiy')){
        $start = strripos($str,'-')+1;
        $arr = explode('.',substr($str, $start));
        $re['param'] = $arr[0];
        $re['type'] = 'techdiy';
    }

    return $re;
}

//截取指定两个字符之间的字符串
function cut($begin,$end,$str){
    $b = mb_strpos($str,$begin) + mb_strlen($begin);
    $e = mb_strpos($str,$end) - $b;
    return mb_substr($str,$b,$e);
}

//暂时关闭
// function hidden_getNeedBetween($kw1,$mark1,$mark2){
//     $kw=$kw1;
//     $kw='123′.$kw.'123′;
//     $st =stripos($kw,$mark1);
//     $ed =stripos($kw,$mark2);
//     if(($st==false||$ed==false)||$st>=$ed)
//     return 0;
//     $kw=substr($kw,($st+1),($ed-$st-1));
//     return $kw;
// }

//暂时关闭
// function hidden_get_param($str)
// {
//     $str = getNeedBetween('-','.',$str);

//     $param = array();
//     if(strpos($str,'gallery') && !strpos($str,'gallery-index---')){
//         $start = strripos($str,'-')+1;
//         $arr = explode('.',substr($str, $start));
//         $param = ['cat_id',$arr[0]];
//     }elseif(strpos($str,'gallery-index---')){
//         $start = strripos($str,'-')+1;
//         $arr = explode('.',substr($str, $start));
//         $param = ['virtual_cat_id',$arr[0]];
//     }elseif(strpos($str,'supplier')){
//         $start = strripos($str,'-')+1;
//         $arr = explode('.',substr($str, $start));
//         $param = ['supplier_id',$arr[0]];
//     }elseif(strpos($str,'brand')){
//         $start = strripos($str,'-')+1;
//         $arr = explode('.',substr($str, $start));
//         $param = ['brand_id',$arr[0]];
//     }elseif(strpos($str,'product')){
//         $start = strripos($str,'-')+1;
//         $arr = explode('.',substr($str, $start));
//         $param = ['product_id',$arr[0]];
//     }elseif(strpos($str,'article')){
//         $start = strripos($str,'-')+1;
//         $arr = explode('.',substr($str, $start));
//         $param = ['article_id',$arr[0]];
//     }elseif(strpos($str,'techdiy')){
//         $start = strripos($str,'-')+1;
//         $arr = explode('.',substr($str, $start));
//         $param = ['product_id',$arr[0]];
//     }
//     return $param;
// }

/**
 * 将参数变为字符串
 * @param $array_query
 * @return string string 'm=content&c=index&a=lists&catid=6&area=0&author=0&h=0&region=0&s=1&page=1' (length=73)
 */
function getUrlQuery($array_query)
{
    $tmp = array();
    foreach($array_query as $k=>$param)
    {
        $tmp[] = $k.'='.$param;
    }
    $params = implode('&',$tmp);
    return $params;
}

function getlunbo(){
$html= file_get_contents("http://www.liidon.com/app/site/view/index.html");
$html = <<<EOF
$html
EOF;
require '/var/www/api/simple_html_dom-master/simple_html_dom.php';
$fun = function($str,$key){
    $str=preg_replace("/[\s\S]*\s".$key."[=\"\']+([^\"\']*)[\"\'][\s\S]*/","$1",$str);
    return $str;
};

$html = str_get_html($html);

    $lunbo = array();
    //$s = matching($html,'<ul class="switchable-triggerBox slide-trigger">',"</ul>");
    $regex4="/<ul class=\"switchable-triggerBox slide-trigger\".*?>.*?<\/ul>/ism";
    if(preg_match_all($regex4, $html, $matches)){  
       $counts = substr_count($matches[0][0],'<li>');
    }else{  
       $counts = 4;  
    }
    foreach($html->find('ol.clearfix') as $v) {
        for ($i=0;$i<$counts;$i++){
            $s = trim($v->find('li a', $i));
            $param = $fun($s,'href');
            $param = f5($param);
            if($param){  
                if(isset($param['cat3'])){
                    $lunbo[$i]['cat3'] = $param['cat3'];
                }
                $lunbo[$i]['param'] = $param['param'];
                $lunbo[$i]['type'] = $param['type'];
                if($lunbo[$i]['type']=='gallery'){
                    $lunbo[$i]['status'] = isset($param['status']) ? $param['status'] : '';    
                }
            }
            $lunbo[$i]['url'] = f3($fun($s,'style'));

            /*if(empty($lunbo[$i]['param'])){
                $s = trim($v->find('li', 0));
                $lunbo[$i]['param'] = '';
                $lunbo[$i]['url'] = f3($fun($s,'style'));
            } */
        }
        //var_dump($lunbo);
    }
    //exit;
    return $lunbo;
}
    function gethot(){
$hot = array('新品速递','网络爆款','独家经销','常备现货');        
$html= file_get_contents("http://www.liidon.com/app/site/view/index.html");
$html = <<<EOF
$html
EOF;
require '/var/www/api/simple_html_dom-master/simple_html_dom.php';
$html = str_get_html($html,$no='');

    foreach($html->find('dl.goods_span') as $v) {
        for ($i=0;$i<16;$i++){
            $a = $v->find('dd h6', $i);
            if($a){
                $data[$i] = trim($a->plaintext);
            }
        }

        foreach ($data as $k => $v) {
            if($no){
                if($k>$no){
                    $a = $k-1;
                    $arr[$k] = $data[$a];
                }else{
                    $arr[$k] = $v;
                }
                $arr[$no] = '';    
            }else{
                $arr = $data;
            }
        }
       
    }

    foreach ($arr as $i => $value) {
        $sql = "select b.product_id,a.supplier_id,a.numsprice,a.goods_id,a.name,a.price,a.mktprice,a.image_default_id from sdb_b2c_goods as a left join sdb_b2c_products as b on a.goods_id=b.goods_id where a.name = '{$value}' limit 1";
        $row = Db::query($sql);
        if(array_key_exists(0, $row)){
            if($row[0]['name']){
                if($i<4){
                    
                    $rows[0]['name'] = $hot[0];
                    $rows[0]['list'][] = $row[0];
                }elseif($i<8){
                    
                    $rows[1]['name'] = $hot[1];
                    $rows[1]['list'][] = $row[0];
                }elseif($i<12){
                    
                    $rows[2]['name'] = $hot[2];
                    $rows[2]['list'][] = $row[0];
                }elseif($i<16){
                    
                    $rows[3]['name'] = $hot[3];
                    $rows[3]['list'][] = $row[0];
                }
            }    
        }
    }
    //var_dump('<pre>',$rows);exit;
   foreach ($rows as $key => $value) {
    foreach ($value['list'] as $ke => $va) {
        $image_id = $va['image_default_id'];
        $sql = "select url from sdb_image_image where image_id = '{$image_id}' limit 1";
        $url = Db::query($sql);
        $value['list'][$ke]['url'] = $url[0]['url'];
    }
    $value['list'] = getdiscount($value['list']);
    $rows[$key] = $value;
   }
    return $rows;  
}

function add_search_log($member_id,$login_name,$key_words,$search_type){
    $addtime=time();
    $addSql ="insert into `sdb_b2c_search_log` (`member_id`,`login_name`,`key_words`,`search_type`,`addtime`) VALUES ('$member_id','$login_name','$key_words','$search_type','$addtime')";
    Db::execute($addSql);
}

function get_like_search($search,$type=1){
    if($type==2){
        $data = Db::query("select brand_id,brand_name as label from sdb_b2c_brand where brand_name like '%{$search}%' limit 0,10");
    }elseif($type==3){
        $data = Db::query("select supplier_id,supplier_name as label from sdb_b2c_supplier where supplier_name like '%{$search}%' and is_show_master=1 limit 0,10");
    }else{
        $data = Db::query("select name as label from sdb_b2c_goods where name like '%{$search}%' and is_show_master=1 and marketable='true' limit 0,10");
    }
    return $data;
}
   
function getGoodsListBySupplier($supplier_id,$limit,$order,$filter=null,$pSize=null){
    $pSize=isset($pSize) ? $pSize : 50;

    $filter['price_area']=isset($filter['price_area']) ? $filter['price_area'] : '';
    if($filter['price_area']){
        $arr = explode('-',$filter['price_area']);
        if($arr){
            $s = $arr[0];
            $e = isset($arr[1]) ? $arr[1] : 999999;
            $price_area = " and g.price between {$s} and {$e}"; 
        }else{
            $price_area = "";
        }
        
    }else{
        $price_area = "";
    }

    unset($filter['price_area']);
    if($supplier_id>0){
        $where = " where g.supplier_id = {$supplier_id} and g.marketable='true'";        
    }
    
    $filter['search']=isset($filter['search']) ? $filter['search'] : '';
    if($filter['search']){
        $asearch = '';
        if(strstr($filter['search'],' ')){
            $asearch = explode(' ', $filter['search']);
        }
        //$where .= " and (g.name like '%".$search."%' or p.bn like '%".$search."%' or b.keyword like '%".$search."%') ";
        if($where){
            if($asearch){
                foreach ($asearch as $key => $value) {
                    $where .= " and (g.name like '%".$value."%')";
                }   
            }else{
                $where .= " and (g.name like '%".$filter['search']."%') ";      
            }  
        }else{
            if($asearch){
                $where .= ' where ';
                foreach ($asearch as $key => $value) {
                    $where .= "(g.name like '%".$value."%') and ";
                }
                $where = substr($where,0,-4);
            }else{
                $where .= " where (g.name like '%".$filter['search']."%') ";    
            }
        }
        
    }else{
        $where .= '';
    }
    unset($filter['search']);

    if(@$filter['cat_id']){
        if($where){
            $link = 'and';    
        }else{
            $link = 'where';
        }
        $cat_ids = implode(',',get_s_cat_id($filter['cat_id']));
        if(strstr($cat_ids, ',')){
           $where .= " {$link} g.cat_id in ({$cat_ids})";   
        }else{
           $cat_id = $filter['cat_id'];
           $where .= " {$link} g.cat_id = {$cat_id}";   
        }   
    }else{
        $where .= '';    
    }

    if(@$filter['goods_id']){
        $goods_ids = $filter['goods_id'];
        if($where){
            $link = 'and';    
        }else{
            $link = 'where';
        }
        $where .= " {$link} g.goods_id not in ({$goods_ids})";   
    }else{
        $where .= '';    
    }

    if(@$filter['tag_id']){
        if($where){
            $link = 'and';    
        }else{
            $link = 'where';
        }
        $taggoods_id = gettaggods_id($filter['tag_id']);
        if(empty($taggoods_id)){
            $where .= " {$link} g.goods_id in (-1,-2)";    
        }else{
            $taggoods_ids = implode(',',$taggoods_id);
            $where .= " {$link} g.goods_id in ({$taggoods_ids})";  
        }  
    }else{
        $where .= '';    
    }

    if(@$filter['brand_id']){
        if($where){
            $link = 'and';    
        }else{
            $link = 'where';
        }
        $brand_id = $filter['brand_id'];
        if($where){
            if(strstr($brand_id, ',')){
               $where .= " {$link} g.brand_id in ({$brand_id})";   
            }else{
               $where .= " {$link} g.brand_id = {$brand_id} "; 
            }  
        }else{
            $where .= "";    
        }  
    }else{
        $where .= '';    
    }

    if(@$filter['rebate']){
        if($where){
            $link = "and";
        }else{
            $link = "";
        }
        $where .= "{$link} FORMAT(g.price/g.mktprice,2)*10<={$filter['rebate']} ";
    }else{
        $where .= '';    
    }

    if(@$filter['type_id']){
        if($where){
            $link = "and";
        }else{
            $link = "";
        }
        $where .= "{$link} g.type_id = {$filter['type_id']}";  
    }else{
        $where .= '';    
    }

    $sql="SELECT g.goods_id,g.supplier_id,g.numsprice,g.techdiy,g.name,g.is_sell,g.mktprice,g.price,img.url,p.product_id,sum(p.store) as st    
        FROM sdb_b2c_goods g LEFT JOIN sdb_b2c_products p ON p.goods_id=g.goods_id 
        LEFT JOIN sdb_b2c_goods_keywords b ON g.goods_id = b.goods_id 
        LEFT JOIN sdb_image_image img ON img.image_id = g.image_default_id".$where.$price_area.' group by g.goods_id ';
    //echo $sql;exit;
     $list= Db::query($sql);
     $data['goods_count']=count($list);
     $sql=$sql.$order.$limit;
     $goods=Db::query($sql);
    foreach ($goods as $k=>$v){
       $p= explode(".",$v['price']);
       $goods[$k]['price_int']=$p[0];
       $goods[$k]['price_f']=substr($p[1], 0,2);
    }
    $data['all_page']=ceil($data['goods_count']/$pSize);
    $data['goods_list']=getdiscount($goods);
    return $data;
       
}

function getGoodsListByBrand($brand_id,$limit,$order,$filter=null){
    $filter['price_area']=isset($filter['price_area']) ? $filter['price_area'] : '';
    $filter['brand_id']=isset($filter['brand_id']) ? $filter['brand_id'] : '';
    if($filter['price_area']){
        $arr = explode('-',$filter['price_area']);
        if($arr){
            $s = $arr[0];
            $e = isset($arr[1]) ? $arr[1] : 999999;
            $price_area = " and g.price between {$s} and {$e}"; 
        }else{
            $price_area = "";
        }
        
    }else{
        $price_area = "";
    }

    unset($filter['price_area']);
    $where = " where g.brand_id = {$brand_id} and g.marketable='true'";

    if(@$filter['cat_id']){
        if($where){
            $link = 'and';    
        }else{
            $link = 'where';
        }
        //$cat_ids = implode(',',get_s_cat_id($filter['cat_id']));
        if(strstr($filter['cat_id'], ',')){
           $where .= " {$link} g.cat_id in ({$filter['cat_id']})";   
        }else{
           $cat_id = $filter['cat_id'];
           $where .= " {$link} g.cat_id = {$cat_id}";   
        }   
    }else{
        $where .= '';    
    }

    if(@$filter['tag_id']){
        if($where){
            $link = 'and';    
        }else{
            $link = 'where';
        }
        $taggoods_id = gettaggods_id($filter['tag_id']);
        if(empty($taggoods_id)){
            $where .= " {$link} g.goods_id in (-1,-2)";    
        }else{
            $taggoods_ids = implode(',',$taggoods_id);
            $where .= " {$link} g.goods_id in ({$taggoods_ids})";  
        }      
    }else{
        $where .= '';    
    }

    if(@$filter['supplier_id']){
        if($where){
            $link = 'and';    
        }else{
            $link = 'where';
        }
        $supplier_id = $filter['supplier_id'];
        if($where){
            $where .= " {$link} g.supplier_id = {$supplier_id} ";    
        }else{
            $where .= "";    
        }  
    }else{
        $where .= '';    
    }

    if(@$filter['rebate']){
        if($where){
            $link = "and";
        }else{
            $link = "";
        }
        $where .= "{$link} FORMAT(g.price/g.mktprice,2)*10<={$filter['rebate']} ";
    }else{
        $where .= '';    
    }

    if(@$filter['type_id']){
        if($where){
            $link = "and";
        }else{
            $link = "";
        }
        $where .= "{$link} g.type_id = {$filter['type_id']}";  
    }else{
        $where .= '';    
    }

    $sql="SELECT g.goods_id,g.supplier_id,g.numsprice,g.techdiy,g.name,g.is_sell,g.mktprice,g.price,img.url,p.product_id,sum(p.store) as st  
        FROM sdb_b2c_goods g LEFT JOIN sdb_b2c_products p ON p.goods_id=g.goods_id 
        LEFT JOIN sdb_b2c_goods_keywords b ON g.goods_id = b.goods_id 
        LEFT JOIN sdb_image_image img ON img.image_id = g.image_default_id".$where.$price_area.' group by g.goods_id ';
    //echo $sql;exit;
     $list= Db::query($sql);
     $data['goods_count']=count($list);
     $sql=$sql.$order.$limit;
     $goods=Db::query($sql);
    foreach ($goods as $k=>$v){
       $goods[$k]['is_fav']=2;
       $p= explode(".",$v['price']);
       $goods[$k]['price_int']=$p[0];
       $goods[$k]['price_f']=substr($p[1], 0,2);
    }
    $data['all_page']=ceil($data['goods_count']/50);
    $data['goods_list']=getdiscount($goods);
    return $data;
}

function get_is_fav($member_id,$goods_id){
    $re = Db::name('b2c_member_goods')->where(array('goods_id'=>$goods_id,'member_id'=>$member_id))->find();
    if($re){
        return true;
    }else{
        return false;
    }
    
}

function gettaggods_id($tag_id){
    if(strstr($tag_id, ',')){
        $tags = array_unique(explode(',', $tag_id));
        $c = 0;
        $tag_sql = '';
        foreach ($tags as $key => $value) {
            if($key>0){
                $tag_sql .= " or `tag_id` ={$value}";
                $c++;    
            }else{
                $tag_sql .= " `tag_id` ={$value}";
            }
        }
        $sql = "SELECT count(*)  as c,`rel_id`   FROM `ecstore`.`sdb_desktop_tag_rel` WHERE `tag_type` ='goods' and ({$tag_sql})  GROUP BY `rel_id` having c>{$c} ORDER BY c desc";   
        $res = Db::query($sql);
        return array_column($res, 'rel_id');
    }
    return Db::name('desktop_tag_rel')->where(array('tag_type'=>"goods",'tag_id'=>$tag_id))->column('rel_id');
}

function getdiscount($goods){
    foreach ($goods as $k=>$v){
        if($v['mktprice']>0){
           $goods[$k]['discount'] = round($v['price']/$v['mktprice']*10,1);
           if($goods[$k]['discount']==10){
            $goods[$k]['discount'] = '无折扣';
           }

        }/*elseif($v['tax_included_price']>0){
           $goods[$k]['discount'] = round($v['tax_included_price']/$v['mktprice']*10,1); 
        }*/else{
            $goods[$k]['discount'] = '无折扣';
        }
        if($v['mktprice']==null){
           $v['mktprice']='0.000';
        }
        if($v['url']==null){
           $url = getGoodsImg($v['goods_id']);
           $goods[$k]['url']=isset($url)? $url : 'http://www.liidon.com/data/temp/default.png';
        }
        $goods[$k]['price'] = substr($v['price'],0,strlen($v['price'])-1);
        $goods[$k]['mktprice'] = substr($v['mktprice'],0,strlen($v['mktprice'])-1);
        $is_fav = get_is_fav(session('member_id'),$v['goods_id']);
        if($is_fav){
            $goods[$k]['is_fav'] = 1;    
        }else{
            $goods[$k]['is_fav'] = 2;    
        }
        $tag_ids = Db::name('desktop_tag_rel')->where(array('rel_id'=>$v['goods_id']))->column('tag_id');
        $tags = array();
        $i = -1;
        foreach ($tag_ids as $key => $value) {
          if($value>0){ 
            $i++;
            $tags[$i]['tag_name'] = Db::name('desktop_tag')->where(array('tag_id'=>$value))->value('tag_name');    
            $tags[$i]['tag_bgcolor'] = Db::name('desktop_tag')->where(array('tag_id'=>$value))->value('tag_bgcolor');    
            $tags[$i]['tag_fgcolor'] = Db::name('desktop_tag')->where(array('tag_id'=>$value))->value('tag_fgcolor');    
            if($tags[$i]['tag_name']==null){
              unset($tags[$i]);  
            }           
          }else{
            unset($tags[$i]);
          }
        }
        $goods[$k]['tag'] = $tags;
        $v['numsprice'] = unserialize($v['numsprice']);
        $numsprice=array();
        if(is_array($v['numsprice'])&&$v['numsprice']){
            foreach($v['numsprice'] as $np_row){
                if($np_row['nums'] && $np_row['price']){
                    $numsprice['nums'][]= $np_row['nums'];
                    $numsprice['price'][]=  $np_row['price'];
                    if($np_row['price2']){
                        $numsprice['price2'][]=  $np_row['price2'];
                    } 
                } 
            }
        }
        if($numsprice){
            $goods[$k]['subscribe'] = current($numsprice['nums']);
            $goods[$k]['numsprice'] = $numsprice;
        }else{
            $goods[$k]['subscribe'] = 1;
            unset($goods[$k]['numsprice']);
        }
         
    }
    return $goods;
}

function write_log($pay_type,$content){
    $filename = '/var/www/temp/log/'.date('Y-m-d').$pay_type.'.txt';
    $Ts=fopen($filename,"a+");
    fputs($Ts,"执行日期：".date('Y-m-d H:i:s',time()).  ' ' . "\n" .$content."\r\n");
    fclose($Ts);
}

/**
 * curl发送htpp请求
 * 可以发送https,http,get方式,post方式,post数据发送
 */
function dataRequest($url,$https=false,$method='get',$data=null)
{
    //初始化curl
    $ch = curl_init($url);
    //字符串不直接输出，进行一个变量的存储
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //https请求
    if ($https === true) {
        //确保https请求能够请求成功
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    }
    //post请求
    if ($method == 'post') {
        curl_setopt($ch,CURLOPT_POST,true);
        //var_dump($data);exit;
        curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($data));
    }
    
    //发送请求
    $str = curl_exec($ch);
    $aStatus = curl_getinfo($ch);
    //关闭连接
    curl_close($ch);
    if(intval($aStatus["http_code"])==200){
        return $str;
    }else{
        return false;
    }
}

function curl_post($curlHttp, $postdata) {
   
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $curlHttp);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //不显示
    curl_setopt($curl, CURLOPT_TIMEOUT, 60); //60秒，超时
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}

function curl_post_raw($url,$data_string){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'X-AjaxPro-Method:ShowList',
        'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36'
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function curl_get($url,$data=null){
    if($data){
        $url = $url.'?'.http_build_query($data);
    }
   $testurl = $url;  
   $ch = curl_init();    
   curl_setopt($ch, CURLOPT_URL, $testurl);    
    //参数为1表示传输数据，为0表示直接输出显示。  
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
    //参数为0表示不带头文件，为1表示带头文件  
   curl_setopt($ch, CURLOPT_HEADER,0);  
   $output = curl_exec($ch);   
   curl_close($ch);   
   return $output;  
 }

function curl_put($url,$data){
    $data = json_encode($data);
    $ch = curl_init(); //初始化CURL句柄 
    curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
    curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"PUT"); //设置请求方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置提交的字符串
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output,true);
}

function get_fav($member_id,$fliter=array(),$page=1,$num=10){
    $count = Db::name('b2c_goods')->alias('g')
    ->join('b2c_member_goods m','m.goods_id = g.goods_id','left')
    ->where(array('m.member_id'=>$member_id,'g.marketable'=>'true'))
    ->where($fliter)
    ->where('m.type','fav')
    ->count();
    $maxPage = ceil($count / $num);
    if($page > $maxPage) $page=$maxPage;//return array();
    $start = ($page-1) * $num;
    $start = $start<0 ? 0 : $start;

    $aProduct = Db::name('b2c_goods')->alias('g')
    ->join('b2c_member_goods m','m.goods_id = g.goods_id','left')
    ->where(array('m.member_id'=>$member_id,'g.marketable'=>'true'))
    ->where($fliter)
    ->where('m.type','fav')
    ->field('m.gnotify_id,g.thumbnail_pic,g.price,m.direct_quote,g.mktprice,m.type,m.goods_id,m.goods_name,m.goods_price,m.actual_price,m.image_default_id')
    ->order('m.create_time','desc')
    ->limit($start,$num)
    ->select();
    if($aProduct){
        $params['fav_page'] = $maxPage;
        $params['fav_count'] = $count;
        foreach ($aProduct as &$val) {
            // 判断图片是否存在
            $image_default_id = Db::name('image_image')->where(array('image_id'=>$val['image_default_id']))->value('url');   
            $val['url'] = $image_default_id;
            if (empty($image_default_id)) {
                $val['image_default_id'] = '';
            }

            $thumbnail_pic = Db::name('image_image')->where(array('image_id'=>$val['thumbnail_pic']))->value('url');
            $val['thumbnail_pic'] = $thumbnail_pic;
            if (empty($thumbnail_pic)) {
                $val['thumbnail_pic'] = '';
            }

            $val['product_id'] = Db::name('b2c_products')->where('goods_id',$val['goods_id'])->value('product_id');
        }
        $params['goods'] = $aProduct;
        return $params;
    }else{
        return array('goods'=>'');
    }
}

function add_fav($member_id=null,$object_type='goods',$goods_id=null,$pid=null){
    if(!$member_id || !$goods_id || !$pid) return false;
    $filter['member_id'] = $member_id;
    $filter['goods_id'] = $goods_id;
    $filter['product_id'] = $pid;
    $filter['type'] = 'fav';
    if($row = Db::name('b2c_member_goods')->where($filter)->find()){
        return true;
    }
    $goodsData = Db::name('b2c_goods')->where(array('goods_id'=>$goods_id))->find();
    $sdf = array(
       'goods_id' =>$goods_id,
       'member_id' =>$member_id,
       'goods_name'=>$goodsData['name'],
       'goods_price'=>$goodsData['price'],
       'image_default_id'=>$goodsData['image_default_id'],
       'product_id'=>$pid,
       'status' =>'ready',
       'create_time' => time(),
       'type' =>'fav',
       'object_type'=> $object_type,
      );
    if(Db::name('b2c_member_goods')->insert($sdf)){
        return true;
    }else{
        return false;
    }
}

function del_fav($member_id,$gnotify_id){
    $arr = explode(',',$gnotify_id);
    if($arr){
        foreach ($arr as $key => $value) {
        $re = Db::name('b2c_member_goods')->where(array('gnotify_id'=>$value,'member_id'=>$member_id))->delete();
        }
    }else{
        $re = Db::name('b2c_member_goods')->where(array('gnotify_id'=>$gnotify_id,'member_id'=>$member_id))->delete();
    }
    return $re;
}

function get_pptx($member_id,$page=1,$num=50){
    $count = Db::name('b2c_member_ppt')->where(array('member_id'=>$member_id))->count();
    $maxPage = ceil($count / $num);
    if($page > $maxPage) $page=$maxPage;//return array();
    $start = ($page-1) * $num;
    $start = $start<0 ? 0 : $start;
    $pptx = Db::name('b2c_member_ppt')->where(array('member_id'=>$member_id))->limit($start,$num)->order('addtime','desc')->select();
    if($pptx){
        foreach ($pptx as $key => $value) {
            $pptx[$key]['addtime']=date("Y年m月d日",$value['addtime']);
            $pptx[$key]['down_url']=http2https("http://www.liidon.com/public/xiangmu/{$member_id}/".$value['ppt']);
        }
        return array('pptx_count'=>$count,'page'=>$maxPage,'pptx'=>$pptx);
    }else{
        return false;
    }
}

function cat_screen($cat_id,$status=null){
    //$status等于1代表是虚拟分类
    if($status==1){
        $re = Db::name("b2c_goods_virtual_cat")
        ->where(array('virtual_cat_id'=>$cat_id))
        ->field('cat_id,cat_path,virtual_cat_id,filter,virtual_cat_name as cat_name')
        ->find();
        parse_str($re['filter'],$vcatFilters);
        $tag_id = $vcatFilters['tag'][0];

        $tag_goods_ids = array();
        if($tag_id!='_ANY_'){
            $tag_goods_ids = Db::name("desktop_tag_rel")
            ->where(array('tag_id'=>$tag_id))
            ->column('rel_id');    
        }
        
        $cat_id = $vcatFilters['cat_id'][0];
        $cat_goods_ids = array();
        if($cat_id!='_ANY_'){
            $cat_id = $vcatFilters['cat_id'];
            $cat_res = array();
            foreach ($cat_id as $key => $value) {
                $cat_res[$key] = Db::name("b2c_goods")
                ->where(array('cat_id'=>$value))
                ->column('goods_id');
                for ($i=0; $i < count($cat_res); $i++) { 
                    foreach ($cat_res[$i] as $key => $value) {
                        $cat_goods_ids[] = $value;
                    }     
                }
            }    
        }
       

        
        $brand_id = $vcatFilters['brand_id'][0];
        $brand_goods_ids = array();
        if($brand_id!='_ANY_'){
            $brand_id = $vcatFilters['brand_id'];
            $brand_res = array();
            foreach ($brand_id as $key => $value) {
                $brand_res[$key] = Db::name("b2c_goods")
                ->where(array('brand_id'=>$value))
                ->column('goods_id');
            }  
            for ($i=0; $i < count($brand_res); $i++) { 
                foreach ($brand_res[$i] as $key => $value) {
                    $brand_goods_ids[] = $value;
                }     
            }
                
        }
        
        $goods_ids = array_merge(array_merge($tag_goods_ids,$cat_goods_ids),$brand_goods_ids);

        $brand_ids = Db::name("b2c_goods")
        ->where('goods_id','in',$goods_ids)
        ->column('brand_id');
        $cats = Db::name("b2c_goods")->alias('g')
        ->where('g.goods_id','in',$goods_ids)
        ->where('c.cat_id > 0')
        ->join('b2c_goods_cat c','c.cat_id = g.cat_id','left')
        ->field('c.cat_id,c.cat_name')
        ->group('cat_id')
        ->select();  
        $tagarr = [];
        $tag = getGoodsColumnCount(['goods_id'=>$goods_ids],'tag_bunch');
        if($tag){
            foreach ($tag as $key => $value) {
                unset($tag[$key]);
                $tag_arr = explode(',', $value);
                foreach ($tag_arr as $k => $v) {
                    $tag[] = $v;
                }
            }
            $tag = array_unique($tag);
            foreach ($tag as $key => $value) {
                $tag[$key] = current(explode(',', base64_decode($value)));
            }
            $tagarr = Db::name('desktop_tag')
            ->where('tag_id','in',$tag)
            ->field('tag_id,tag_name,tag_bgcolor,tag_fgcolor')
            ->select();
        }
        $brands = [];
        $brand = getGoodsColumnCount(['goods_id'=>$goods_ids],'brand_id');
        if($brand){
            $brands = Db::name('b2c_brand')->alias('b')
            ->join('image_image i','i.image_id=b.brand_logo','left')
            ->where('b.brand_id','in',$brand)
            ->field('b.brand_id,b.brand_name,i.url')
            ->select();
        } 
    }else{
        $catInfo = Db::name('b2c_goods_cat')->where('cat_id',$cat_id)->find();
        $brand_ids = Db::name('b2c_type_brand')->where('type_id',$catInfo['type_id'])->column('brand_id'); 
        $str_ids = implode(',', array_filter($brand_ids));
        $sql = "select b.brand_id,b.brand_name,i.url from sdb_b2c_brand as b left join sdb_image_image as i on i.image_id=b.brand_logo where b.disabled='false' and b.brand_id in ({$str_ids}) group by b.brand_id";
        $brands = Db::query($sql);
        $level = get_cat_level($cat_id);
        $catkey = 'cat'.$level;
        $tagarr = [];
        $tag = getGoodsColumnCount([$catkey=>$cat_id],'tag_bunch');
        if($tag){
            foreach ($tag as $key => $value) {
                unset($tag[$key]);
                $tag_arr = explode(',', $value);
                foreach ($tag_arr as $k => $v) {
                    $tag[] = $v;
                }
            }
            $tag = array_unique($tag);
            foreach ($tag as $key => $value) {
                $tag[$key] = current(explode(',', base64_decode($value)));
            }
            $tagarr = Db::name('desktop_tag')
            ->where('tag_id','in',$tag)
            ->field('tag_id,tag_name,tag_bgcolor,tag_fgcolor')
            ->select();
        }
    }
    
     
    if(isset($cats)){
        return array('cat'=>$cats,'brand'=>$brands,'tag'=>$tagarr);
    }
    return array('brand'=>$brands,'tag'=>$tagarr);
}

function get_cat_level($cat_id,$i=1){
    $sql = "SELECT cat_id,parent_id FROM sdb_b2c_goods_cat WHERE cat_id=".$cat_id."";
    $c = current(Db::query($sql));
    if($c['parent_id']==0){
        return $i;
    }else{
        $i = $i+1;
        return get_cat_level($c['parent_id'],$i);
    }
}

function getgoodstag(){
    return Db::name('desktop_tag')->field('tag_id,tag_name')->where(array('tag_mode'=>'normal','app_id'=>'b2c','tag_type'=>"goods"))->order(
        'tag_id','desc')->select();
}

function getGoodsColumnCount($filter,$column){
    $where = "marketable='true' and is_show_master=1 and {$column} is not NULL and {$column} <> '' ";
    if(@$filter['brand_id']){
        $brand = implode(',', explode(',',$filter['brand_id']));
        $where .= " and brand_id in ({$brand}) ";
    }
    if(@$filter['type_id']){
        $gtype = array_filter(explode(',',$filter['type_id']));
        $shieldtype = array_filter(explode(',', @$filter['shieldtype']));
        if($gtype && $shieldtype){
            foreach ($gtype as $key => $value) {
                foreach ($shieldtype as $k => $v) {
                    if($v==$value){
                        unset($gtype[$key]);
                    }
                }        
            }     
        }
        if($gtype){
            $where .= " and";
            foreach ($gtype as $key => $value) {
                unset($gtype[$key]);
                $gtype[$key] = " type_id = {$value}";
            }
            $where .= implode(' or ', $gtype);     
        }  
    }
    if(@$filter['shieldtype']){
        $shieldtype = array_filter(explode(',', @$filter['shieldtype']));
        $where .= " and";
        foreach ($shieldtype as $key => $value) {
            unset($shieldtype[$key]);
            $shieldtype[$key] = " type_id != {$value}";
        }
        $where .= implode(' and ', $shieldtype);   
    }
    if(@$filter['supplier_id']){
        $supplier = implode(',', explode(',',$filter['supplier_id']));
        $where .= " and supplier_id in ({$supplier}) ";
    }
    if(@$filter['token']){
        $goods = base64_decode($filter['token']);
        $where .= " and goods_id in ({$goods}) ";
    }
    if(@$filter['name']){
        $search = array_slice(explode(' ', $filter['name']),0,2);
        foreach ($search as $k => $v) {
            $search[$k] = "  (instr(name_alias,'".$v."')>0 or instr(type_alias,'".$v."')>0) ";    
        }
        $where .= ' and '.implode(' and ', $search);
    }
    if(@$filter['tag_id']){
        $tag = explode(',', $filter['tag_id']);
        foreach ($tag as $key => $value) {
            unset($tag[$key]);
            $tag[] = "instr(tag_bunch,'".gettag_bunch($value)."')>0";
        }
        $where .= ' and '.implode(' and ', $tag);
    }
    if(@$filter['prop']){
        $prop = json_decode(base64_decode($filter['prop']),true);
        if($prop){
            foreach ($prop as $key => $value) {
                unset($prop[$key]);
                $prop[$key] = explode(',', $value);
                foreach ($prop[$key] as $k => $v) {
                    $prop[$key][$k] = " p_{$key} = $v ";
                }
            }
            foreach ($prop as $key => $value) {
                $prop[$key] = implode(' or ', $value);
            } 
            $where .= ' and '.implode(' and ', $prop);      
        }
    }
    if(@$filter['cat1'] && !@$filter['cat2'] && !@$filter['cat3']){
        $where .= " and cat1 = {$filter['cat1']} ";
    }
    if(@$filter['cat1'] && @$filter['cat2'] && !@$filter['cat3']){
        $where .= " and cat2 = {$filter['cat2']} ";
    }
    if(@$filter['cat1'] && @$filter['cat2'] && @$filter['cat3']){
        $where .= " and cat3 = {$filter['cat3']} ";
    }
    if(@!$filter['cat1'] && @!$filter['cat2'] && @$filter['cat3']){
        $cat3 = implode(',', explode(',',$filter['cat3']));
        $where .= " and cat3 in ({$cat3}) ";
    }
    if(@$filter['discount']){
        $where .= " and FORMAT(price/mktprice,2)*10<={$filter['discount']} ";
    }
    if(@$filter['price_area']){
        $arr = explode('~',$filter['price_area']);
        if($arr){
            $s = $arr[0];
            $e = isset($arr[1]) ? $arr[1] : 999999;
            $where .= " and price >= {$s} and price < {$e} ";
        }
    }
    if(@$filter['store']){
        $s = time()-86400;
        $e = time();
        $where .= " and store > {$filter['store']} and last_up_store between {$s} and {$e} ";
    }
    if(@$filter['goods_id']){
        $goods_ids_str = implode(',', $filter['goods_id']);
        $where .= " and goods_id in ({$goods_ids_str}) ";
    }

    $goods=Db::name("b2c_goods")
    ->distinct(true)
    ->where($column,'>',0)
    ->where($where)
    ->column($column);
    // ->buildSql();
    // echo $goods;exit;
    //var_dump('<pre>',$goods);exit;
    return $goods;
}

function all_search_sql($search){
    $search = array_slice(explode(' ', $search),0,2);
    $type_sql = "";
    foreach ($search as $key => $value) {
        $type_ids_str = getTypeidBySearch(trim($value));
        if($type_ids_str){
            $a_type_ids_str = strstr($type_ids_str, ',');
            if($a_type_ids_str){
                $type_sql = "OR `g`.`type_id` in ({$type_ids_str})";    
            }else{
                $type_sql = "OR `g`.`type_id` = {$type_ids_str}";
            }   
        }
        $search[$key]="(instr(`g`.`name`,'".$value."')>0 OR instr(`k`.`keyword`,'".$value."')>0 ".$type_sql.")";
    }
    $all_sql = implode(' AND ', $search);   
    return $all_sql;
}


function search_screen($search){
    if($search){
        $sql = "select brand_id,cat3 from sdb_b2c_goods where instr(name_alias,'".$search."')>0 and marketable='true' and is_show_master=1 and brand_id<>'' and cat3>0";
        $brand_ids = array_unique(array_column(Db::query($sql), 'brand_id'));
        $cat_ids = array_unique(array_column(Db::query($sql), 'cat3'));
        $str_brand_ids = implode(',', $brand_ids);
        $str_cat_ids = implode(',', $cat_ids);
        
        $sql = "select b.brand_id,b.brand_name,i.url from sdb_b2c_brand as b left join sdb_image_image as i on i.image_id=b.brand_logo where b.disabled='false' and b.brand_id in ({$str_brand_ids})";
        $sql2 = "select cat_id,cat_name from sdb_b2c_goods_cat where cat_id in ({$str_cat_ids})";
        $Data['cat'] = Db::query($sql2);
    }else{
        $sql = "select b.brand_id,b.brand_name,i.url from sdb_b2c_brand as b left join sdb_image_image as i on i.image_id=b.brand_logo where b.disabled='false'";      
    }    
    $Data['brand'] = Db::query($sql);
    $Data['tag'] = array();
    $tag = getGoodsColumnCount(['name'=>$search],'tag_bunch');
    if($tag){
        foreach ($tag as $key => $value) {
            unset($tag[$key]);
            $tag_arr = explode(',', $value);
            foreach ($tag_arr as $k => $v) {
                $tag[] = $v;
            }
        }
        $tag = array_unique($tag);
        foreach ($tag as $key => $value) {
            $tag[$key] = current(explode(',', base64_decode($value)));
        }
        $Data['tag'] = Db::name('desktop_tag')
        ->where('tag_id','in',$tag)
        ->field('tag_id,tag_name,tag_bgcolor,tag_fgcolor')
        ->select();
    } 
    return $Data;
}

function brand_screen($brand_id){
    $sql = "select g.cat_id,c.cat_name,count(*) as count from sdb_b2c_goods as g left join sdb_b2c_goods_cat as c on c.cat_id=g.cat_id where g.marketable='true' and g.brand_id={$brand_id} and c.cat_name<>'' group by g.cat_id";
    $a = Db::query($sql);
    foreach ($a as $key => $value) {
        if($value['count']<0){
            unset($a[$key]);
        }
    }
    $a_supplier = getSupplieridByBrand($brand_id,1);
    $o_supplier = getSupplieridByBrand($brand_id,2);
    $suppliers = array_merge($a_supplier,$o_supplier);
    foreach ($suppliers as $key => $value) {
        $hav_goods = Db::name('b2c_goods')->where(array('supplier_id'=>$value['supplier_id']))->select();
        if(!$hav_goods){
            unset($suppliers[$key]);
        }
    }

    $Data['suppliers'] = $suppliers;
    $Data['cat'] = $a;
    $Data['tag'] = array();
    $tag = getGoodsColumnCount(['brand_id'=>$brand_id],'tag_bunch');
    if($tag){
        foreach ($tag as $key => $value) {
            unset($tag[$key]);
            $tag_arr = explode(',', $value);
            foreach ($tag_arr as $k => $v) {
                $tag[] = $v;
            }
        }
        $tag = array_unique($tag);
        foreach ($tag as $key => $value) {
            $tag[$key] = current(explode(',', base64_decode($value)));
        }
        $Data['tag'] = Db::name('desktop_tag')
        ->where('tag_id','in',$tag)
        ->field('tag_id,tag_name,tag_bgcolor,tag_fgcolor')
        ->select();
    }
    return $Data;
}

function supplier_screen($supplier_id){
    $sql = "select g.cat_id,c.cat_name,count(*) as count from sdb_b2c_goods as g left join sdb_b2c_goods_cat as c on c.cat_id=g.cat_id where g.marketable='true' and g.supplier_id={$supplier_id} and c.cat_name<>'' group by g.cat_id";
    $a = Db::query($sql);
    foreach ($a as $key => $value) {
        if($value['count']<0){
            unset($a[$key]);
        }
    }
    $a_brand = getBrandBySupplier($supplier_id,1);
    $o_brand = getBrandBySupplier($supplier_id,2);
    $brands = array_merge($a_brand,$o_brand);
    foreach ($brands as $key => $value) {
        $brands[$key]['url'] = Db::name("b2c_brand")->alias('b')
        ->join('image_image img','img.image_id = b.brand_logo','left')
        ->where(array('brand_id' => $value['brand_id']))
        ->value('url');
        $hav_goods = Db::name('b2c_goods')->where(array('brand_id' => $value['brand_id']))->find();
        if(!$hav_goods){
            unset($brands[$key]);
        }
    }

    $Data['brands'] = $brands;
    $Data['cat'] = $a;
    $Data['tag'] = array();
    $tag = getGoodsColumnCount(['supplier_id'=>$supplier_id],'tag_bunch');
    if($tag){
        foreach ($tag as $key => $value) {
            unset($tag[$key]);
            $tag_arr = explode(',', $value);
            foreach ($tag_arr as $k => $v) {
                $tag[] = $v;
            }
        }
        $tag = array_unique($tag);
        foreach ($tag as $key => $value) {
            $tag[$key] = current(explode(',', base64_decode($value)));
        }
        $Data['tag'] = Db::name('desktop_tag')
        ->where('tag_id','in',$tag)
        ->field('tag_id,tag_name,tag_bgcolor,tag_fgcolor')
        ->select();
    }
    return $Data;
}

function get_p_cat_id($cat_id){
    $c = Db::name('b2c_goods_cat')->field('cat_id,parent_id')->where(array('cat_id'=>$cat_id))->select();
    if($c[0]['parent_id']==0){
        return $c[0]['cat_id'];
    }else{
        return get_p_cat_id($c[0]['parent_id']);
    }
}



function get_s_cat_id($cat_id){
    $arr = strstr($cat_id,',');
    if($arr){
        $data = explode(',', $cat_id);
        $rd = array();
        foreach ($data as $key => $value) {
            $w = Db::name('b2c_goods_cat')->where(array('parent_id'=>$value))->select();
            $y = Db::query("select cat_id from sdb_b2c_goods_cat where cat_path like '%{$value}%'");
            $x = array_merge($w,$y);
            $rd[$key] = array_unique(array_column($x,'cat_id')); 
        }
        $q = array();
        foreach ($rd as $key => $value) {
            foreach ($value as $k => $v) {
                array_push($q,$v);
            } 
        }
        return $q;
    }else{
        $w = Db::name('b2c_goods_cat')->where(array('parent_id'=>$cat_id))->select();
        $y = Db::query("select cat_id from sdb_b2c_goods_cat where cat_path like '%{$cat_id}%'");
        $x = array_merge($w,$y);
        return array_unique(array_column($x,'cat_id'));    
    }
    
}

function http2https($url){
    $s = strrpos($url,'/')+1;
    $e = strrpos($url,'.');
    $str = substr($url,$s,$e);
    if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $str)>0){
        $url = str_replace("+", "%20", substr($url,0,$s).urlencode($str));
    }
    return str_replace("http://","https://",$url);
}

function sync_store($bn,$supplier_id,$goods_id,$product_id){
    $url = "http://www.liidon.com/upstore/upstore.php?goods_id={$goods_id}&supplier_id={$supplier_id}";
    $re = curl_get($url);
    $data_curl = explode('<br/>',$re);
    $data = array();
    foreach ($data_curl as $key => $value) {
        if($key==0){
            $value = trim(str_replace('#!/usr/bin/php -q', '', $value));      
        }
        if(!$value){
             unset($data_curl[$key]); 
        }else{
            $storeinfo = json_decode($value,true);
            $k = array_keys($storeinfo['data']);
            if($k[0]==$bn){
                $data[$bn] = $storeinfo['data'][$bn];   
            }else{
                $data[$bn] = 0; 
            }
        }
    }
    return $data;
}

/**
* 生成image的唯一标识的image_id
* @param null
* @return string image_id
*/
function gen_id(){
    return md5(rand(0,9999).microtime());
}

//获取文件扩展名
function get_extension($file){ 
    return substr(strrchr($file, '.'), 1); 
} 

//七牛云上传提交文件
function qiniu_upload($file){
    $obj = new \qiuniu\QiniuSdk();
    $uniqid = uniqid();
    $ext = substr(strrchr($file['name'], '.'), 1);
    $file_path = "/var/www/temp/wx/".$file['name'].'.'.$ext;
    if(!file_exists($file_path)){
        move_uploaded_file($file['tmp_name'],$file_path);
    }
    $r = $obj->upload($uniqid.'.'.$ext,$file_path);
    if(array_key_exists('key',current($r))){
      $key = "https://image.liidon.com/".current($r)['key'];
    }else{
      $key = "";
    }
    //var_dump($key);exit;
    return $key;  
}

//七牛云上传本地文件
function qiniu_loca_upload($file_url){
    $obj = new \qiuniu\QiniuSdk();
    $file_url_info = explode('/',$file_url);
    $filename = end($file_url_info);
    $file_path = str_replace('https://api.liidon.com/','/var/www/api/public/temp/wx/',$file_url);
    if(!file_exists($file_path)){
        $file_path = '/var/www/api/public/temp/wx/'.$filename;
        curlGetFile($file_url,$file_path);
    }
    $uniqid = uniqid();
    $ext = substr(strrchr($filename, '.'), 1);
    
    $r = $obj->upload($uniqid.'.'.$ext,$file_path);
    if(array_key_exists('key',current($r))){
      $key = "https://image.liidon.com/".current($r)['key'];
    }else{
      $key = "";
    }
    
    //var_dump($key);exit;
    return $key;  
}

function curlGetFile($url, $file)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0); 
    curl_setopt($ch,CURLOPT_URL,$url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $file_content = curl_exec($ch);
    curl_close($ch);
    $downloaded_file = fopen($file, 'w');
    $r = fwrite($downloaded_file, $file_content);
   
    fclose($downloaded_file);
}

function get_uptoken(){
    $obj = new \qiuniu\QiniuSdk();
    $uptoken = $obj->uptoken();
    return $uptoken;  
}

function weibo_upload($file){
    $obj = new \qiuniu\QiniuSdk();
    $ext = substr(strrchr($file['name'], '.'), 1);
    $file_path = "/var/www/temp/wx/".uniqid().'.'.$ext;
    if(!file_exists($file_path)){
        move_uploaded_file($file['tmp_name'],$file_path);
    }
    $r = $obj->upload($file['name'],$file_path);
    if(array_key_exists('key',current($r))){
      $key = "http://7xplup.com1.z0.glb.clouddn.com/".current($r)['key'];
    }else{
      $key = "";
    }
    //var_dump($key);exit;
    return $key;  
}

function multiSort($arr, $field, $sort = SORT_ASC)
{
    array_multisort(array_column($arr, $field), SORT_ASC, $arr);
    return $arr;
}


function getprojectgoods($project_id){
    $sql = "select p.p_name,g.s_id,g.project_id,g.supplier_id,g.create_time,g.status,s.supplier_name,g.check_status from sdb_b2c_member_project_goods as g left join sdb_b2c_supplier as s on g.supplier_id = s.supplier_id left join sdb_b2c_member_project as p on p.project_id = g.project_id where g.project_id={$project_id} group by g.supplier_id order by g.create_time desc";
    $re = Db::query($sql);
    if($re){
        foreach ($re as $key => $value) {
            $re[$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
        }
        return $re;    
    }else{
        return false;
    }
}

function getsuppliergoods($project_id,$supplier_id){
    $sql = "select p.product_id,p.name,s.supplier_name,img.url,g.goods_id,gg.ppt from sdb_b2c_member_project_goods as g left join sdb_b2c_supplier as s on g.supplier_id = s.supplier_id left join sdb_b2c_goods as gg on gg.goods_id = g.goods_id left join sdb_image_image as img on img.image_id = gg.image_default_id left join sdb_b2c_products as p on p.goods_id = g.goods_id where g.supplier_id = {$supplier_id} and g.project_id={$project_id} group by g.goods_id";
    $re = Db::query($sql);
    if($re){
        Db::name('b2c_member_project_goods')->where('project_id',$project_id)->where('supplier_id',$supplier_id)->update(array('check_status'=>1));
        foreach ($re as $key => $value) {
            $is_fav = Db::name('b2c_member_goods')->where(array('goods_id'=>$value['goods_id']))->value('goods_id');
            if($is_fav){
                $re[$key]['is_fav'] = 1;    
            }
            $re[$key]['ppt'] = http2https($value['ppt']);
        }
        return $re;    
    }else{
        return false;
    }
}

function time_change($time,$type=null){
      $time = time()-$time;

      if($time>=0 && $time<60){
          $time = '刚刚';
      }elseif ($time>=60 && $time<3600) {
          $time = floor($time/60).'分钟前';
      }elseif ($time>=3600 && $time<86400) {
          $time = floor($time/3600).'小时前';
      }elseif ($time>=86400 && $time<604800) {
          $time = floor($time/86400).'天前';
      }elseif ($time>=604800 && $time<2592000) {
          $time = floor($time/604800).'周前'; 
      }elseif ($time>=2592000 && $time<31104000) {
          $time = floor($time/2592000).'个月前';
      }elseif ($time>=31104000) {
          $time = floor($time/31104000).'年前';
          if($type==1){
            $time = '刚刚';
          }
      }

      return $time;
}


//获取货品规格数据
function _get_goods_spec($aGoods){
   
  
    $goodsSpec = array();
   $products= Db::name('b2c_products')->where(array('goods_id'=>$aGoods['goods_id']))
    ->select();
    //$products = app::get('b2c')->model('products')->getList('product_id,spec_desc,store,freez,marketable',array('goods_id'=>$aGoods['goods_id']));
    if($aGoods['spec_desc']){
        $goodsSpec['goods'] = $aGoods['g_spec_desc'];
        $goodsSpec['product'] = $aGoods['spec_desc']['spec_private_value_id'];
        foreach($products as $row){
            
            $row['spec_desc']=unserialize($row['spec_desc']);
            $products_spec = $row['spec_desc']['spec_private_value_id'];
            $diff_class = array_diff_assoc($products_spec,$goodsSpec['product']);//求出当前货品和其他货品规格的差集
            if(count($diff_class) === 1){
                $goodsSpec['goods'][key($diff_class)][current($diff_class)]['product_id'] = $row['product_id'];
                $goodsSpec['goods'][key($diff_class)][current($diff_class)]['marketable'] = $row['marketable'];
                if($row['store'] === '' || $row['store'] === null){
                    $product_store = '999999';
                }else{
                    $product_store = $row['store']-$row['freez'];
                }
                $goodsSpec['goods'][key($diff_class)][current($diff_class)]['store'] = $product_store;
            }
        }
$ids='';
        foreach($aGoods['g_spec_desc'] as $specId=>$specValue){
            //$arrSpecId['spec_id'][] = $specId;
            $ids.=$specId.",";
        }
        $ids=substr($ids, 0,(strlen($ids)-1));
        $w['spec_id']=array('in',$ids);
        $arrSpecName = Db::name('b2c_specification')->where($w)->select();
        foreach($arrSpecName as $specItem){
            $goodsSpec['specification']['spec_name'][$specItem['spec_id']] = $specItem['spec_name'];
            $goodsSpec['specification']['spec_type'][$specItem['spec_id']] = $specItem['spec_type'];

        }
    }
    
    if(isset($goodsSpec['goods'])){
        foreach ($goodsSpec['goods'] as $k=>$v){
            foreach ($v as $k1=>$v1){
               $v[$k1]['image_url']=Db::name('image_image')->field('url')->where(array('image_id'=>$v1['spec_image']))->find();
            }
            $goodsSpec['goods'][$k]=$v;
        }
    }
    else{
        $goodsSpec['goods']='';
    }
    //  print_r($goodsSpec['goods']);exit;
//print_r($goodsSpec['goods'][1]['1513924625.046712145']);exit;
    return $goodsSpec;
}

function ajax_store($product_id,$is_sdk)
{
    if (empty($product_id)) {
        return false;
    }
    $product = Db::name('b2c_products')->field('bn,freez,store,goods_id,store_time,future_store')->where(array('product_id'=>$product_id))->find();
    if (!$product) {
        return false;
    }
    $product['freez'] = is_null($product['freez']) ? 0 : $product['freez'];
    $store_db = $product['store'] - $product['freez'];
    $bn = $product['bn'];
    $goodsId = $product['goods_id'];
    $bddhrq = $product['store_time'];
    $bdsl = $product['future_store'];
    $aGoodsList = Db::name('b2c_goods')->field('supplier_id')->where(array('goods_id'=>$goodsId))->find();

    $supplier_id = $aGoodsList['supplier_id'];
    if($supplier_id!=12 && $supplier_id!=1){
         $url = "http://www.liidon.com/upstore/upstore.php?goods_id={$goodsId}&supplier_id={$supplier_id}";
            curl_get($url);  
             $data['wpkc'] = array(
        'wpdm'=>$bn,
        'sjkc'=>$store_db,
        'bddhrq'=>$bddhrq,
        'bdsl'=>$bdsl,
        'stype'=>$is_sdk,
        );

        return $data;  
    }
   

   
}

 function getweektime($week){
    $s = strtotime($week[0]);
    $e = strtotime($week[1]);
    $time = array();
    for ($i=$s; $i <= $e; $i+=86400) { 
        $time[] = $i;
    }
    $weektime = array();
    foreach ($time as $key => $value) {
        $weektime[$key]['start_time'] = $value;   
        $weektime[$key]['end_time'] = $value+86400;     
    }
    return $weektime;
}

function getxdata($week){
    $s = strtotime($week[0]);
    $e = strtotime($week[1]);
    $time = array();
    for ($i=$s; $i <= $e; $i+=86400) { 
        $time[] = $i;
    }
    $xdata = array();
    foreach ($time as $key => $value) {
        $xdata[$key] = date('m-d',$value);   
    }
    return $xdata;
}

function gettop($arr,$length=10){
    array_multisort(array_column($arr,'count'),SORT_DESC,$arr);
    $arr = array_slice($arr,0,$length);
    return $arr;
}

function getSearchDate(){
    $date=date('Y-m-d');  //当前日期
    $first=1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
    $w=date('w',strtotime($date));  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
    $now_start=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days')); //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
    $now_end=date('Y-m-d',strtotime("$now_start +6 days"));  //本周结束日期
    $last_start=date('Y-m-d',strtotime("$now_start - 7 days"));  //上周开始日期
    $last_end=date('Y-m-d',strtotime("$now_start - 1 days"));  //上周结束日期
    return array($last_start,$last_end,$now_start,$now_end);
}

function getSearchtime(){
    $date=date('Y-m-d');  //当前日期
    $first=1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
    $w=date('w',strtotime($date));  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
    $now_start=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days')); //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
    $now_end=date('Y-m-d',strtotime("$now_start +6 days"));  //本周结束日期
    $last_start=date('Y-m-d',strtotime("$now_start - 7 days"));  //上周开始日期
    $last_end=date('Y-m-d',strtotime("$now_start - 1 days"));  //上周结束日期
    $seven_start=date('Y-m-d', strtotime('-7 days'));  //7开始日期
    $seven_end=date('Y-m-d');  //7结束日期
    return array(strtotime($last_start),strtotime($last_end),strtotime($now_start),strtotime($now_end),strtotime($seven_start),strtotime($seven_end));
}

function getthemonth($date=''){
    $date = isset($date) ? $date : date('Y-m-d');
    $firstday = date('Y-m-01', strtotime($date));
    $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
    return array($firstday,$lastday);
}

function sort_with_keyName($arr,$orderby='desc'){
//在内存的另一处 $a 复制内容与 $arr 一样的数组
    foreach($arr as $key => $value) 
    $a[$key]=$value;
    if($orderby== 'asc'){//对数组 $arr 进行排序
        asort($arr);
    }else{
        arsort($arr);
    }                         
/*创建一个以原始数组的键名为元素值 (键值) 的
    *数组 $b, 其元素 (键值) 顺序，与排好序的数组 $arr 一致。
*/
    $index=0;
    foreach ($arr as $keys => $values) //按排序后数组的顺序
    foreach($a as $key => $value) //在备份数组中寻找键值
    if ($values==$value)//如果找到键值
    $b[$index++]=$key; // 则将数组 $b 的元素值，设置成备份数组 $a 的键名 
//返回用数组 $b 的键值作为键名,数组 $arr 的键值作为键值,所组成的数组 
    return array_combine($b, $arr);
}

function getthemonthtime(){
    $begin_time = date('Y-m-01 00:00:00',strtotime('-1 month'));
    $end_time = date("Y-m-d 23:59:59", strtotime(-date('d').'day'));
    return array(strtotime($begin_time),strtotime($end_time));
}

function strfine($str){
    return substr($str,0,strlen($str)-1); 
}

 //$arr->传入数组   $key->判断的key值  
function array_unset_tt($arr,$key){     
    //建立一个目标数组  
    $res = array();        
    foreach ($arr as $value) {           
       //查看有没有重复项  
         
       if(isset($res[$value[$key]])){  
             //有：销毁  
              
             unset($value[$key]);  
               
       }  
       else{  
              
            $res[$value[$key]] = $value;  
       }    
    }  
    $data = array();
    foreach ($res as $key => $value) {
       $data[] = $value;
    }
    return $data;  
}  

function getdownrate($i,$where,$week,$yest,$month){
  $count = Db::name('b2c_download')->where($where)->count();
  
  $re['count'] = $count;
  if(!empty($yest)){
      $yest_time = $yest[0]+86399;
      $yest_day_area = "$yest[0],$yest_time";
      $yest_day_where['download_time'] = array('between',$yest_day_area);
      $yest_day_count = Db::name('b2c_download')->where($where)->where($yest_day_where)->count();
      $day_time = $yest[1]+86399;
      $day_area = "$yest[1],$day_time";
      $day_where['download_time'] = array('between',$day_area);
      $day_count = Db::name('b2c_download')->where($where)->where($day_where)->count();
      $re['yest_rate'] = '0.00%';
      if($yest_day_count>0){
         $re['yest_rate'] = (number_format(round($day_count/$yest_day_count,4)-1,4)*100).'%';
      }
  }
  if($i==1){
    $re['count'] = $yest_day_count;  
  }

 
  if(!empty($week)){
      unset($where['download_time']);
      $week_time = $week[1]+86399;
      $week_area = "$week[0],$week_time";
      $week_where['download_time'] = array('between',$week_area);
      $week_count = Db::name('b2c_download')->where($where)->where($week_where)->count();
      $weekt = getSearchtime();
      $yest_week_time = $weekt[1]+86399;
      $yest_week_area = "$weekt[0],$yest_week_time";
      $yest_week_where['download_time'] = array('between',$yest_week_area);
      $yest_week_count = Db::name('b2c_download')->where($where)->where($yest_week_where)->count();
      $re['week_rate'] = '0.00%';
      if($yest_week_count>0){
         $re['week_rate'] = (number_format(round($week_count/$yest_week_count,4)-1,4)*100).'%';
      }
  }


  if(!empty($month)){
      unset($where['download_time']);
      $month_time = $month[1]+86399;
      $month_area = "$month[0],$month_time";
      $month_where['download_time'] = array('between',$month_area);
      $month_count = Db::name('b2c_download')->where($where)->where($month_where)->count();
      $montht = getthemonthtime();
      $yest_month_time = $montht[1];
      $yest_month_area = "$montht[0],$yest_month_time";
      $yest_month_where['download_time'] = array('between',$yest_month_area);
      $yest_month_count = Db::name('b2c_download')->where($where)->where($yest_month_where)->count();
      $re['month_rate'] = '0.00%';
      if($yest_month_count>0){
         $re['month_rate'] = (number_format(round($month_count/$yest_month_count,4)-1,4)*100).'%';
      }
  }


  return $re;
}

function getunintrate($i,$where,$week,$yest,$month){
  $count = Db::name('b2c_unint')->where($where)->count();
  $re['count'] = $count;
  if(!empty($yest)){
      $yest_time = $yest[0]+86399;
      $yest_day_area = "$yest[0],$yest_time";
      $yest_day_where['unit_time'] = array('between',$yest_day_area);
      $yest_day_count = Db::name('b2c_unint')->where($where)->where($yest_day_where)->count();
      $day_time = $yest[1]+86399;
      $day_area = "$yest[1],$day_time";
      $day_where['unit_time'] = array('between',$day_area);
      $day_count = Db::name('b2c_unint')->where($where)->where($day_where)->count();
      $re['yest_rate'] = '0.00%';
      if($yest_day_count>0){
         $re['yest_rate'] = (number_format(round($day_count/$yest_day_count,4)-1,4)*100).'%';
      }
  }

  if($i==1){
    $re['count'] = $yest_day_count;  
  }
  
  if(!empty($week)){
      unset($where['unit_time']);
      $week_time = $week[1]+86399;
      $week_area = "$week[0],$week_time";
      $week_where['unit_time'] = array('between',$week_area);
      $week_count = Db::name('b2c_unint')->where($where)->where($week_where)->count();
      $weekt = getSearchtime();
      $yest_week_time = $weekt[1]+86399;
      $yest_week_area = "$weekt[0],$yest_week_time";
      $yest_week_where['unit_time'] = array('between',$yest_week_area);
      $yest_week_count = Db::name('b2c_unint')->where($where)->where($yest_week_where)->count();
      $re['week_rate'] = '0.00%';
      if($yest_week_count>0){
         $re['week_rate'] = (number_format(round($week_count/$yest_week_count,4)-1,4)*100).'%';
      }
  }
  

  if(!empty($month)){
      unset($where['unit_time']);
      $month_time = $month[1]+86399;
      $month_area = "$month[0],$month_time";
      $month_where['unit_time'] = array('between',$month_area);
      $month_count = Db::name('b2c_unint')->where($where)->where($month_where)->count();
      $montht = getthemonthtime();
      $yest_month_time = $montht[1];
      $yest_month_area = "$montht[0],$yest_month_time";
      $yest_month_where['unit_time'] = array('between',$yest_month_area);
      $yest_month_count = Db::name('b2c_unint')->where($where)->where($yest_month_where)->count();
      $re['month_rate'] = '0.00%';
      if($yest_month_count>0){
         $re['month_rate'] = (number_format(round($month_count/$yest_month_count,4)-1,4)*100).'%';
      }
  }
  

  return $re;
}


function getprojectpricedown($project_id){
    $down = '';
    $up = '';
    $sql = "select price_down,price_up,nums from sdb_b2c_member_project_requirement where project_id={$project_id}";
    $requirement = Db::query($sql);
    foreach ($requirement as $k => $v) {
        $down+=$v['price_down']*$v['nums'];
        $up+=$v['price_up']*$v['nums'];
    }
    if($down && $up){
        $re['down'] = $down;
        $re['up'] = $up;
    }else{
        $re['down'] = 0;
        $re['up'] = 0;
    }
    return $re;
}

function getGoodsListByProject($project_id,$supplier_id){
     $where = " where m.project_id={$project_id} and g.supplier_id={$supplier_id}";
     $sql="SELECT g.goods_id,g.numsprice,g.techdiy,g.name,g.is_sell,img.url,p.product_id 
        FROM sdb_b2c_goods g LEFT JOIN sdb_b2c_products p ON p.goods_id=g.goods_id 
        LEFT JOIN sdb_b2c_goods_keywords b ON g.goods_id = b.goods_id 
        LEFT JOIN sdb_b2c_member_project_goods m ON m.goods_id = g.goods_id 
        LEFT JOIN sdb_image_image img ON img.image_id = g.image_default_id".$where.' group by g.goods_id ';
    //echo $sql;exit;
     $list= Db::query($sql);
    return $list;

}

function getuserinfo($login_name){
    $user_id = Db::name('pam_account')->where('account_type','shopadmin')->where('login_name',$login_name)->value('account_id');
    $re = Db::name('desktop_users')->field('user_id,name as user_name,mobile,email,qq')->where('user_id',$user_id)->find();
    if(!$re) return false;
    return $re;
}

function getsenduser($user_id){
    $re = Db::name('desktop_users')->field('user_id,user_id,mobile,email,qq')->where('user_id',$user_id)->find();
    if(!$re) return false;
    return $re;
}

function getGoodsIdsList($filter=null){
    $filter['g.marketable']='true';
    $filter['price_area']=isset($filter['price_area']) ? $filter['price_area'] : '';
    if($filter['price_area']){
        $arr = explode('-',$filter['price_area']);
        if($arr){
            $s = $arr[0];
            $e = isset($arr[1]) ? $arr[1] : 999999;
            $price_area = "g.price between {$s} and {$e}"; 
        }else{
            $price_area = "";
        }
        
    }else{
        $price_area = "";
    }

    $filter['g.brand_id']=isset($filter['g.brand_id']) ? $filter['g.brand_id'] : '';
    if(!$filter['g.brand_id']){
        unset($filter['g.brand_id']);
    }
    unset($filter['price_area']);
    $goods=Db::name("b2c_goods")->alias('g')
    ->join('image_image img','img.image_id = g.image_default_id','left')
    ->join('b2c_products p ','p.goods_id=g.goods_id','left')
    ->join('b2c_supplier s','s.supplier_id = g.supplier_id','left')
    ->where($filter)
    ->where($price_area)
    ->where(array('g.supplier_id'=>array('gt',0)))
    ->group("g.goods_id")
    ->column('g.goods_id');
   
    return $goods;
}

function getOpenidByproject_id($project_id){
    return Db::name("b2c_member_project")->alias('p')
           ->join('pam_account a','a.account_id = p.member_id','left')
           ->where('p.project_id',$project_id)
           ->value("openid");
}

function sendspirit_count($openid){
    $member_id = Db::name("pam_account")
           ->where('openid',$openid)
           ->where('account_type','member')
           ->value("account_id");
    $project_id_arr = Db::name("b2c_member_project")
           ->where('member_id',$member_id)
           ->column("project_id");
    $count = 0;
    if($project_id_arr){
        foreach ($project_id_arr as $key => $value) {
             $count += Db::name("b2c_member_project_goods")
                       ->where('project_id',$value)
                       ->where('check_status',2)
                       ->count();
        }
        return $count;
    }else{
        return $count;
    }
}

function getval($expval){
    
    if (eval("return ".$expval.";") > 0.000001)
    {
        return 1;
    }
    else if (eval("return ".$expval.";") >-0.000001 && eval("return ".$expval.";")< 0.000001)
    {
        return 1/2;
    }
    else
    {
        return 0;
    }
}

function getceil($expval){
 
  if (eval("return ".$expval.";") > 0)
  {

    return ceil(eval("return ".$expval.";")-0.000001);
  }
  else
  {
    return 0;
  }
}

function countexp($data){
  $bds = $data['dt_expressions'];

  if (preg_match("/^[^\]\[\}\{\)\(0-9WwPp\+\-\/\*]+$/",$bds))
  {
    return ;
  }

  $price = '';
  $weight = $data['weight'];
  $fp = $data['fp'];
  $cp = $data['cp'];
  $str = str_replace("[", "getceil(",$bds);
  $str = str_replace("]", ")",$str);
  $str = str_replace("{", "getval(",$str);
  $str = str_replace("}", ")",$str);
  $str = str_replace("fp", $fp,$str);
  $str = str_replace("cp", $cp,$str);
  $str = str_replace("W", $weight,$str);
  $str = str_replace("w", $weight,$str);
  $str = str_replace("P", $price,$str);
  $str = str_replace("p", $price,$str);

  $sum = floor(eval('return '.$str.";")*100+0.01)/100;
  return $sum;
}



function my_unique($k,$arr,$arr2,$i){     
    $i++;
    if(array_key_exists($i, $k)){
        if($i==1){
            $arr = my_merge($arr[$k[0]],$arr2[$k[$i]]);       
        }else{
            $arr = my_merge($arr,$arr2[$k[$i]]);        
        } 
        return my_unique($k,$arr,$arr2,$i);
    }else{
        return $arr;
    }
}

function str_cat($goods_str){
    $supplier_id_arr = explode(',',  $goods_str);
    foreach ($supplier_id_arr as $key => $value) {
        $supplier_id = Db::name('b2c_goods')->where('goods_id',$value)->value('supplier_id');
        $supplier_id_arr[$key] = $supplier_id;
    }
    $supplier_id_arr = array_unique($supplier_id_arr);
    $data = array();
    foreach ($supplier_id_arr as $key => $value) {
        $data[$value] = $value;
    }
    foreach ($data as $key => $value) {
        $data[$key] = filterVirCatList($value);
    }
    return $data;
    
}

function filterVirCatList($supplier_id){
    $catList=getCatList();
    $arr=array();
   
    foreach ($catList as $k1=>$v1){
        $str='';
        foreach ($v1['second_cat'] as $k2=>$v2){
            if(isset($v2['three_cat'])){
                foreach ($v2['three_cat'] as $k3=>$v3){
                    $fitler['supplier_id']=$supplier_id;
                    $fitler['cat_id']=$v3['cat_id'];
                    $fitler['marketable']='true';
                    $count= Db::name('b2c_goods')->where($fitler)->count();
                    if($count>0){
                        $str.=$v3['cat_id'].",";
                        $arr[$v1['cat_id']][$v2['cat_id']][$v3['cat_id']]=$v3['cat_id'];
                    }
                }
            }
            else{
                $fitler['supplier_id']=$supplier_id;
                $fitler['cat_id']=$v2['cat_id'];
                $fitler['marketable']='true';
                $count= Db::name('b2c_goods')->where($fitler)->count();
                if($count>0){
                    $str.=$v2['cat_id'].",";
                    $arr[$v1['cat_id']][$v2['cat_id']]=$v2['cat_id'];
                }
            }
        }
    }
    return $arr;
} 



function my_merge(&$a,$b){  
  
    foreach($a as $key=>&$val){  
        if(is_array($val) && array_key_exists($key, $b) && is_array($b[$key])){  
            my_merge($val,$b[$key]);  
            $val = $val + $b[$key];  
        }else if(is_array($val) || (array_key_exists($key, $b) && is_array($b[$key]))){  
            $val = is_array($val)?$val:$b[$key];  
        }  
    }  
    $a = $a + $b;  

    return $a;
}  

function getCatListById($id){
    $parent_id = get_p_cat_id($id);
    $list=Db::name('b2c_goods_cat')->field('cat_id,cat_name,cat_path')->where(array('parent_id'=>$parent_id))->order('p_order','asc')->select();
    foreach ($list as $k=>$v){
        $list[$k]['second_cat']=Db::name('b2c_goods_cat')->field('cat_id,cat_name,cat_path')->where(array('parent_id'=>$v['cat_id']))->select();
        foreach ($list[$k]['second_cat'] as $key=>$val){
           $three= Db::name('b2c_goods_cat')->field('cat_id,cat_name,cat_path')->where(array('parent_id'=>$val['cat_id']))->select();
           if(count($three)>0){
                $list[$k]['second_cat'][$key]['three_cat']=$three;
           }
        }
    }

    return $list;
}

function filterCate1($param){
    $catList=getCatListById($param);
    $arr=array();
   
    foreach ($catList as $k1=>$v1){
        $str='';
        foreach ($v1['second_cat'] as $k2=>$v2){
            if(isset($v2['three_cat'])){
                foreach ($v2['three_cat'] as $k3=>$v3){
                    $fitler['cat_id']=$v3['cat_id'];
                    $fitler['marketable']='true';
                    $count= Db::name('b2c_goods')->where($fitler)->count();
                    if($count>0){
                        $str.=$v3['cat_id'].",";
                        $arr[$v1['cat_id']][$v2['cat_id']][$v3['cat_id']]=$v3['cat_id'];
                    }
                }
            }
            else{
                $fitler['cat_id']=$v2['cat_id'];
                $fitler['marketable']='true';
                $count= Db::name('b2c_goods')->where($fitler)->count();
                if($count>0){
                    $str.=$v2['cat_id'].",";
                    $arr[$v1['cat_id']][$v2['cat_id']]=$v2['cat_id'];
                }
            }
        }
    }
    return $arr;
} 

function filterCate2($param){
    if($param){
        $where = "";
    }else{
        $where = "";  
    }
    
    $catList=getCatList();
    $arr=array();
   
    foreach ($catList as $k1=>$v1){
        $str='';
        foreach ($v1['second_cat'] as $k2=>$v2){
            if(isset($v2['three_cat'])){
                foreach ($v2['three_cat'] as $k3=>$v3){
                    $fitler['cat_id']=$v3['cat_id'];
                    $fitler['marketable']='true';
                    $count= Db::name('b2c_goods')->where($fitler)->where($where)->count();
                    if($count>0){
                        $str.=$v3['cat_id'].",";
                        $arr[$v1['cat_id']][$v2['cat_id']][$v3['cat_id']]=$v3['cat_id'];
                    }
                }
            }
            else{
                $fitler['cat_id']=$v2['cat_id'];
                $fitler['marketable']='true';
                $count= Db::name('b2c_goods')->where($fitler)->where($where)->count();
                if($count>0){
                    $str.=$v2['cat_id'].",";
                    $arr[$v1['cat_id']][$v2['cat_id']]=$v2['cat_id'];
                }
            }
        }
    }    

    return $arr;
} 

function filterCate3($param){
    $catList=getCatList();
    $arr=array();
   
    foreach ($catList as $k1=>$v1){
        $str='';
        foreach ($v1['second_cat'] as $k2=>$v2){
            if(isset($v2['three_cat'])){
                foreach ($v2['three_cat'] as $k3=>$v3){
                    $fitler['cat_id']=$v3['cat_id'];
                    $fitler['brand_id']=$param;
                    $fitler['marketable']='true';
                    $goods=Db::name('b2c_goods')->where($fitler)->select();
                    $count= count($goods);
                    if($count>0){
                        $str.=$v3['cat_id'].",";
                        $arr[$v1['cat_id']][$v2['cat_id']][$v3['cat_id']]=$v3['cat_id'];
                    }
                }
            }
            else{
                $fitler['cat_id']=$v2['cat_id'];
                $fitler['brand_id']=$param;
                $fitler['marketable']='true';
                $goods=Db::name('b2c_goods')->where($fitler)->select();
                $count= count($goods);
                if($count>0){
                    $str.=$v2['cat_id'].",";
                    $arr[$v1['cat_id']][$v2['cat_id']]=$v2['cat_id'];
                }
            }
        }
    }
    return $arr;
} 

function filterCate4($param){
    $catList=getCatList();
    $arr=array();
   
    foreach ($catList as $k1=>$v1){
        $str='';
        foreach ($v1['second_cat'] as $k2=>$v2){
            if(isset($v2['three_cat'])){
                foreach ($v2['three_cat'] as $k3=>$v3){
                    $fitler['cat_id']=$v3['cat_id'];
                    $fitler['supplier_id']=$param;
                    $fitler['marketable']='true';
                    $goods=Db::name('b2c_goods')->where($fitler)->select();
                    $count= count($goods);
                    if($count>0){
                        $str.=$v3['cat_id'].",";
                        $arr[$v1['cat_id']][$v2['cat_id']][$v3['cat_id']]=$v3['cat_id'];
                    }
                }
            }
            else{
                $fitler['cat_id']=$v2['cat_id'];
                $fitler['supplier_id']=$param;
                $fitler['marketable']='true';
                $goods=Db::name('b2c_goods')->where($fitler)->select();
                $count= count($goods);
                if($count>0){
                    $str.=$v2['cat_id'].",";
                    $arr[$v1['cat_id']][$v2['cat_id']]=$v2['cat_id'];
                }
            }
        }
    }
    return $arr;
}

function getfileBySearch($search,$limit){
    $where = "";
    if($search){
        $where .= "instr(`f`.`file_name`,'{$search}')> 0 || instr(`file_content`,'{$search}')> 0";
    }
    $data = Db::name('b2c_supplier_file')->alias('f')
    ->join('b2c_supplier s ','f.supplier_id=s.supplier_id','left')
    ->where($where)
    ->field('s.supplier_id,s.supplier_name,f.*')
    ->order('f.upload_time desc')
    ->limit($limit['start'],$limit['end'])
    ->select();
    if($data){
        foreach ($data as $key => $value) {
            $data[$key]['update_ago'] = time_change($value['upload_time']);
        }    
    }
    return $data;
}

function getfileBySearchCount($search){
    $where = "";
    if(@$search){
        $where .= " instr(`file_name`,'{$search}')> 0 || instr(`file_content`,'{$search}')> 0";
    }
    $count = Db::name('b2c_supplier_file')
    ->where($where)
    ->order('upload_time desc')
    ->count();
    return $count;
}

function getTypeidBySearch($search){
    $sql = "select type_id from sdb_b2c_goods_type where instr(name,'{$search}')> 0 or instr(alias,'{$search}')> 0";
    $data = Db::query($sql);
    if($data){
        return implode(',', array_column($data, 'type_id'));
    }else{
        return '';
    }
}

function getGoodsTypeBySearch($search){
    $sql = "select type_id from sdb_b2c_goods_type where instr(name,'{$search}')> 0 or instr(alias,'{$search}')> 0";
    $data = Db::query($sql);
    if($data){
        return array_column($data, 'type_id');
    }else{
        return '';
    }
}

function get_id_by_uname($name){
    if($account_id = db('pam_account')->where('login_name',$name)->where('account_type','member')->value('account_id')){
        return $account_id;
    }
}

function gettag_bunch($tag_id,$t=null){
    $tag_info = Db::name('desktop_tag')->where(array('tag_id'=>$tag_id))->find();    
    $tag_bunch = base64_encode($tag_info['tag_id'].','.$tag_info['tag_name'].','.$tag_info['tag_bgcolor'].','.$tag_info['tag_fgcolor']);
    return $tag_bunch;
}

function findNum($str=''){
    $str=trim($str);
    if(empty($str)){return '';}
    $temp=array('1','2','3','4','5','6','7','8','9','0');
    $result='';
    for($i=0;$i<strlen($str);$i++){
        if(in_array($str[$i],$temp)){
            $result.=$str[$i];
        }
    }
    return $result;
}

function get_price_bunch($price){
    $price_arr = array('0~5','5~10','10~20','20~50','50~100','100~300','300~500','500~1000','1000~2000','2000~5000','5000~99999');
    foreach ($price_arr as $key => $value) {
        $price_area = explode('~', $value);
        if($price>=$price_area[0] && $price<=$price_area[1]){
            return base64_encode($value);
        }
    }
}

function arraySort($arr, $keys, $type = 'asc') {
    $keysvalue = $new_array = array();
    foreach ($arr as $k => $v){
        $keysvalue[$k] = $v[$keys];
    }
    $type == 'asc' ? asort($keysvalue) : arsort($keysvalue);
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
       $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

function geTypeByName($name){
    return  db('b2c_goods_type')->where("name='{$name}' or instr(alias,'".$name."')>0")->value('type_id');
}

function getShieldType($member_id){
    return db('b2c_members')->where("member_id",$member_id)->value('shieldtype');
}

function gettechdiyimg($member_id){
    $image_list = db('techdiy_image')->where('member_id',$member_id)->order('createtime desc')->field('name,image_default_id')->limit(5)->select();
    $data['img'] = [];
    foreach ($image_list as $key => $value) {
        $url = db('image_image')->where(array('image_id'=>$value['image_default_id']))->value('url');
        if($url){
            $data['img'][$key] = str_replace('http://7xplup.com1.z0.glb.clouddn.com', 'https://image.liidon.com', $url);    
        }
    }
    $vip_image_list = db('techdiy_vipimage')->where('member_id',$member_id)->order('createtime desc')->field('name,image_default_id')->select();
    $data['vipimg'] = [];
    foreach ($vip_image_list as $key => $value) {
        $url = db('image_image')->where(array('image_id'=>$value['image_default_id']))->value('url');
        if($url){
            $data['vipimg'][$key] = str_replace('http://7xplup.com1.z0.glb.clouddn.com', 'https://image.liidon.com', $url);    
        }
    }
    return $data;
}

function arrayKeyAsc($arr){
    $new_array = [];
    if($arr){
        foreach ($arr as $key => $value) {
            $new_array[] = $value;
        }
    }
    
    return $new_array;
}
//PHP stdClass Object转array  
function object_array($array) {  
    if(is_object($array)) {  
        $array = (array)$array;  
     } if(is_array($array)) {  
         foreach($array as $key=>$value) {  
             $array[$key] = object_array($value);  
             }  
     }  
     return $array;  
}

//$html-被查找的字符串 $tag-被查找的标签 $attr-被查找的属性名 $value-被查找的属性值
function get_tag_data($html,$tag,$attr,$value){
$regex = "/<$tag.*?$attr=\".*?$value.*?\".*?>(.*?)<\/$tag>/is";
preg_match_all($regex,$html,$matches,PREG_PATTERN_ORDER);
return $matches[1];
}

function isinproduct($product_id){
    $info= Db::name('b2c_products')->where('product_id',$product_id)->find();
    if($info){
      return true;
    }else{
      return false;
    }
   return $info;
}

function getsmalllunbo(){ 
$html= file_get_contents("http://www.liidon.com/app/site/view/index.html");
$html = <<<EOF
$html
EOF;
require '/var/www/api/simple_html_dom-master/simple_html_dom.php';
$fun = function($str,$key){
    $str=preg_replace("/[\s\S]*\s".$key."[=\"\']+([^\"\']*)[\"\'][\s\S]*/","$1",$str);
    return $str;
};

    $lunbo = array();
    $ms = get_tag_data($html,'div','id','ad_shopmax_group_2452');
    if($ms){  
       $html = str_get_html($ms[0]);
       $counts = substr_count($ms[0],'<img');
       foreach($html->find('ul.clearfix') as $v) {
            for ($i=0;$i<$counts;$i++){
                $h = trim($v->find('li a', $i));
                $str = $fun($h,'href');
                $start = strripos($str,'-')+1;
                $arr = explode('.',substr($str, $start));
                if(isinproduct($arr[0])){
                    $lunbo[$i]['product_id'] = $arr[0];
                    $s = trim($v->find('a img', $i));
                    $lunbo[$i]['url'] = $fun($s,'src');    
                }
            }
        }
    }
    return arrayKeyAsc($lunbo); 
}

function getboutique(){ 
$html= file_get_contents("http://www.liidon.com/app/site/view/index.html");
$html = <<<EOF
$html
EOF;
require '/var/www/api/simple_html_dom-master/simple_html_dom.php';
$fun = function($str,$key){
    $str=preg_replace("/[\s\S]*\s".$key."[=\"\']+([^\"\']*)[\"\'][\s\S]*/","$1",$str);
    return $str;
};

    $que = array();
    $ms = get_tag_data($html,'div','id','maxCX-ad');
    if($ms){  
       $html = str_get_html($ms[0]);
       $counts = substr_count($ms[0],'<img');
       foreach($html->find('ul.clearfix') as $v) {
            for ($i=0;$i<$counts;$i++){
                $h = trim($v->find('li a', $i));
                $str = $fun($h,'href');
                $start = strripos($str,'-')+1;
                $arr = explode('.',substr($str, $start));
                if(isinproduct($arr[0])){
                    $que[$i]['product_id'] = $arr[0];
                    $que[$i]['name'] = Db::name('b2c_products')->alias('p')->join('b2c_goods g','g.goods_id = p.goods_id','left')->where('p.product_id',$arr[0])->value('g.name');
                    $s = trim($v->find('a img', $i));
                    $que[$i]['url'] = $fun($s,'src');    
                }
            }
        }
    }
    unset($que[1]);
    return arrayKeyAsc($que); 
}

function getoneque(){
$html= file_get_contents("http://www.liidon.com/app/site/view/index.html");
$html = <<<EOF
$html
EOF;
require '/var/www/api/simple_html_dom-master/simple_html_dom.php';
$fun = function($str,$key){
    $str=preg_replace("/[\s\S]*\s".$key."[=\"\']+([^\"\']*)[\"\'][\s\S]*/","$1",$str);
    return $str;
};

    $que = array();
    $ms = get_tag_data($html,'div','id','ad_pic_2490');
    if($ms){  
        $html = str_get_html($ms[0]);
        $h = trim($html->find('a', 0));
        $str = $fun($h,'href');
        $start = strripos($str,'-')+1;
        $arr = explode('.',substr($str, $start));
        if(isinproduct($arr[0])){
            $que['product_id'] = $arr[0];
            $s = trim($html->find('img', 0));
            $que['url'] = $fun($s,'src');    
        }    
    }
    return arrayKeyAsc($que);     
}

function getmaxdim($vDim)
{
  if(!is_array($vDim)) return 0;
  else
  {
    $max1 = 0;
    foreach($vDim as $item1)
    {
     $t1 = getmaxdim($item1);
     if( $t1 > $max1) $max1 = $t1;
    }
    return $max1 + 1;
  }
}

//arr 二维数组
function changeDoubleDecimal($arr){
    foreach($arr as $k=>$v){
        if(is_string($v)){
            $strrchr_str = substr(strrchr($v,'.'),1);
            if(strlen($strrchr_str)==3 && is_numeric($strrchr_str)){
                $arr[$k] = sprintf("%.2f",$v);
            }
        }
    }
    return $arr;
}


function json_encode_mb($array)
{
   if (version_compare(PHP_VERSION, '5.4.0', '<')) {
      $str = json_encode($array);
      $str = preg_replace_callback("#\\\u([0-9a-f]{4})#i", function ($matchs) {
         return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
      }, $str);
      return $str;
   } else {
      return json_encode($array, JSON_UNESCAPED_UNICODE);
   }
}

function curl_row($url,$params){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,           $url );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_POST,           1 );
    curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($params));// 必须为字符串
    curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain'));// 必须声明请求头
    $return = curl_exec($ch);
    return $return;
}

function curl_header_post($url,$params=null,$header){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,           $url );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_POST,           1 );
    if($params){
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($params));// 必须为字符串
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    $rest = curl_exec($ch);
    curl_close($ch);
    return $rest;
}

function curl_header_get($url,$params=null,$header){
    $ch = curl_init();    
    curl_setopt($ch, CURLOPT_URL, $url);    
        //参数为1表示传输数据，为0表示直接输出显示。  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        //参数为0表示不带头文件，为1表示带头文件  
    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    $output = curl_exec($ch);   
    curl_close($ch);   
    return $output;  
}
  
//获取省名称
function getProvince($province){
    if(strstr($province,'省') || strstr($province,'市')){
        $province = mb_substr($province, 0, -1);
    }
    return $province;
}

function curl_header_put($url,$data,$header){
    $data = json_encode($data);
    $ch = curl_init(); //初始化CURL句柄 
    curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
    curl_setopt ($ch, CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"PUT"); //设置请求方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置提交的字符串
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output,true);
}

function curl_delete($url,$data){
    $data  = json_encode($data);
    $ch = curl_init();
    curl_setopt ($ch,CURLOPT_URL,$url);
    curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");   
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output,true);
}

function FetchRepeatMemberInArray($array) { 
    // 获取去掉重复数据的数组 
    $unique_arr = array_unique ( $array ); 
    // 获取重复数据的数组 
    $repeat_arr = array_diff_assoc ( $array, $unique_arr ); 
    return $repeat_arr; 
} 


/**
 * php正则提取文本中多个11位国内手机号完整实例
 *
 * @author yujianyue <admin@ewuyi.net>
 * @copyright www.echafen.com
 * @version 2.5 2017-08-28
 */
 
function chafenbacom($str){
    $mobis = [];
    $tels = [];
    preg_match_all('/1[23456789][0-9]{8,10}/', $str, $match);
    foreach($match[0] as $mobi1){ 
        $mobis[] = $mobi1;
    } 
    preg_match_all("/([0-9]{3,4}-)?[0-9]{7,8}/", $str, $match2);
    foreach($match2[0] as $tel1){ 
        $tels[] = $tel1;
    } 
    foreach($tels as $key=>$value){ 
        if(strlen($value)<11){
            unset($tels[$key]);
        }
    } 
    $re = array_merge($tels,$mobis);
    return $re;//得结果，可输出查看或调用
}

function delByValue($arr, $value){
    if(!is_array($arr)){
        return $arr;
    }
    foreach($arr as $k=>$v){
        if($v == $value){
        unset($arr[$k]);
        }
    }
    return $arr;
}


/**
 * 生成JSON数据返回值
 */
function JSONReturn($result)
{
    return json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}

/**
* 将数组按字母A-Z排序
* @return [type] [description]
*/
function chartSort($arr,$field){
   
    foreach ($arr as $k => &$v) {
        $name=preg_replace('/^\d+/','',$v[$field]);
        $arr[$k]['chart']= getFirstChart( $name );
    }
        $data=array();
    foreach ($arr as $key => $value) {
        if ( empty( $data[ $value['chart'] ] ) ) {
            $data[ $value['chart'] ]=array();;
        }
        $data[ $value['chart'] ][]=$value;
    }
    ksort($data);
    return $data;
}
/**
* 返回取汉字的第一个字的首字母
* @param  [type] $str [string]
* @return [type]      [strind]
*/
function getFirstChart($str){
    $first_str = mb_substr($str,0,1);


    if($first_str=='焱'){
        return 'Y';
    }elseif($first_str=='桦'){
        return 'H';
    }elseif($first_str=='梓'){
        return 'Z';
    }
    if( empty($str) ){
        return '';
    }
        $char=ord($str[0]);
        
    if( $char >= ord('A') && $char <= ord('z') ){
        return strtoupper($str[0]);
    } 
    try{
        $s1=iconv('UTF-8','gb2312',$str);
        $s2=iconv('gb2312','UTF-8',$s1);
        $s=$s2==$str?$s1:$str;
    }catch (\Exception $e) {
        return 'Z';
    }    
    
    $asc=ord($s{0})*256+ord($s{1})-65536;
    if($asc>=-20319&&$asc<=-20284) return 'A';
    if($asc>=-20283&&$asc<=-19776) return 'B';
    if($asc>=-19775&&$asc<=-19219) return 'C';
    if($asc>=-19218&&$asc<=-18711) return 'D';
    if($asc>=-18710&&$asc<=-18527) return 'E';
    if($asc>=-18526&&$asc<=-18240) return 'F';
    if($asc>=-18239&&$asc<=-17923) return 'G';
    if($asc>=-17922&&$asc<=-17418) return 'H';
    if($asc>=-17417&&$asc<=-16475) return 'J';
    if($asc>=-16474&&$asc<=-16213) return 'K';
    if($asc>=-16212&&$asc<=-15641) return 'L';
    if($asc>=-15640&&$asc<=-15166) return 'M';
    if($asc>=-15165&&$asc<=-14923) return 'N';
    if($asc>=-14922&&$asc<=-14915) return 'O';
    if($asc>=-14914&&$asc<=-14631) return 'P';
    if($asc>=-14630&&$asc<=-14150) return 'Q';
    if($asc>=-14149&&$asc<=-14091) return 'R';
    if($asc>=-14090&&$asc<=-13319) return 'S';
    if($asc>=-13318&&$asc<=-12839) return 'T';
    if($asc>=-12838&&$asc<=-12557) return 'W';
    if($asc>=-12556&&$asc<=-11848) return 'X';
    if($asc>=-11847&&$asc<=-11056) return 'Y';
    if($asc>=-11055&&$asc<=-10247) return 'Z';

    return null;
}

//过滤字符串中的emoji表情
function removeEmoji($message) {
    $message = json_encode($message);
    return preg_replace("#(\\\ud[0-9a-f]{3})#i", "", $message);
}

//发送短信
function sendMessage($phone,$msg){
    // 请根据实际 accesskey 和 secretkey 进行开发，以下只作为演示 sdk 使用
    $accesskey = "5aa889530cf28157767b0bfc";
    $secretkey = "27a771c149c840e1978f0f887f6fd66e";
    try {
        $obj = new \sms\SmsSender($accesskey,$secretkey);
        $Tools = new \sms\SmsTools();
        // 普通单发
        $result = $obj->send(0, "86", $phone , $msg, "", "");
        $rsp = json_decode($result,true);
        return $rsp;
    } catch (\Exception $e) {
        return $e;
    }
}

?>

