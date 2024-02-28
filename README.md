# sso with firebase/Jwt

基于 firebase/jwt 库实现的sso 单点登录，支持服务端生成token，登出、续期、加入黑名单
支持客户端跳转主站完成sso登录后跳回原站点，支持中间件auth。支持客户端登出、加入黑名单和续期。

## 安装

在composer.json 中添加 
~~~
    "repositories": {
        "yangweijie/jwt-sso": {
            "type": "vcs",
            "url": "https://git.tun.jsaix.cn/yangweijie/jwt-sso"
        }
    },
~~~

后通过composer安装包:

```bash
composer require yangweijie-jwt-sso/jwt-sso
```

## 使用

```php

参考 

```


## Credits

- [yangweijie](https://git.tun.jsaix.cn/yangweijie/jwt-sso)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
