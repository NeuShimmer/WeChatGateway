<?php
/**
 * PC登录Token
 * 
 * @author ShuangYa
 * @package WeChatGateway
 * @category Model
 * @link https://shimmer.neusoft.edu.cn/
 * @copyright Copyright (c) 2018 Shimmer Network Studio
 * @license https://github.com/NeuShimmer/WechatGateway/blob/master/LICENSE
 */
namespace shimmerwx\model;
use yesf\library\ModelAbstract;
use shimmerwx\library\Cache;
use shimmerwx\library\Utils;

class LoginToken {
	//Token有效期，单位：秒
	const EXPIRE = 120;
	/**
	 * 获取Token和对应的信息
	 * 
	 * @access public
	 * @param string $token
	 * @return array/null
	 */
	public static function get($token) {
		$result = Cache::get('login_token_' . $token);
		if ($result) {
			return swoole_unserialize($result);
		}
		return NULL;
	}
	/**
	 * Update Token
	 * 
	 * @access public
	 * @return string
	 */
	public static function set($token, $info) {
		Cache::set('login_token_' . $token, swoole_serialize($info), self::EXPIRE);
		return $token;
	}
	/**
	 * 创建Token
	 * 
	 * @access public
	 * @return string
	 */
	public static function create() {
		$token = hash('sha256', uniqid(Utils::getRandStr(4), TRUE));
		Cache::set('login_token_' . $token, swoole_serialize([
			'status' => 1,
			'wechat_token' => null
		]), self::EXPIRE);
		return $token;
	}
}