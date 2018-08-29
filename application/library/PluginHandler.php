<?php
/**
 * 插件响应类
 * 
 * @author ShuangYa
 * @package WeChatGateway
 * @category Library
 * @link https://shimmer.neusoft.edu.cn/
 * @copyright Copyright (c) 2018 Shimmer Network Studio
 * @license https://github.com/NeuShimmer/WechatGateway/blob/master/LICENSE
 */
namespace shimmerwx\library;

use yesf\Yesf;
use shimmerwx\model\Config;

class PluginHandler {
	public static function onBeforeDispatcher($module, $controller, $action, $request, $response) {
		if ($module === 'index' || ($module === 'web' && $controller !== 'api')) {
			$response->assign('__PUBLIC_URL', Yesf::app()->getConfig('application.public'));
		}
		if ($module === 'web' && $controller === 'api') {
			$response->header('Content-Type', 'application/json; charset=UTF-8');
		}
		if ($module === 'admin') {
			$response->header('Content-Type', 'application/json; charset=UTF-8');
			//检查登录状态
			if (!isset($request->header['x-admin-password'])) {
				$response->write(Utils::getWebApiResult([
					'error' => '未登录'
				]));
				return FALSE;
			}
			$admin = $request->header['x-admin-password'];
			if ($admin !== Config::getInstance()->read('admin_password')) {
				$response->write(Utils::getWebApiResult([
					'error' => '未登录'
				]));
				return FALSE;
			}
		}
		return NULL;
	}
}