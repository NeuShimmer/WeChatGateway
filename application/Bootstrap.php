<?php
use yesf\Yesf;
use yesf\Constant;
use yesf\library\Plugin;
use shimmerwx\model\Config;
class Bootstrap {
	public function run() {
		Yesf::setBaseUri(Yesf::getInstance()->getConfig('urlPrefix'));
		Yesf::getLoader()->addPsr4('shimmerwx\\library\\', APP_PATH . 'library');
		Plugin::register('beforeDispatcher', ['\\shimmerwx\\library\\PluginHandler', 'onBeforeDispatcher']);
		Config::getInstance();
	}
}