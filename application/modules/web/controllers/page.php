<?php
/**
 * 面向用户的页面
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
use shimmerwx\model\Config;
use shimmerwx\model\User;
use shimmerwx\model\Token;

class Page extends ControllerAbstract {
	//登录页面
	public static function loginAction($request, $response) {
		$wechat = Utils::getWeChat();
		$url = $wechat->getSnsLoginUrl(Config::getInstance()->read('redirect_uri'));
		$response->assign('title', '登录');
		$response->assign('message', '请稍候');
		$response->assign('desc', '正在前往登录页面');
		$response->assign('time', 500);
		$response->assign('type', 'wait');
		$response->assign('url', $url);
		$response->assign('extra_script', 'sessionStorage.setItem("redirect_uri", decodeURIComponent("' . urlencode($request->get['redirect_uri']) . '"));');
		$response->display('page/redirect');
	}
	//登录跳转页面
	public static function redirectAction($request, $response) {
		$wechat = Utils::getWeChat();
		$code = $request->get['code'];
		$token = $wechat->getSnsAccessToken($code);
		//AuthorizeCode无效，跳转到登录页面
		if (!$token['access_token']) {
			$url = $wechat->getSnsLoginUrl(Config::getInstance()->read('redirect_uri'));
			$response->assign('title', '登录');
			$response->assign('message', '请稍候');
			$response->assign('desc', '正在前往登录页面');
			$response->assign('time', 500);
			$response->assign('type', 'wait');
			$response->assign('url', $url);
			$response->display('page/redirect');
			return;
		}
		//获取用户的基本信息
		$user = $wechat->getSnsUserInfo($token['access_token'], $token['openid']);
		//检查是否已经存在，建立openid和unionid的对应关系
		$u = User::getInstance()->get([
			'unionid' => $token['unionid']
		]);
		if ($u) {
			//更新用户信息
			if (empty($u['openid'])) {
				User::getInstance()->set([
					'openid' => $user['openid'],
					'nickname' => $user['nickname']
				], $u['id']);
			} else {
				User::getInstance()->set([
					'nickname' => $user['nickname']
				], $u['id']);
			}
			$id = $u['id'];
		} else {
			$id = User::getInstance()->add([
				'openid' => $user['openid'],
				'unionid' => $user['unionid'],
				'nickname' => $user['nickname'],
				'is_follow' => 0,
				'receive_push' => 0
			]);
		}
		//添加token信息
		$user['id'] = $id;
		$token = Token::create($user);
		$response->cookie([
			'name' => 'wechat_token',
			'value' => $token,
			'expire' => 0,
			'path' => '/',
			'domain' => Config::getInstance()->read('cookie_domain'),
			'httponly' => TRUE
		]);
		$response->assign('title', '完成登录');
		$response->assign('message', '登录成功');
		$response->assign('desc', '正在前往登录前页面');
		$response->assign('type', 'success');
		$response->assign('url', '');
		$response->assign('extra_script', 'var url=sessionStorage.getItem("redirect_uri");document.getElementById("continue").href=url;setTimeout(()=>{window.location.href=url;},1000);');
		$response->display('page/redirect');
	}
	//用户设置自己是否接收消息推送
}