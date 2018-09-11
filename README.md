# WeChat Gateway

[![GitHub release](https://img.shields.io/github/release/NeuShimmer/WeChatGateway.svg)](https://github.com/NeuShimmer/WeChatGateway/releases)
[![license](https://img.shields.io/github/license/NeuShimmer/WeChatGateway.svg)](https://github.com/NeuShimmer/WeChatGateway/blob/master/LICENSE)

微信的一些API封装

接口文档使用[APIDoc](http://apidocjs.com/)生成

### 安装说明

* 环境需求：PHP 7.0+，Swoole 4.0+，Swoole Serialize，Redis，MySQL，Composer

* 下载源代码并解压

* 进入源代码目录，执行`composer install`

* 将`database.sql`中的内容导入MySQL

* 修改`application/Config.ini`中相应配置（包括静态文件地址、MySQL连接、Redis连接、Swoole相关配置等）

* 修改`application/Bootstrap.php`中的`Yesf::setBaseUri('/wechat/');`部分为您实际部署的路径

* 修改WeUI相关文件路径（见下文）

* 进入后台（`/index/admin/index`），输入默认密码`admin`，进行进一步配置

### 修改WeUI相关文件路径

* 用户可选择将WeUI文件下载至本地，或直接使用在线文件：

```
https://res.wx.qq.com/open/libs/weuijs/1.1.4/weui.min.js
https://res.wx.qq.com/open/libs/weui/1.1.3/weui.min.css
```

* 修改以下几处文件中的相关地址：

```
application/modules/web/views/page/redirect.phtml
application/modules/web/views/page/setting.phtml
```