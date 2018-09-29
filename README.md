# WeChat Gateway

[![GitHub release](https://img.shields.io/github/release/NeuShimmer/WeChatGateway.svg)](https://github.com/NeuShimmer/WeChatGateway/releases)
[![license](https://img.shields.io/github/license/NeuShimmer/WeChatGateway.svg)](https://github.com/NeuShimmer/WeChatGateway/blob/master/LICENSE)

微信的一些API封装

接口文档使用[APIDoc](http://apidocjs.com/)生成

### 安装说明

* 环境需求：PHP 7.0+，Swoole 4.0+，Swoole Serialize，Redis，MySQL，Composer

* 运行`composer create-project shimmer/wechat-gateway`

* 将`database.sql`中的内容导入MySQL

* 修改`application/Config/env.ini`中相应配置（包括静态文件地址、MySQL连接、Redis连接、Swoole相关配置等）

* 进入后台（`/index/admin/index`），输入默认密码`admin`，进行进一步配置
