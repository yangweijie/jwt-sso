<?php

namespace yangweijie\jwt;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JwtSsoServer
{
    private array $config;
    private $request;
    private $publicKey;
    private $privateKey;

    public function __construct($config)
    {
        $this->config = $config;
        if(in_array($config['alg'], ['HS256','HS384', 'HS512'])){
            if(empty($config['secret'])){
                throw new \Exception('secrect未配置');
            }
            $this->publicKey = $config['secret'];
            $this->privateKey = $config['secret'];
        }else{
            if(!file_exists($config['keys']['public'])){
                throw new \Exception('公钥文件不存在');
            }
            if(!file_exists($config['keys']['private'])){
                throw new \Exception('私钥文件不存在');
            }
            $this->publicKey = file_get_contents($config['keys']['public']);
            $this->privateKey = file_get_contents($config['keys']['private']);
        }
        $this->request = request();
    }

    // 生成token
    public function getToken($userData, $site='main', $head = null){
        $config = $this->config;
        $payload = [
            'sub' => 'sso login',
            'iss' => env('APP_URL'),
            'exp' => now()->addSeconds($config['ttl'])->unix(),
            'nbf' => time(),
            'iat' => time(),
            'jti' => $this->getCacheKey($userData, $config['sso_key'], $config['cache_prefix'], $config['login_type'], $site),
            'aud' => env('APP_URL'),
        ];
        Log::info('登录时payload');
        Log::info($payload);
        $jwt = JWT::encode($payload, $this->privateKey, $config['alg'], $site, $head);
        Log::info('缓存过期时间:'. $config['ttl']);
        Cache::put($payload['jti'], $payload['iat'], $config['ttl']);
        Cache::put($payload['jti'].'_data', $userData, $config['ttl']);
        return $jwt;
    }

    public function parseToken($token){
        $decoded = JWT::decode($token, new Key($this->publicKey, $this->config['alg']));
        return get_object_vars($decoded);
    }

    public function setConfig($value)
    {
        if($value && is_array($value)){
            $this->config = array_merge($this->config, $value);
        }
    }

    // 添加token 到 黑名单
    public function addTokenBlack($token, $site, $addByCreateTokenMethod = false)
    {
        $config = $this->config;
        if($config['blacklist_enabled']){
            try{
                $userData = $this->parseToken($token);
            }catch (\Exception $e){
                return true;
            }
            $loginType = $this->config['login_type'];
            $cacheKey = $userData['jti'];
            if($loginType === 'sso'){
                // 创建token 来刷新，用旧token签发时间-1，刷新token 用当前时间 -1
                $validUntil = $addByCreateTokenMethod? $userData['iat'] - 1 : time() - 1;
            }else{
                $blacklistGracePeriod = $config['blacklist_grace_period'];
                $validUntil = $userData['iat'] + $blacklistGracePeriod;
            }
            $leftSeconds = now()->diffInSeconds(Carbon::parse($userData['exp']));
            // 未过期
            if($leftSeconds > 0){
                Cache::put($cacheKey, $validUntil, $leftSeconds);
            }
            return true;
        }
        return false;
    }

    public function getRequestToken()
    {
        $token = $this->request->header('Authorization', $this->request->input('token', ''));
        $token = str_replace('Bearer ', '', $token);
        return $token;
    }

    // 刷新token
    public function refreshToken($site, $token = null)
    {
        if(!$token){
            $token = $this->getRequestToken();
        }
        $userData = $this->parseToken($token);
        if($this->config['login_type'] == 'sso'){
            $uid = last(explode('_', $userData['jit']));
        }else{
            $uid = 0;
        }
        $this->logout($site, $token);
        $newData = [];
        foreach ($userData as $key => $value){
            if(!in_array($key, ['exp', 'nbf', 'iat', 'jti', 'iss', 'exp'])){
                $newData[$key] = $value;
            }
        }
        return $this->getToken($newData);
    }

    // 是否在黑名单中
    public function hasTokenBlack($token, $site)
    {
        try {
            $userData = $this->parseToken($token);
        }catch (\Exception $e){
            Log::error('tokenBlackParseError');
            return true;
        }
        if($this->config['blacklist_enabled']){
            $cacheKey = $userData['jti'];
            if(Cache::has($cacheKey)){
                $cacheValue = Cache::get($cacheKey);
                Log::info([
                    'login_type'=>$this->config['login_type'],
                        'cache_exp'=>$cacheValue,
                        'userData'=>$userData,
                    ]
                );
                // sso 缓存过期时间小于签发时间  mmp 当前时间大于等于缓存过期时间
                return $this->config['login_type'] == 'sso'? $cacheValue < $userData['iat'] : time() < $cacheValue;
            }
        }
        return false;
    }

    // 登出
    public function logout($site, $token = null){
        if(!$token){
            $token = $this->getRequestToken();
        }
        return $this->addTokenBlack($token, $site, true);
    }

    // 获取缓存的key
    public function getCacheKey($userData, $sso_key, $cache_prefix, $login_type, $site){
        if($login_type === 'sso'){
            if(isset($userData[$sso_key])){
                return sprintf('%s_%s_%s', $cache_prefix, $site, $userData[$sso_key]);
            }else{
                throw new Exception("用户数据里 {$sso_key}键的值不存在:");
            }
        }else if($login_type === 'mpop'){
            $random = \Illuminate\Support\Str::random();
            return sprintf('%s_%s_%s', $cache_prefix, $site, $random);
        }else{
            throw new Exception("不支持的登录方式: {$login_type}");
        }
    }

    public function redirectTo($site, $token, $scheme, $loginTo)
    {
        $domain = config("jwt.sites.{$site}.domain", '');
        if(!$domain){
            throw new Exception("站点 {$site} 未配置");
        }
        return sprintf('%s://%s%s?token=%s', $scheme, $domain, $loginTo, $token);
    }
}
