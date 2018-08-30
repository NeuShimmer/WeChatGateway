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
use \shimmerwx\library\WeChat;
use \shimmerwx\model\Token;

class Privapi extends ControllerAbstract {
	private static function checkLogin($request, $response) {
		$token = $request->cookie['wechat_token'];
		$info = Token::get($token);
		if (!$info) {
			$response->write(Utils::getWebApiResult([
				'error' => '未登录'
			]));
			return FALSE;
		}
		return $info;
	}
	/**
	 * 设置是否接受推送
	 * 
	 * @api {get} /web/privapi/setPush 设置是否接受推送
	 * @apiName SetPush
	 * @apiGroup Public
	 * 
	 * @apiParam {Int} receive 是否接受推送
	 */
	public static function setPushAction($request, $response) {
		if (($user = self::checkLogin($request, $response)) === FALSE) {
			return;
		}
		//
	}
}