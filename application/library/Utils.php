<?php
/**
 * 工具类
 * 
 * @author ShuangYa
 * @package WeChatGateway
 * @category Library
 * @link https://shimmer.neusoft.edu.cn/
 * @copyright Copyright (c) 2018 Shimmer Network Studio
 * @license https://github.com/NeuShimmer/WechatGateway/blob/master/LICENSE
 */
namespace shimmerwx\library;

use Swoole\Coroutine as co;
use shimmerwx\model\Config;
use shimmerwx\model\App;

class Utils {
	/**
	 * 获取随机字符串
	 * 
	 * @access public
	 * @param int $length 长度
	 * @return string
	 */
	public static function getRandStr($length) {
		$str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		while (strlen($str) < $length) {
			$str .= $str;
		}
		$str = str_shuffle($str);
		return substr($str, 0, $length);
	}
	/**
	 * 格式化WebAPI的返回结果
	 * 
	 * @access public
	 * @param array $result
	 * @return string
	 */
	public static function getWebApiResult($result = []) {
		if (isset($result['error'])) {
			return json_encode([
				'success' => FALSE,
				'error' => $result['error']
			]);
		} else {
			$result['success'] = TRUE;
			return json_encode($result);
		}
	}
	/**
	 * 格式化私有API的返回结果
	 * 
	 * @access public
	 * @param array $result
	 * @return string
	 */
	public static function getPrivApiResult($result = []) {
		if (isset($result['error'])) {
			return swoole_serialize([
				'success' => FALSE,
				'error' => $result['error']
			]);
		} else {
			$result['success'] = TRUE;
			return swoole_serialize($result);
		}
	}
	/**
	 * 获取微信配置
	 * 
	 * @access public
	 * @param int $id
	 * @return array
	 */
	public static function getWeChatConfig($id = -1) {
		if ($id === -1) {
			return [
				'appid' => Config::getInstance()->read('appid'),
				'secret' => Config::getInstance()->read('appsecret')
			];
		} else {
			$app = App::getInstance()->get($id);
			if (!$app) {
				return NULL;
			}
			return [
				'appid' => $app['appid'],
				'secret' => $app['appsecret']
			];
		}
	}
	/**
	 * 获取微信实例类
	 * 
	 * @access public
	 * @param int $id
	 * @return object
	 */
	public static function getWeChat($id = -1) {
		$config = self::getWeChatConfig($id);
		if ($config === NULL) {
			return NULL;
		}
		return WeChat::getInstance($config['appid'], $config['secret']);
	}
}