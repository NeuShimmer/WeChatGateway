<?php
/**
 * 接收微信消息事件
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
use \shimmerwx\model\Token;

class Wechat extends ControllerAbstract {
	public static function pushAction($request, $response) {
		$appid = Config::getInstance()->read('appid');
		$token = Config::getInstance()->read('msg_token');
		$time = $request->get['timestamp'];
		$nonce = $request->get['nonce'];
		if ($request->server['request_method'] === 'GET') {
			//初始验证请求
			$arr = [$token, $time, $nonce];
			sort($arr, SORT_STRING);
			if (sha1(implode('', $arr)) === $request->get['signature']) {
				$response->write('success');
			} else {
				$response->write('fail');
			}
			return;
		} else {
			libxml_disable_entity_loader(true);
			$xml = $request->rawContent();
			$wechat = Utils::getWeChat();
			//加密太麻烦，没意义，不写了
			try {
				$parser = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
			} catch (\Throwable $e) {
				$response->write('fail');
			}
			$msg_type = strval($parser->MsgType);
			switch ($msg_type) {
				case 'event':
					//事件推送
					$event = strval($parser->Event);
					$method_name = 'onEvent' . ucfirst($event);
					if (method_exists(__CLASS__, $method_name)) {
						$response->write(call_user_func([self, $method_name], $parser));
						return;
					}
					$response->write('success');
					return;
				case 'text':
					//文本消息
					$response->write(self::onMessageText(
						strval($parser->FromUserName),
						strval($parser->Content),
						strval($parser->MsgId)));
					return;
				case 'image':
					//图片消息
					$response->write(self::onMessageImage(
						strval($parser->FromUserName),
						strval($parser->PicUrl),
						strval($parser->MediaId),
						strval($parser->MsgId)));
					return;
				case 'voice':
					$response->write(self::onMessageVoice(
						strval($parser->FromUserName),
						strval($parser->MediaId),
						strval($parser->Format),
						isset($parser->Recognition) ? strval($parser->Recognition) : '',
						strval($parser->MsgId)));
					return;
				case 'video':
				case 'shortvideo':
					$response->write(self::onMessageVideo(
						strval($parser->FromUserName),
						strval($parser->MediaId),
						strval($parser->ThumbMediaId),
						strval($parser->MsgId)));
					return;
				case 'location':
					$response->write(self::onMessageLocation(
						strval($parser->FromUserName),
						strval($parser->Location_X),
						strval($parser->Location_Y),
						strval($parser->Scale),
						strval($parser->Label),
						strval($parser->MsgId)));
					return;
				case 'link':
					$response->write(self::onMessageLink(
						strval($parser->FromUserName),
						strval($parser->Title),
						strval($parser->Description),
						strval($parser->Url),
						strval($parser->MsgId)));
					return;
				default:
					$response->write('success');
					return;
			}
		}
	}
	//事件推送：订阅
	protected static function onEventSubscribe($data) {
		$openid = strval($data->FromUserName);
		//获取用户信息，包括unionid
		$user = $wechat->getUserInfo($openid);
		$u = User::getInstance()->get([
			'unionid' => $user['unionid']
		]);
		//已存在用户则只开启消息订阅
		if ($u) {
			//更新用户信息
			User::getInstance()->set([
				'nickname' => $user['nickname'],
				'receive_push' => 1
			], $u['id']);
		} else {
			User::getInstance()->add([
				'openid' => $user['openid'],
				'unionid' => $user['unionid'],
				'nickname' => $user['nickname'],
				'receive_push' => 1
			]);
		}
		return 'success';
	}
	//事件推送：取消订阅
	protected static function onEventUnsubscribe($data) {
		$openid = strval($data->FromUserName);
		$u = User::getInstance()->get([
			'openid' => $user['openid']
		]);
		//取消订阅
		if ($u) {
			//更新用户信息
			User::getInstance()->set([
				'receive_push' => 0
			], $u['id']);
		}
		return 'success';
	}
	/**
	 * 文本消息事件
	 * 
	 * @param string $openid 来源用户OpenID
	 * @param string $content 消息内容
	 * @param string $msg_id 消息ID
	 */
	protected static function onMessageText($openid, $content, $msg_id) {
		return 'success';
	}
	/**
	 * 图片消息事件
	 * 
	 * @param string $openid 来源用户OpenID
	 * @param string $url 图片地址
	 * @param string $media_id 媒体文件ID
	 * @param string $msg_id 消息ID
	 */
	protected static function onMessageImage($openid, $url, $media_id, $msg_id) {
		return 'success';
	}
	/**
	 * 语音消息事件
	 * 
	 * @param string $openid 来源用户OpenID
	 * @param string $media_id 媒体文件ID
	 * @param string $format 语音文件格式
	 * @param string $text 语音转文字结果（如果开启了此功能的话）
	 * @param string $msg_id 消息ID
	 */
	protected static function onMessageVoice($openid, $media_id, $format, $text, $msg_id) {
		return 'success';
	}
	/**
	 * 视频和小视频消息事件
	 * 
	 * @param string $openid 来源用户OpenID
	 * @param string $media_id 媒体文件ID
	 * @param string $thumb_id 缩略图文件ID
	 * @param string $msg_id 消息ID
	 */
	protected static function onMessageVideo($openid, $media_id, $thumb_id, $msg_id) {
		return 'success';
	}
	/**
	 * 地址消息事件
	 * 
	 * @param string $openid 来源用户OpenID
	 * @param string $content 消息内容
	 * @param string $x 地理位置维度
	 * @param string $y 地理位置经度
	 * @param string $scale 地图缩放大小
	 * @param string $label 地理位置信息
	 * @param string $msg_id 消息ID
	 */
	protected static function onMessageLocation($openid, $x, $y, $scale, $label, $msg_id) {
		return 'success';
	}
	/**
	 * 链接消息事件
	 * 
	 * @param string $openid 来源用户OpenID
	 * @param string $title 消息标题
	 * @param string $desc 消息描述
	 * @param string $url 消息链接
	 * @param string $msg_id 消息ID
	 */
	protected static function onMessageLink($openid, $title, $desc, $url, $msg_id) {
		return 'success';
	}
}