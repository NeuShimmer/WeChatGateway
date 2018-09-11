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
use \shimmerwx\model\User;

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
		//从数据库中读取
		$user = User::getInstance()->get($info['id']);
		return array_merge($info, $user);
	}
	/**
	 * 获取选项
	 * 
	 * @api {get} /web/privapi/getSetting 获取选项
	 * @apiName GetSetting
	 * @apiGroup Public
	 * 
	 * @apiSuccess {Int} receive_push 是否接受推送
	 */
	public static function getSettingAction($request, $response) {
		if (($user = self::checkLogin($request, $response)) === FALSE) {
			return;
		}
		$response->write(Utils::getWebApiResult([
			'receive_push' => $user['receive_push'] ? 1 : 0
		]));
	}
	/**
	 * 保存设置
	 * 
	 * @api {post} /web/privapi/setSetting 保存设置
	 * @apiName setSetting
	 * @apiGroup Public
	 * 
	 * @apiParam {Int} receive_push 是否接受推送
	 */
	public static function setSettingAction($request, $response) {
		if (($user = self::checkLogin($request, $response)) === FALSE) {
			return;
		}
		$set = [];
		if (isset($request->post['receive_push'])) {
			$set['receive_push'] = $request->post['receive_push'] == 1 ? 1 : 0;
		}
		if (count($set) > 0) {
			User::getInstance()->set($set, $user['id']);
		}
		$response->write(Utils::getWebApiResult());
	}
}