<?php
/**
 * WEB接口
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

class Api extends ControllerAbstract {
	/**
	 * 初始化JS-SDK
	 * 
	 * @api {get} /web/api/init 初始化JS-SDK
	 * @apiName Init
	 * @apiGroup Public
	 * 
	 * @apiParam {String} url 页面的完整地址
	 * @apiParam {Int} id 指定应用ID（可选，-1代表默认应用，与后台对应）
	 * 
	 * @apiSuccess {String} appid AppID
	 * @apiSuccess {String} timestamp 时间戳
	 * @apiSuccess {String} noncestr 随机字符串
	 * @apiSuccess {String} signature 签名
	 */
	public static function initAction($request, $response) {
		$id = isset($request->get['id']) ? intval($request->get['id']) : -1;
		$config = Utils::getWeChatConfig($id);
		if (!$config) {
			$response->write(Utils::getWebApiResult([
				'error' => '应用不存在'
			]));
			return;
		}
		$wechat = Utils::getWeChat($id);
		$url = $request->get['url'];
		$randStr = Utils::getRandStr(6);
		$time = time();
		$result = [
			'appid' => $config['appid'],
			'timestamp' => $time,
			'noncestr' => $randStr,
			'signature' => $wechat->getJsSign($url, $randStr, $time)
		];
		$response->write(Utils::getWebApiResult($result));
	}
	/**
	 * 通过AuthorizeCode，获取用户信息并生成Token
	 * 用于小程序、App等非网页渠道登录
	 * 
	 * @api {get} /web/api/token 通过AuthorizeCode，获取用户信息并生成Token
	 * @apiName GetToken
	 * @apiGroup Public
	 * 
	 * @apiParam {String} code AuthorizeCode
	 * @apiParam {Int} id 指定应用ID（与后台对应）
	 * 
	 * @apiSuccess {Int} id 用户在系统内的ID
	 * @apiSuccess {String} token Token
	 * @apiSuccess {String} session_key 小程序使用的session_key
	 * @apiSuccess {String} app_openid 当前程序独有的OpenID
	 * @apiSuccess {String} openid 用户的OpenID
	 * @apiSuccess {String} unionid 用户的UnionID
	 * @apiSuccess {String} nickname 用户昵称
	 * @apiSuccess {String} headimgurl 用户头像
	 * @apiSuccess {Int} sex 用户性别，值为1时是男性，值为2时是女性，值为0时是未知
	 */
	public static function tokenAction($request, $response) {
		$wechat = Utils::getWeChat($request->get['id']);
		if (!$wechat) {
			$response->write(Utils::getWebApiResult([
				'error' => '应用不存在'
			]));
			return;
		}
		$code = $request->get['code'];
		if ($wechat->isMp()) {
			$token = $wechat->getSnsAccessToken($code);
			//AuthorizeCode无效
			if (!$token['access_token']) {
				$response->write(Utils::getWebApiResult([
					'error' => 'AuthorizeCode无效'
				]));
				return;
			}
			//获取用户的基本信息
			$user = $wechat->getSnsUserInfo($token['access_token'], $token['openid']);
			$u = User::getInstance()->get([
				'unionid' => $user['unionid']
			]);
			if ($u) {
				//更新用户信息
				User::getInstance()->set([
					'nickname' => $user['nickname']
				], $u['id']);
				$user['id'] = $u['id'];
			} else {
				$user['id'] = User::getInstance()->add([
					'openid' => '',
					'unionid' => $user['unionid'],
					'nickname' => $user['nickname'],
					'is_follow' => 0,
					'receive_push' => 0
				]);
			}
			$user['app_openid'] = $token['openid'];
		} else {
			//小程序
			$token = $wechat->getSnsSession($code);
			if (!$token['session_key']) {
				$response->write(Utils::getWebApiResult([
					'error' => 'Code无效'
				]));
				return;
			}
			if (!$token['unionid']) {
				$response->write(Utils::getWebApiResult([
					'error' => '请在微信开发者平台将此应用与主应用绑定至同一开发者账号'
				]));
				return;
			}
			//获取用户的基本信息
			$u = User::getInstance()->get([
				'unionid' => $token['unionid']
			]);
			if (!$u) {
				$user = [
					'openid' => '',
					'unionid' => $token['unionid'],
					'nickname' => '',
					'is_follow' => 0,
					'receive_push' => 0
				];
				$user['id'] = User::getInstance()->add($user);
			} else {
				$user = $u;
			}
			$user['session_key'] = $token['session_key'];
			$user['app_openid'] = $token['openid'];
		}
		//添加token信息
		$token = Token::create($user);
		$user['token'] = $token;
		$response->write(Utils::getWebApiResult($user));
	}
	/**
	 * 获取用户登录状态
	 * 如果用户已登录，则会返回详细信息
	 * 
	 * @api {get} /web/api/me 获取用户登录状态
	 * @apiName GetMe
	 * @apiGroup Public
	 * 
	 * @apiParam {String} token 系统生成的Token，可选，默认从Cookie中读取
	 * 
	 * @apiSuccess {Boolean} is_login 是否已经登录
	 * @apiSuccess {String} openid 用户的OpenID
	 * @apiSuccess {String} unionid 用户的UnionID
	 * @apiSuccess {String} nickname 用户昵称
	 * @apiSuccess {String} headimgurl 用户头像
	 * @apiSuccess {Int} sex 用户性别，值为1时是男性，值为2时是女性，值为0时是未知
	 */
	public static function meAction($request, $response) {
		if (isset($request->get['token']) && !empty($request->get['token'])) {
			$token = $request->get['token'];
		}
		if (isset($request->cookie['wechat_token']) && !empty($request->cookie['wechat_token'])) {
			$token = $request->cookie['wechat_token'];
		}
		if (!isset($token)) {
			$response->write(Utils::getWebApiResult([
				'is_login' => FALSE
			]));
			return;
		}
		$info = Token::get($token);
		if (!$info) {
			$response->write(Utils::getWebApiResult([
				'is_login' => FALSE
			]));
			return;
		}
		//从数据库中读取
		$user = User::getInstance()->get($info['id']);
		$info['is_login'] = TRUE;
		$response->write(Utils::getWebApiResult(array_merge($info, $user)));
	}
	/**
	 * 获取媒体文件
	 * @api {get} /web/api/media 获取媒体文件
	 * @apiName GetMedia
	 * @apiGroup Public
	 * 
	 * @apiParam {Int} id 指定应用ID（与后台对应）
	 * @apiParam {String} media_id 通过JSSDK上传接口得到的媒体文件ID
	 */
	public static function mediaAction($request, $response) {
		$media_id = $request->get['media_id'];
		$id = isset($request->get['id']) ? intval($request->get['id']) : -1;
		$config = Utils::getWeChatConfig($id);
		if (!$config) {
			$response->write(Utils::getWebApiResult([
				'error' => '应用不存在'
			]));
			return;
		}
		$wechat = Utils::getWeChat($id);
		$retry_count = 0;
		while (TRUE) {
			list($content_type, $body) = $wechat->getMedia($media_id);
			if (strpos($content_type, 'application/json') === 0) {
				$response->header('Content-Type', 'application/json; charset=UTF-8');
				$result = json_decode($body, 1);
				if ($result['errcode'] == 40001) {
					//临时失败
					if ($retry_count++ >= 10) {
						$response->write(Utils::getWebApiResult([
							'error' => '请重试'
						]));
						return;
					}
				} else {
					$response->write(Utils::getWebApiResult([
						'error' => $result['errmsg']
					]));
					return;
				}
			} else {
				break;
			}
		}
		$response->header('Content-Type', $content_type);
		$response->write($body);
	}
}