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
class WeChat {
	const WECHAT_HOST = 'api.weixin.qq.com';
	const WECHAT_PORT = 443;
	const WECHAT_HTTPS = TRUE;
	private $appid;
	private $secret;
	/**
	 * 单例模式相关方法
	 */
	public static $_instance = [];
	public static function getInstance($appid, $secret): WeChat {
		if (!isset(self::$_instance[$appid])) {
			self::$_instance[$appid] = new self($appid, $secret);
		}
		return self::$_instance[$appid];
	}
	/**
	 * 调用微信API
	 * 
	 * @access private
	 * @param string $name
	 * @param array $param
	 * @return array
	 */
	private function callApi($name, $param = NULL, $post = NULL) {
		return Utils::fetchUrl([
			'type' => $post === NULL ? Utils::FETCH_GET : Utils::FETCH_POST,
			'host' => self::WECHAT_HOST,
			'port' => self::WECHAT_PORT,
			'is_https' => self::WECHAT_HTTPS,
			'is_json' => TRUE,
			'path' => '/cgi-bin/' . $name . (is_array($param) ? '?' . http_build_query($param) : ''),
			'post' => $post === NULL ? NULL : json_encode($post),
			'post_type' => 'application/json'
		]);
	}
	private function callSnsApi($name, $param = NULL) {
		return Utils::fetchUrl([
			'type' => Utils::FETCH_GET,
			'host' => self::WECHAT_HOST,
			'port' => self::WECHAT_PORT,
			'is_https' => self::WECHAT_HTTPS,
			'is_json' => TRUE,
			'path' => '/sns/' . $name . (is_array($param) ? '?' . http_build_query($param) : '')
		]);
	}
	/**
	 * 初始化
	 * 
	 * @access public
	 * @param string $appid
	 * @param string $secret
	 */
	public function __construct($appid, $secret) {
		$this->appid = $appid;
		$this->secret = $secret;
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
			$res = $this->callApi('token', [
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
			$res = $this->callApi('ticket/getticket', [
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
		$this->callApi('message/template/send', [
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
		return $this->callApi('user/info', [
			'access_token' => $access_token,
			'openid' => $openid,
			'lang' => 'zh_CN'
		]);
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
		return 'https://open.weixin.qq.com/connect/oauth2/authorize' . http_build_query([
			'appid' => $this->appid,
			'redirect_uri' => $redirect_uri,
			'response_type' => 'code',
			'scope' => 'snsapi_userinfo',
			'state' => $state
		]) . '#wechat_redirect';
	}
	/**
	 * SNS接口：获取AccessToken
	 * 
	 * @access public
	 * @param string $code
	 * @return array
	 */
	public function getSnsAccessToken($code) {
		return $this->callSnsApi('oauth2/access_token', [
			'appid' => $this->appid,
			'secret' => $this->secret,
			'code' => $code,
			'grant_type' => 'authorization_code'
		]);
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
		return $this->callSnsApi('userinfo', [
			'access_token' => $access_token,
			'openid' => $openid,
			'lang' => 'zh_CN'
		]);
	}
	/**
	 * SNS接口：刷新AccessToken
	 * 
	 * @access public
	 * @param string $refresh_token
	 * @return array
	 */
	public function getSnsNewAccessToken($refresh_token) {
		return $this->callSnsApi('oauth2/refresh_token', [
			'appid' => $this->appid,
			'refresh_token' => $refresh_token,
			'grant_type' => 'refresh_token'
		]);
	}
}