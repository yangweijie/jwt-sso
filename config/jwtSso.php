<?php

return [
    /**
     * 不需要检查的路由，如果使用jwt提供的默认中间件，可以对某些不用做检验的路由进行配置，例如登录等
     * 具体的逻辑可以效仿JWT提供的默认中间件
     * [
     *      ["GET", "/index/test"],
     *      ["**", "/test"]
     * ]
     *
     * 第一个填写请求方法('**'代表支持所有的请求方法)，第二个填写路由路径('/**'代表支持所有的路径)
     * 如果数组中存在["**", "/**"]，则默认所有的请求路由都不做jwt token的校验，直接放行，如果no_check_route为一个空数组，则
     * 所有的请求路由都需要做jwt token校验
     * 正则写法：["**", "/api/{name:.+}"]  支持模块化不做jwt token的校验，例如：/api/login/login
     */
    'no_check_route' => [
        ['**', '/**'],
    ],

    'login_type' => env('JWT_LOGIN_TYPE', 'mpop'), //  登录方式，sso为单点登录，mpop为多点登录

    /**
     * 单点登录自定义数据中必须存在uid的键值，这个key你可以自行定义，只要自定义数据中存在该键即可
     */
    'sso_key' => 'uid',

    /**
     * 只能用于Hmac包下的加密非对称算法，其它的都会使用公私钥
     */
    'secret' => env('JWT_SECRET', '1234567890ABCDEFGHIJKLMNOPQRST'),

    /**
     * JWT 权限keys
     * 对称算法: HS256, HS384 & HS512 使用 `JWT_SECRET`.
     * 非对称算法: RS256, RS384 & RS512 / ES256, ES384 & ES512 使用下面的公钥私钥，需要自己去生成.
     */
    'keys' => [
        'public' => env('JWT_PUBLIC_KEY'), // 公钥，例如：'file:///path/to/public/key'
        'private' => env('JWT_PRIVATE_KEY'), // 私钥，例如：'file:///path/to/private/key'

        /**
         * 你的私钥的密码。不需要密码可以不用设置
         */
        'passphrase' => env('JWT_PASSPHRASE'),
    ],

    'ttl' => env('JWT_TTL', 7200), // token过期时间，单位为秒

    /**
     * 支持的对称算法：HS256、HS384、HS512
     * 支持的非对称算法：RS256、RS384、RS512、ES256、ES384、ES512
     */
    'alg' => env('JWT_ALG', 'HS256'), // jwt的hearder加密算法

    /**
     * jwt使用到的缓存前缀
     * 建议使用独立的redis做缓存，这样比较好做分布式
     */
    'cache_prefix' => 'yangweijie:jwt',

    /**
     * 是否开启黑名单，单点登录和多点登录的注销、刷新使原token失效，必须要开启黑名单
     */
    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    /**
     * 黑名单的宽限时间 单位为：秒，注意：如果使用单点登录，该宽限时间无效
     */
    'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    /**
     * 签发者
     */
    'issued_by' => 'yangweijie/jwt-sso',

    'sites' => [
        'main' => [
            'domain' => env('JWT_MAIN_SITE_URL', ''),
        ],
        'subsite1' => [
            'domain' => env('APP_URL'),
        ],
    ],
    'current_site' => 'main',
];
