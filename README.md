# laravel_GatewayWorker
在laravel框架中使用基于workerman开发的GatewayWorker组件从而使用长连接功能操作DEMO

1、新建Laravel项目

2、`composer require workerman/workerman >=3.1.8`

3、`composer require workerman/gateway-worker`

4、`composer require workerman/gatewayclient`

5、编辑启动文件 /app/ChatService 、/start.php 、/start_for_win.bat 

    /app/ChatService 为启动配置文件
    /start.php 为linux系统的启动文件
    /start+for_win.bat 为windows系统的启动文件


6、新建一个控制器 `php artisan make:controller IndexController`

    在新建的控制器中加入 use GatewayClient\Gateway;
    然后就可以使用 Lib/Gateway类 提供的接口进行长链接操作了
    示例见代码
    
7、把 GatewayWorker 与 Laravel 相结合的好处是,把laravel接收到的所有请求(post/get/put/等请求)均可以通过长链接推送给客户端，而workerman的长链接服务器，只是作消息的推送处理，这样的话利于业务逻辑与长链接服务器的相结合，而不需要把所有的业务逻辑都写在 `/app/ChatService/Events.php` 的事件回调中(如果在事件回调中写业务逻辑的话,那么laravel框架的绝大多数功能都不能正常调用)

8、编写测试demo  
    
    1)编写路由 web.php
    
        Route::match(['post', 'get'], 'index/index/{uid}/{to_uid}', 'IndexController@client');
        
        Route::match(['post', 'get'], 'index/client/{uid}/{to_uid}', 'IndexController@client');
        
        Route::match(['post', 'get'], 'index/bind', 'IndexController@bind');
        
        Route::match(['post', 'get'], 'index/send', 'IndexController@send');

    2)编写视图  /resources/views/client.blade.php 文件(注意修改视图模版中链接websocket服务器的端口,本机测试改为127.0.0.1,它机测试填写它机ip)
    
    3)测试效果 在浏览器开启两个窗口(注意确认已开启websocket对外的端口号,此处demo中应服务器应放行7272端口，测试前请开启服务端的websocket服务器)
        
        http://域名/index/index/111/222
        http://域名/index/index/222/111
    
   
9、websocket服务器相关操作命令
    
   
    1）以debug（调试）方式启动
    
    php start.php start
    
    2）以daemon（守护进程）方式启动
    
    php start.php start -d
    
    3）停止
    
    php start.php stop
    
    4）重启
    
    php start.php restart
    
    5）平滑重启
    
    php start.php reload
    
    6）查看状态
    
    php start.php status    
    
    
10、Thinkphp使用GatewayWorker 使用方法与此基本一致，然后复制修改 `/app/ChatService 、/start.php 、/start_for_win.bat ` 这几个文件的路径问题即可

 ### 其他说明
 
 GatewayWorker手册 http://doc2.workerman.net/326107
 
 workerman官方下载地址 https://www.workerman.net/download
 
