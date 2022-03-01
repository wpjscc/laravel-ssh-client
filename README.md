
* [gitee](https://gitee.com/wpjscc/laravel-ssh-client)
* [github](https://github.com/wpjscc/laravel-ssh-client)
## 快速开始
```
git clone https://gitee.com/wpjscc/laravel-ssh-client
composer install
cp .env.example .env
php artisan serve --port=8088
php artisan websocket:serve

```

或者

```
docker run -it -p 8088:8000 -p 6001:6001 wpjscc/laravel-ssh-client

```

## 访问

http://127.0.0.1:8088

## 开发

```
npm install
npm run dev
```



## 其他
参考的: https://github.com/roke22/Laravel-ssh-client

Laravel-ssh-client 有两个小瑕疵
* 需要安装 ssh2 扩展
* 没有封装在 [laravel-websocket](https://github.com/beyondcode/laravel-websockets) 内

该项目做了几点优化
* 使用 github.com/phpseclib/phpseclib 这个库，建立 ssh 链接
* 集成在 laravel-websocket 内
* 支持密码和 ssh-key 登录

如果你想自定义打包docker镜像
```
docker login
docker build -t yourusername/laravel-ssh-client -f docker/Dockerfile
docker push yourusername/laravel-ssh-client
```
如果你想自定义打包私有镜像，比如阿里云,个人可以免费300个私有镜像
```
docker login yourdomain.com
docker build -t yourdomain.com/yourusername/laravel-ssh-client -f docker/Dockerfile
docker push -t yourdomain.com/yourusername/laravel-ssh-client
```
你可能发现了只是加了个域名前缀

如果你想自定义php镜像,比如在docker/Dockerfile中的`wpjscc/php:7.4.7-fpm-alpine` 想换成自己的，可以看这个仓库
[gitee docker-php](https://github.com/wpjscc/docker-php)
[github docker-php](https://gitee.com/wpjscc/docker-php)


## 注意

* 连接成功后会隐藏登录框，请确定密码和ssh key 是否正确
* 使用docker时，注意是运行在docker容器中的，其他内网ip容器访问不到,可以用外网ip或宿主主机ip调试
  * mac 可以用 docker.for.mac.host.internal 做为宿主主机IP
  * linux 找到 ifconfig | grep docker 获取ip

## 效果预览

![](/public/image/login.png)
![](/public/image/iterm.png)
![](/public/image/iterm-share.png)
![](/public/image/top.png)

