
* [gitee](https://gitee.com/wpjscc/laravel-ssh-client)
* [github](https://github.com/wpjscc/laravel-ssh-client)
## 启动
```
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

## 效果预览

![](/public/image/login.png)
![](/public/image/iterm.png)
![](/public/image/iterm-share.png)
![](/public/image/top.png)

