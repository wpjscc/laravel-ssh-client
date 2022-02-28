
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
