<?php
/**
 * 私有API
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
use \shimmerwx\model\User;

class Privapi extends ControllerAbstract {
	/**
	 * 发送模板消息
	 * 
	 * @api {post} /web/privapi/send 发送模板消息
	 * @apiName Send
	 * @apiGroup PrivateAPI
	 * 
	 * @apiSuccess {Int} receive_push 是否接受推送
	 */
	public static function sendAction($request, $response) {
		$wechat = Utils::getWeChat();
		$data = swoole_unserialize($request->rawContent());
		//检查是否开启消息推送
		$user = User::getInstance()->get($data['to']);
		if (!$user || !$user['receive_push']) {
			$response->write(Utils::getPrivApiResult([
				'error' => '无法推送消息'
			]));
			return;
		}
		$wechat->send($user['openid'], $data['template'], $data['data'], $data['url'], $data['mini_prog']);
		$response->write(Utils::getPrivApiResult());
	}
}