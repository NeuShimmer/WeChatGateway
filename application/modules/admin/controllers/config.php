<?php
/**
 * 配置管理
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
use \shimmerwx\library\WeChat;
use \shimmerwx\model\Config as ConfigModel;
use \shimmerwx\model\App as AppModel;

class Config extends ControllerAbstract {
	const CONFIG_LIST = [
		['appid', '默认应用AppID'],
		['appsecret', '默认应用Secret'],
		['msg_token', '微信消息推送Token'],
		['redirect_uri', '登录完成重定向地址'],
		['cookie_domain', 'Cookie域名']
	];
	/**
	 * 获取配置列表
	 * 
	 * @api {get} /admin/config/list 获取配置列表
	 * @apiName GetConfigList
	 * @apiGroup Admin
	 * 
	 * @apiSuccess {Object[]} list 配置列表
	 * @apiSuccess {String} list.id 配置项ID
	 * @apiSuccess {String} list.name 配置项名称
	 * @apiSuccess {String} list.value 配置项内容
	 */
	public static function listAction($request, $response) {
		$list = [];
		foreach (self::CONFIG_LIST as $v) {
			$list[] = [
				'id' => $v[0],
				'name' => $v[1],
				'value' => ConfigModel::getInstance()->read($v[0])
			];
		}
		$response->write(Utils::getWebApiResult([
			'list' => $list
		]));
	}
	/**
	 * 保存单个配置
	 * 
	 * @api {post} /admin/config/save 保存单个配置
	 * @apiName ConfigSave
	 * @apiGroup Admin
	 * 
	 * @apiParam {String} id 配置项ID
	 * @apiParam {String} value 配置项内容
	 */
	public static function saveAction($request, $response) {
		ConfigModel::getInstance()->save($request->post['id'], $request->post['value']);
		$response->write(Utils::getWebApiResult());
	}
	/**
	 * 修改密码
	 * 
	 * @api {post} /admin/config/password 修改密码
	 * @apiName ConfigPassword
	 * @apiGroup Admin
	 * 
	 * @apiParam {String} password 密码
	 */
	public static function passwordAction($request, $response) {
		$password = hash('sha256', $request->post['password']);
		ConfigModel::getInstance()->save('admin_password', $password);
		$response->write(Utils::getWebApiResult([
			'password' => $password
		]));
	}
}