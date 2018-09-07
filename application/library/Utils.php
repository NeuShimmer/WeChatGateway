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
	const FETCH_GET = 1;
	const FETCH_POST = 2;
	/**
	 * 抓取URL
	 * 
	 * @param array $info
	 * @param int $info[type] GET或POST
	 * @param string $info[host] 主机名
	 * @param int $info[port] 可选，端口
	 * @param boolean $info[is_https] 是否为HTTPS
	 * @param string $info[path] 路径
	 * @param string/array $info[post] POST内容
	 * @param string $info[post_type] POST类型，默认为application/x-www-form-urlencoded
	 * @param boolean $info[is_json] 预期返回类型是否为JSON
	 */
	public static function fetchUrl($info) {
		$ip = co::gethostbyname($info['host']);
		$port = isset($info['port']) ? $info['port'] : ($info['is_https'] ? 443 : 80);
		$cli = new co\Http\Client($ip, $port, $info['is_https']);
		$headers = [
			'Host' => $info['host'],
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36'
		];
		if ($info['type'] === self::FETCH_POST && isset($info['post_type'])) {
			$headers['Content-Type'] = $info['post_type'];
		}
		$cli->setHeaders($headers);
		if ($info['type'] === self::FETCH_GET) {
			$cli->get($info['path']);
		} else {
			$cli->post($info['path'], $info['post']);
		}
		$result = $cli->body;
		if ($info['is_json'] || (isset($cli->headers['content-type']) && stripos($cli->headers['content-type'], 'application/json') === 0)) {
			$result = json_decode($result, 1);
		}
		$cli->close();
		return $result;
	}
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