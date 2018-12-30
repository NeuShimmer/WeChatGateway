<?php
/**
 * 微信常用API封装
 * 关于用户相关的接口，均以SNS开头
 * 
 * @author ShuangYa
 * @package WeChatGateway
 * @category Library
 * @link https://shimmer.neusoft.edu.cn/
 * @copyright Copyright (c) 2018 Shimmer Network Studio
 * @license https://github.com/NeuShimmer/WechatGateway/blob/master/LICENSE
 */
namespace shimmerwx\library;

use Swlib\Saber;

class WeChat {
	const WECHAT_URL = 'https://api.weixin.qq.com/';
	const TYPE_MP = 1;
	const TYPE_MINI_PROG = 2;
	private $appid;
	private $secret;
	private $type;
	/**
	 * 单例模式相关方法
	 */
	public static $_instance = [];
	public static function getInstance($appid, $secret, $type = self::TYPE_MP): WeChat {
		if (!isset(self::$_instance[$appid])) {
			self::$_instance[$appid] = new self($appid, $secret, $type);
		}
		return self::$_instance[$appid];
	}
	/**
	 * 获取Saber实例类
	 * 
	 * @access private
	 * @return object
	 */
	private static function getSaber() {
		static $client = NULL;
		if ($client === NULL) {
			$client = Saber::create([
				'timeout' => 15,
				'base_uri' => self::WECHAT_URL,
				'use_pool' => 10
			]);
		}
		return $client;
	}
	/**
	 * 调用微信API
	 * 
	 * @access private
	 * @param string $name
	 * @param array $param
	 * @param array $post
	 * @param boolean $sns
	 * @return array
	 */
	private static function callApi($name, $param = NULL, $post = NULL, $sns = FALSE) {
		$client = self::getSaber();
		$url = ($sns ? '/sns' : '/cgi-bin') . '/' . $name;
		if ($post === NULL) {
			$result = $client->get($url . (is_array($param) ? '?' . http_build_query($param) : ''));
		} else {
			$result = $client->post($url, json_encode($post));
		}
		return $result->getParsedJsonArray();
	}
	/**
	 * 初始化
	 * 
	 * @access public
	 * @param string $appid
	 * @param string $secret
	 */
	public function __construct($appid, $secret, $type) {
		$this->appid = $appid;
		$this->secret = $secret;
		$this->type = $type;
	}
	/**
	 * 获取AppID
	 * 
	 * @access public
	 * @return string
	 */
	public function getAppId() {
		return $this->appid;
	}
	/**
	 * 判断当前实例化的类型
	 * 
	 * @access public
	 * @return boolean
	 */
	public function isMp() {
		return self::TYPE_MP === $this->type;
	}
	public function isMiniProg() {
		return self::TYPE_MINI_PROG === $this->type;
	}
	/**
	 * 获取公众号的AccessToken
	 * 
	 * @return string
	 */
	public function getAccessToken() {
		$res = Cache::get('mp_token');
		if ($res && $res['expire_at'] <= time()) {
			$res = NULL;
		}
		if (empty($res['access_token'])) {
			$res = self::callApi('token', [
				'grant_type' => 'client_credential',
				'appid' => $this->appid,
				'secret' => $this->secret
			]);
			$res['expire_at'] = time() + $res['expires_in'];
			Cache::set('mp_token', $res, $res['expires_in']);
		}
		return $res['access_token'];
	}
	/**
	 * 获取JS Ticket
	 * 
	 * @access public
	 * @param string $access_token 公众号AccessToken
	 * @return string
	 */
	public function getJsTicket($access_token = NULL) {
		if ($access_token === NULL) {
			$access_token = $this->getAccessToken();
		}
		$res = Cache::get('mp_js_' . md5($access_token));
		if ($res && $res['expire_at'] <= time()) {
			$res = NULL;
		}
		if (empty($res['ticket'])) {
			$res = self::callApi('ticket/getticket', [
				'access_token' => $access_token,
				'type' => 'jsapi'
			]);
			$res['expire_at'] = time() + $res['expires_in'];
			Cache::set('mp_js_' . md5($access_token), $res, $res['expires_in']);
		}
		return $res['ticket'];
	}
	/**
	 * 生成JS签名
	 * 
	 * @access public
	 * @param string $url 完整地址
	 * @param string $noncestr 随机字符串
	 * @param string $timestamp 时间戳
	 * @param string $js_ticket JS Ticket
	 * @return string
	 */
	public function getJsSign($url, $noncestr, $timestamp, $js_ticket = NULL) {
		if ($js_ticket === NULL) {
			$js_ticket = $this->getJsTicket();
		}
		$data = [
			'noncestr' => $noncestr,
			'jsapi_ticket' => $js_ticket,
			'timestamp' => $timestamp,
			'url' => $url
		];
		ksort($data);
		$dataStr = '';
		foreach ($data as $k => $v) {
			$dataStr .= $k . '=' . $v . '&';
		}
		$dataStr = trim($dataStr, '&');
		$sign = sha1($dataStr);
		return $sign;
	}
	/**
	 * 发送模板消息
	 * 
	 * @access public
	 * @param string $to 发送目标用户ID
	 * @param string $tpl 模板ID
	 * @param string $datas 模板数据
	 * @param string $access_token 公众号AccessToken
	 */
	public function send($to, $tpl, $datas, $url = NULL, $mini_prog = NULL, $access_token = NULL) {
		if ($access_token === NULL) {
			$access_token = $this->getAccessToken();
		}
		$data = [
			'touser' => $to,
			'template_id' => $tpl,
			'data' => $datas
		];
		if (is_string($url)) {
			$data['url'] = $url;
		}
		if (is_array($mini_prog)) {
			$data['miniprogram'] = $mini_prog;
		}
		self::callApi('message/template/send', [
			'access_token' => $access_token
		], $data);
	}
	/**
	 * 发送模板消息
	 * 
	 * @access public
	 * @param string $to 发送目标用户ID
	 * @param string $tpl 模板ID
	 * @param string $datas 模板数据
	 * @param string $access_token 公众号AccessToken
	 */
	public function sendMiniProg($to, $tpl, $datas, $form_id, $page = NULL, $access_token = NULL) {
		if ($access_token === NULL) {
			$access_token = $this->getAccessToken();
		}
		$data = [
			'touser' => $to,
			'template_id' => $tpl,
			'form_id' => $form_id,
			'data' => $datas
		];
		if ($page) {
			$data['page'] = $page;
		}
		self::callApi('message/wxopen/template/send', [
			'access_token' => $access_token
		], $data);
	}
	/**
	 * 获取用户信息
	 * 
	 * @access public
	 * @param string $openid OpenID
	 * @param string access_token
	 * @return array
	 */
	public function getUserInfo($openid, $access_token = NULL) {
		if ($access_token === NULL) {
			$access_token = $this->getAccessToken();
		}
		return self::callApi('user/info', [
			'access_token' => $access_token,
			'openid' => $openid,
			'lang' => 'zh_CN'
		]);
	}
	/**
	 * 获取已上传的多媒体资源
	 * 
	 * @access public
	 * @param int $id
	 * @param string $access_token
	 * @return array
	 */
	public function getMedia($id, $access_token = NULL) {
		if ($access_token === NULL) {
			$access_token = $this->getAccessToken();
		}
		$client = self::getSaber();
		$result = $client->get('/cgi-bin/media/get?' . http_build_query([
			'access_token' => $access_token,
			'media_id' => $id
		]));
		$content_type = $result->getHeader('content-type')[0];
		$body = strval($result->getBody());
		return [$content_type, $body];
	}
	/**
	 * SNS接口：获取登录地址
	 * 
	 * @access public
	 * @param string $redirect_uri
	 * @param string $state
	 * @return string
	 */
	public function getSnsLoginUrl($redirect_uri, $state = 'shimmer') {
		return 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query([
			'appid' => $this->appid,
			'redirect_uri' => $redirect_uri,
			'response_type' => 'code',
			'scope' => 'snsapi_userinfo',
			'state' => $state
		]) . '#wechat_redirect';
	}
	/**
	 * SNS接口：获取Session
	 * 
	 * @access public
	 * @param string $code
	 * @return array
	 */
	public function getSnsSession($code) {
		return self::callApi('jscode2session', [
			'appid' => $this->appid,
			'secret' => $this->secret,
			'js_code' => $code,
			'grant_type' => 'authorization_code'
		], NULL, TRUE);
	}
	/**
	 * SNS接口：获取AccessToken
	 * 
	 * @access public
	 * @param string $code
	 * @return array
	 */
	public function getSnsAccessToken($code) {
		return self::callApi('oauth2/access_token', [
			'appid' => $this->appid,
			'secret' => $this->secret,
			'code' => $code,
			'grant_type' => 'authorization_code'
		], NULL, TRUE);
	}
	/**
	 * SNS接口：获取用户信息
	 * 
	 * @access public
	 * @param string $access_token
	 * @param string $openid
	 * @return array
	 */
	public function getSnsUserInfo($access_token, $openid) {
		return self::callApi('userinfo', [
			'access_token' => $access_token,
			'openid' => $openid,
			'lang' => 'zh_CN'
		], NULL, TRUE);
	}
	/**
	 * SNS接口：刷新AccessToken
	 * 
	 * @access public
	 * @param string $refresh_token
	 * @return array
	 */
	public function getSnsNewAccessToken($refresh_token) {
		return self::callApi('oauth2/refresh_token', [
			'appid' => $this->appid,
			'refresh_token' => $refresh_token,
			'grant_type' => 'refresh_token'
		], NULL, TRUE);
	}
	/**
	 * 解密小程序的加密数据
	 * 
	 * @access public
	 * @param string $data
	 * @param string $key
	 * @param string $iv
	 * @return array
	 */
	public function decryptData($data, $key, $iv) {
		if (strlen($key) != 24) {
			throw new \Exception('session_key无效');
		}
		$aesKey = base64_decode($key);
		if (strlen($iv) != 24) {
			throw new \Exception('iv无效');
		}
		$aesIV = base64_decode($iv);
		$aesCipher = base64_decode($data);
		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
		$resultArr = json_decode($result, 1);
		if ($resultArr == NULL) {
			throw new \Exception('解密失败');
		}
		if ($resultArr['watermark']['appid'] != $this->getAppId()) {
			throw new \Exception('校验失败');
		}
		return $resultArr;
	}
}