<?php
use yesf\Yesf;
use yesf\Constant;
use yesf\library\Plugin;
use shimmerwx\model\Config;
use shimmerwx\library\PluginHandler;
class Bootstrap {
	public function run() {
		Yesf::getLoader()->addPsr4('shimmerwx\\library\\', APP_PATH . 'library');
		Plugin::register('beforeDispatcher', [PluginHandler::class, 'onBeforeDispatcher']);
		Plugin::register('workerStart', [PluginHandler::class, 'onWorkerStart']);
		Config::getInstance();
	}
}