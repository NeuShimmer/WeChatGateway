<?php
/**
 * 应用管理
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
use \shimmerwx\model\App as AppModel;

class App extends ControllerAbstract {
	/**
	 * 获取应用列表
	 * 
	 * @api {get} /admin/app/list 获取应用列表
	 * @apiName GetAppList
	 * @apiGroup Admin
	 * 
	 * @apiSuccess {Object[]} list 应用列表
	 * @apiSuccess {Int} list.id 应用ID
	 * @apiSuccess {Int} list.type 应用类型
	 * @apiSuccess {String} list.name 应用名称
	 * @apiSuccess {String} list.appid 应用AppID
	 * @apiSuccess {String} list.appsecret 应用Secret
	 */
	public static function listAction($request, $response) {
		$response->write(Utils::getWebApiResult([
			'list' => AppModel::getInstance()->list([], 200)
		]));
	}
	/**
	 * 新增或修改单个应用
	 * 
	 * @api {post} /admin/app/save 新增或修改单个应用
	 * @apiName AppSave
	 * @apiGroup Admin
	 * 
	 * @apiParam {Int} id 应用ID，传入-1或不传则视为新建
	 * @apiParam {Int} type 应用类型
	 * @apiParam {String} name 应用名称
	 * @apiParam {String} appid 应用AppID
	 * @apiParam {String} appsecret 应用Secret
	 * 
	 * @apiSuccess {Int} id 应用ID
	 */
	public static function saveAction($request, $response) {
		if (!isset($request->post['id']) || $request->post['id'] == -1) {
			$id = AppModel::getInstance()->add([
				'type' => $request->post['type'],
				'name' => $request->post['name'],
				'appid' => $request->post['appid'],
				'appsecret' => $request->post['appsecret']
			]);
		} else {
			$id = intval($request->post['id']);
			AppModel::getInstance()->set([
				'type' => $request->post['type'],
				'name' => $request->post['name'],
				'appid' => $request->post['appid'],
				'appsecret' => $request->post['appsecret']
			], $id);
		}
		$response->write(Utils::getWebApiResult([
			'id' => $id
		]));
	}
	/**
	 * 删除单个应用
	 * 
	 * @api {post} /admin/app/del 删除单个应用
	 * @apiName AppDel
	 * @apiGroup Admin
	 * 
	 * @apiParam {Int} id 应用ID，传入-1或不传则视为新建
	 * 
	 * @apiSuccess {Int} id 应用ID
	 */
	public static function delAction($request, $response) {
		$id = intval($request->post['id']);
		AppModel::getInstance()->del($id);
		$response->write(Utils::getWebApiResult([
			'id' => $id
		]));
	}
}