<?php
/**
 * Token登录相关
 * 因为Token不会进行长期保留，因此不进行持久化储存
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

class Token {
	//Token有效期，单位：秒
	const EXPIRE = 3600;
	/**
	 * 获取Token和对应的信息
	 * 
	 * @access public
	 * @param string $token
	 * @return array/null
	 */
	public static function get($token) {
		$result = Cache::get('token_' . $token);
		if ($result) {
			Cache::ttl('token_' . $token, self::EXPIRE);
			return $result;
		}
		return NULL;
	}
	/**
	 * 创建Token
	 * 
	 * @access public
	 * @param array $user 用户信息
	 * @return string
	 */
	public static function create($user) {
		$token = hash('sha256', uniqid(Utils::getRandStr(4), TRUE));
		Cache::set('token_' . $token, $user, self::EXPIRE);
		return $token;
	}
}