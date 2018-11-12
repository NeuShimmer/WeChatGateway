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
	 * 获取选项
	 * 
	 * @api {get} /web/privapi/getSetting 获取选项
	 * @apiName GetSetting
	 * @apiGroup PageApi
	 * 
	 * @apiSuccess {Int} receive_push 是否接受推送
	 */
	public static function getSettingAction($request, $response) {
		$response->write(Utils::getWebApiResult([
			'receive_push' => $request->user['receive_push'] ? 1 : 0
		]));
	}
	/**
	 * 保存设置
	 * 
	 * @api {post} /web/pageapi/setSetting 保存设置
	 * @apiName setSetting
	 * @apiGroup PageApi
	 * 
	 * @apiParam {Int} receive_push 是否接受推送
	 */
	public static function setSettingAction($request, $response) {
		$set = [];
		if (isset($request->post['receive_push'])) {
			$set['receive_push'] = $request->post['receive_push'] == 1 ? 1 : 0;
		}
		if (count($set) > 0) {
			User::getInstance()->set($set, $request->user['id']);
		}
		$response->write(Utils::getWebApiResult());
	}
}