<?php
return [
	'name' => 'WeChatGateway',
	'namespace' => 'shimmerwx',
	'charset' => 'utf-8',
	'bootstrap' => 'Bootstrap',
	'router' => [
		'type' => 'utf-8',
		'extension' => FALSE
	],
	'modules' => ['index', 'admin', 'web'],
	'module' => 'index',
	'view' => [
		'auto' => FALSE,
		'extension' => 'phtml'
	]
];