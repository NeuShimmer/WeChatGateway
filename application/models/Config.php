<?php
/**
 * Config配置表
 * 
 * @author ShuangYa
 * @package WeChatGateway
 * @category Model
 * @link https://shimmer.neusoft.edu.cn/
 * @copyright Copyright (c) 2018 Shimmer Network Studio
 * @license https://github.com/NeuShimmer/WechatGateway/blob/master/LICENSE
 */
namespace shimmerwx\model;
use yesf\library\ModelAbstract;

class Config extends ModelAbstract {
	protected $table_name = 'config';
	protected $primary_key = 'id';
	/**
	 * 获取单项配置
	 * 
	 * @access public
	 * @param string $name
	 * @return string
	 */
	public function read($name) {
		$result = $this->get($name, ['value']);
		return $result['value'];
	}
	/**
	 * 写入配置
	 * 
	 * @access public
	 * @param string $name
	 * @param string $value
	 */
	public function save($name, $value) {
		$this->set([
			'value' => $value
		], $name);
	}
}