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
	protected static $_table_name = 'config';
	protected static $_primary_key = 'id';
	protected $cache = NULL;
	public function __construct() {
		$this->cache = \Swoole\Table(32);
		$this->cache->column('value', \Swoole\Table::TYPE_STRING, 255);
		parent::__construct();
	}
	/**
	 * 获取单项配置
	 * 
	 * @access public
	 * @param string $name
	 * @return string
	 */
	public function read($name) {
		$rs = $this->cache->get($name, 'value');
		if ($rs !== FALSE) {
			return $rs;
		}
		$result = $this->get($name, ['value']);
		$this->cache->set($name, [
			'value' => $result['value']
		]);
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
		$this->cache[$name] = $value;
		$this->cache->set($name, [
			'value' => $value
		]);
	}
}