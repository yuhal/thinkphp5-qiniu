<?php
namespace app\api\controller\oauth;

use think\Request;
use think\facade\Cache;
use app\api\controller\Send;
use app\api\controller\Oauth;
use app\api\model\ApiUser;
use app\api\validate\oauth\Token as TokenValidate;
use app\api\validate\oauth\Authentication as AuthenticationValidate;

//主要为跨域CORS配置的两大基本信息,Origin和headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authentication');

/**
 * 生成token
 */
class Token
{
    use Send;

    /**
     * 请求时间差
     */
    public static $timeDif = 10000;

    public static $accessTokenPrefix = 'accessToken_';
    public static $refreshAccessTokenPrefix = 'refreshAccessToken_';
    public static $expires = 7200;
    public static $refreshExpires = 60*60*24*30;   //刷新token过期时间
    /**
     * 测试appid，正式请数据库进行相关验证
     */
    public static $appid;
    /**
     * appsercet
     */
    public static $appsercet;

    /**
     * 生成token
     */
    public function token(
        Request $request,
        ApiUser $apiUser,
        TokenValidate $tokenValidate
    ){
        $params = input('get.');
        // 参数验证
        if (!$tokenValidate->check($params)) {
            return self::returnMsg(401, $tokenValidate->getError());
        }
        // 传入参数应该是根据手机号查询改用户的数据
        $user = $apiUser->getByMobile($params['mobile']);
        self::checkParams($params, $user);  //参数校验
        $userInfo = [
            'uid'   => $user['id'],
            'mobile'=> $user['mobile']
        ];
        try {
            $accessToken = self::setAccessToken(array_merge($userInfo, $params));
            return self::returnMsg(200, 'success', $accessToken);
        } catch (Exception $e) {
            return self::returnMsg(500, 'fail', $e);
        }
    }

    /**
     * 刷新token
     */
    public function refresh(
        AuthenticationValidate $authenticationValidate,
        ApiUser $apiUser
    ){
        $params = input('get.');
        // 参数验证
        if (!$authenticationValidate->scene('refresh')->check($params)) {
            return self::returnMsg(401, $authenticationValidate->getError());
        }
        $cache_refresh_token = Cache::get(self::$refreshAccessTokenPrefix.$params['appid']);  //查看刷新token是否存在
        if ($cache_refresh_token !== $params['refresh_token']) {
            return self::returnMsg(401, 'fail', 'refresh_token is error');
        } else {
            // 重新给用户生成调用token
            $userInfo = $apiUser->field('id as uid,appid,mobile')
                ->getByAppid($params['appid'])
                ->toArray();
            $accessToken = self::setAccessToken($userInfo);
            return self::returnMsg(200, 'success', $accessToken);
        }
    }

    /**
     * 参数检测
     */
    public static function checkParams($params = [], $user)
    {
        // 请求的头信息
        $requestHeader = request()->header();
        // 时间戳校验
        if (abs($params['timestamp'] - time()) > self::$timeDif) {
            return self::returnMsg(401, 'Request timestamp and server timestamp exception', $requestHeader);
        }
        if (!$user) {
            return self::returnMsg(401, 'Mobile not found', $requestHeader);
        } else {
            // appid检测
            if ($params['appid'] != $user['appid']) {
                return self::returnMsg(401, 'Appid not found', $requestHeader);
            }
            self::$appsercet = $user['appsercet'];
        }
        // 签名检测
        $sign = Oauth::makeSign($params, self::$appsercet);
        if ($sign !== $params['sign']) {
            return self::returnMsg(401, 'Invalid sign', $requestHeader);
        }
    }

    /**
     * 设置AccessToken
     * @param $clientInfo
     * @return int
     */
    protected function setAccessToken($clientInfo)
    {
        //生成令牌
        $accessToken = self::buildAccessToken();
        $refresh_token = self::getRefreshToken($clientInfo['appid']);
        $accessTokenInfo = [
            'access_token'  => $accessToken,//访问令牌
            'expires_time'  => time() + self::$expires,      //过期时间时间戳
            'refresh_token' => $refresh_token,//刷新的token
            'refresh_expires_time'  => time() + self::$refreshExpires,      //过期时间时间戳
            'client'        => $clientInfo,//用户信息
        ];
        self::saveAccessToken($accessToken, $accessTokenInfo);  //保存本次token
        self::saveRefreshToken($refresh_token, $clientInfo['appid']);
        return $accessTokenInfo;
    }

    /**
     * 刷新用的token检测是否还有效
     */
    public static function getRefreshToken($appid = '')
    {
        return Cache::get(self::$refreshAccessTokenPrefix.$appid) ? Cache::get(self::$refreshAccessTokenPrefix.$appid) : self::buildAccessToken();
    }

    /**
     * 生成AccessToken
     * @return string
     */
    protected static function buildAccessToken($lenght = 32)
    {
        //生成AccessToken
        $str_pol = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwxyz";
        return substr(str_shuffle($str_pol), 0, $lenght);
    }

    /**
     * 存储token
     * @param $accessToken
     * @param $accessTokenInfo
     */
    protected static function saveAccessToken($accessToken, $accessTokenInfo)
    {
        //存储accessToken
        cache(self::$accessTokenPrefix . $accessToken, $accessTokenInfo, self::$expires);
    }

    /**
     * 刷新token存储
     * @param $accessToken
     * @param $accessTokenInfo
     */
    protected static function saveRefreshToken($refresh_token, $appid)
    {
        //存储RefreshToken
        cache(self::$refreshAccessTokenPrefix.$appid, $refresh_token, self::$refreshExpires);
    }
}
