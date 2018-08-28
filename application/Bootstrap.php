<?php
use yesf\Yesf;
use yesf\Constant;
class Bootstrap {
	public function run() {
		Yesf::getLoader()->addPsr4('shimmerwx\\library\\', APP_PATH . 'library');
	}
}