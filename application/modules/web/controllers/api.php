<?php
/**
 * WEB接口
 * 
 * @author ShuangYa
 * @package WeChatGateway
 * @category Controller
 * @link https://shimmer.neusoft.edu.cn/
 * @copyright Copyright (c) 2018 Shimmer Network Studio
 * @license https://github.com/NeuShimmer/WechatGateway/blob/master/LICENSE
 */
namespace shimmerwx\controller\web;
use \yesf\library\ControllerAbstract;
use \shimmerwx\library\Utils;
use \shimmerwx\library\WeChat;

class Api extends ControllerAbstract {
	/**
	 * 初始化JS SDK
	 * 
	 * 
	 */
	public static function initAction($request, $response) {
		$wechat = Utils::getWeChat();
		$url = $request->get['url'];
		$randStr = Utils::getRandStr(6);
		$time = time();
		$result = [
			'appId' => '123',
    		'timestamp' => $time,
    		'nonceStr' => $randStr,
    		'signature' => $wechat->getJsSign($url, $noncestr, $timestamp)
		];
		$response->write(Utils::getWebApiResult($result));
	}
}