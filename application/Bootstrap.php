<?php
use yesf\Yesf;
use yesf\Constant;
use yesf\library\Plugin;
class Bootstrap {
	public function run() {
		Yesf::getLoader()->addPsr4('shimmerwx\\library\\', APP_PATH . 'library');
		Plugin::register('beforeDispatcher', ['\\shimmerwx\\library\\PluginHandler', 'onBeforeDispatcher']);
	}
}