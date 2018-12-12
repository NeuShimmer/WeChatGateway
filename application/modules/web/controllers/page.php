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
use yesf\Yesf;
use yesf\library\ControllerAbstract;
use shimmerwx\library\Utils;
use shimmerwx\library\WeChat;
use shimmerwx\model\Config;
use shimmerwx\model\User;
use shimmerwx\model\Token;
use shimmerwx\model\LoginToken;

class Page extends ControllerAbstract {
	private static function isMobile($ua) {
		if (strpos($ua, 'MicroMessage') !== FALSE) {
			return TRUE;
		}
		if (strpos($ua, 'Android') !== FALSE) {
			return TRUE;
		}
		if (strpos($ua, 'iOS') !== FALSE) {
			return TRUE;
		}
		if (strpos($ua, 'iPhone') !== FALSE) {
			return TRUE;
		}
		if (strpos($ua, 'iPad') !== FALSE) {
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * 登录页面
	 * 
	 * @api {get} /web/page/login 登录页面
	 * @apiName Login
	 * @apiGroup Web
	 * 
	 * @apiParam {String} redirect_uri 登录完成回调地址
	 */
	public static function loginAction($request, $response) {
		//如果已经处于登录状态，则直接跳转到目标页面
		$token = $request->cookie['wechat_token'];
		if ($token) {
			$info = Token::get($token);
			if ($info) {
				$response->assign('title', '完成登录');
				$response->assign('message', '登录成功');
				$response->assign('desc', '正在回到登录前页面');
				$response->assign('type', 'success');
				$response->assign('url', $request->get['redirect_uri']);
				$response->display('page/redirect');
				return;
			}
		}
		//如果是PC
		if (isset($request->get['pc']) && !self::isMobile($request->header['user-agent'])) {
			$response->assign('redirect_uri', $request->get['redirect_uri']);
			$response->assign('urlPrefix', Yesf::getBaseUri());
			$response->display('page/pclogin');
		} else {
			$wechat = Utils::getWeChat();
			$url = $wechat->getSnsLoginUrl(Config::getInstance()->read('redirect_uri'));
			$response->cookie([
				'name' => 'wechat_redirect_uri',
				'value' => $request->get['redirect_uri'],
				'expire' => 0,
				'path' => '/',
				'domain' => Config::getInstance()->read('cookie_domain'),
				'httponly' => TRUE
			]);
			$response->assign('title', '登录');
			$response->assign('message', '请稍候');
			$response->assign('desc', '正在登录');
			$response->assign('type', 'wait');
			$response->assign('url', $url);
			$response->display('page/redirect');
		}
	}
	/**
	 * PC
	 * 
	 * @api {get} /web/page/pc PC登录状态
	 * @apiName Pc
	 * @apiGroup Web
	 * 
	 * @apiParam {String} token LoginToken
	 * 
	 * @apiSuccess {String} token LoginToken
	 * @apiSuccess {Int} status 状态（0=需要新token，1=等待扫码，2=等待确认，3=完成）
	 */
	public static function pcAction($request, $response) {
		$login_token = $request->get['token'];
		if (!empty($login_token)) {
			$token_info = LoginToken::get($login_token);
			if (!$token_info) {
				unset($token_info);
			}
		}
		if (!isset($token_info)) {
			$login_token = LoginToken::create();
			$response->write(Utils::getWebApiResult([
				'token' => $login_token,
				'status' => 0
			]));
			return;
		}
		if ($token_info['status'] === 3) {
			$token = $token_info['wechat_token'];
			$response->cookie([
				'name' => 'wechat_token',
				'value' => $token,
				'expire' => 0,
				'path' => '/',
				'domain' => Config::getInstance()->read('cookie_domain'),
				'httponly' => TRUE
			]);
			$response->write(Utils::getWebApiResult([
				'token' => $login_token,
				'status' => 3
			]));
			return;
		}
		$response->write(Utils::getWebApiResult([
			'token' => $login_token,
			'status' => $token_info['status']
		]));
	}
	/**
	 * 手机扫码
	 * 
	 * @api {get} /web/page/mobileStatus 手机扫码状态
	 * @apiName MobileStatus
	 * @apiGroup Web
	 * 
	 * @apiParam {String} token LoginToken
	 * @apiParam {Int} status 状态（2=等待确认，3=完成）
	 */
	public static function mobileStatusAction($request, $response) {
		$status = intval($request->get['status']);
		$login_token = $request->get['token'];
		if (!empty($login_token)) {
			$token_info = LoginToken::get($login_token);
			if (!$token_info) {
				unset($token_info);
			}
		}
		if (!isset($token_info)) {
			return;
		}
		$token_info['status'] = $status;
		if ($status === 3) {
			$token_info['wechat_token'] = $request->cookie['wechat_token'];
		}
		LoginToken::set($login_token, $token_info);
		$response->write(Utils::getWebApiResult());
	}
	public static function mobileScanAction($request, $response) {
		$response->assign('token', $request->get['token']);
		$response->assign('urlPrefix', Yesf::getBaseUri());
		$response->display('page/mobilescan');
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
			$response->assign('desc', '正在登录');
			$response->assign('type', 'wait');
			$response->assign('url', $url);
			$response->display('page/redirect');
			return;
		}
		//检查unionid
		if (!$token['unionid']) {
			$response->assign('title', '提示');
			$response->assign('message', '登录失败');
			$response->assign('desc', 'unionid无效，此问题通常是因为我们配置有误，请联系我们解决此问题');
			$response->assign('type', 'warn');
			$response->display('page/notice');
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
		$response->assign('desc', '正在回到登录前页面');
		$response->assign('type', 'success');
		$response->assign('url', $request->cookie['wechat_redirect_uri']);
		$response->display('page/redirect');
	}
	//用户设置
	public static function settingAction($request, $response) {
		$response->display('page/setting');
	}
}