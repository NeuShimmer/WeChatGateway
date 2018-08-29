<?php
/**
 * 基本类
 * 
 * @author ShuangYa
 * @package WeChatGateway
 * @category Controller
 * @link https://shimmer.neusoft.edu.cn/
 * @copyright Copyright (c) 2018 Shimmer Network Studio
 * @license https://github.com/NeuShimmer/WechatGateway/blob/master/LICENSE
 */
namespace shimmerwx\controller\admin;
use \yesf\library\ControllerAbstract;
use \shimmerwx\library\Utils;

class Index extends ControllerAbstract {
	/**
	 * 获取登录状态
	 * 因为已经在Plugin中进行检查，此处直接返回成功即可
	 * 
	 * @api {get} /admin/index/status 获取登录状态
	 * @apiName GetStatus
	 * @apiGroup Admin
	 * 
	 * @apiSuccess {Boolean} success 是否登录
	 */
	public static function statusAction($request, $response) {
		$response->write(Utils::getWebApiResult());
	}
}