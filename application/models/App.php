<?php
/**
 * App应用表
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

class App extends ModelAbstract {
	protected $table_name = 'app';
	protected $primary_key = 'id';
}