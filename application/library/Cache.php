<?php
/**
 * 缓存封装
 * 
 * @author ShuangYa
 * @package WeChatGateway
 * @category Library
 * @link https://shimmer.neusoft.edu.cn/
 * @copyright Copyright (c) 2018 Shimmer Network Studio
 * @license https://github.com/NeuShimmer/WechatGateway/blob/master/LICENSE
 */
namespace shimmerwx\library;
use \yesf\library\database\Database;

class Cache {
	const PREFIX = 'wg_';
	public static function get($k) {
		$result = Database::get('redis')->get(self::PREFIX . $k);
		return $result ? swoole_unserialize($result) : NULL;
	}
	public static function set($k, $v, $ttl = 7200) {
		Database::get('redis')->set(self::PREFIX . $k, swoole_serialize($v), $ttl);
	}
}