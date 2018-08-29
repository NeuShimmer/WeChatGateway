<?php
/**
 * 管理员相关（无需鉴权部分）
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
use \shimmerwx\model\Config;

class Admin extends ControllerAbstract {
	//首页
	public static function indexAction($request, $response) {
		$response->display('admin/index');
	}
	/**
	 * 登录
	 * 
	 * @api {post} /index/admin/login 登录
	 * @apiName Login
	 * @apiGroup Admin
	 * 
	 * @apiParam {String} password 密码
	 * 
	 * @apiSuccess {Boolean} success 是否成功
	 * @apiSuccess {String} error 失败原因
	 */
	public static function adminAction($request, $response) {
		$response->header('Content-Type', 'application/json; charset=UTF-8');
		$password = $request->post['password'];
		$encrypt = hash('sha256', $password);
		if ($encrypt === Config::getInstance()->read('admin_password')) {
			$response->cookie([
				'name' => 'wechat_admin',
				'value' => $encrypt,
				'expire' => 0,
				'path' => '/',
				'domain' => Config::getInstance()->read('cookie_domain'),
				'httponly' => TRUE
			]);
			$response->write(Utils::getWebApiResult([]));
		} else {
			$response->write(Utils::getWebApiResult([
				'error' => '密码错误'
			]));
		}
	}
}