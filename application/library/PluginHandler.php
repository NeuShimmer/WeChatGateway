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
use shimmerwx\model\Token;
use shimmerwx\model\User;

class PluginHandler {
	public static function onBeforeDispatcher($module, $controller, $action, $request, $response) {
		if ($module === 'index' || ($module === 'web' && $controller === 'page')) {
			$response->assign('__PUBLIC_URL', Yesf::app()->getConfig('public'));
		}
		if ($module === 'web' && $controller === 'api' && $action !== 'media') {
			$response->header('Content-Type', 'application/json; charset=UTF-8');
		}
		if ($module === 'web' && $controller === 'pageapi') {
			$response->header('Content-Type', 'application/json; charset=UTF-8');
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
			$request->user = array_merge($info, $user);
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