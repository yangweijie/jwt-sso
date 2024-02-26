<?php

namespace yangweijie\jwt;

use Illuminate\Support\Facades\Http;

class JwtSsoClient
{
    private $main_host;

    public $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->main_host = $config['sites']['main']['domain'];
    }

    public function parseToken($token): \Illuminate\Http\Client\Response
    {
        $url = $this->main_host.'/sso/parseToken';
        $response = Http::acceptJson()->withToken($token)->post($url, ['site' => $this->config['current_site']]);

        return $response;
    }

    public function logout($token)
    {
        $url = $this->main_host.'/sso/logout';
        $response = Http::acceptJson()->withToken($token)->post($url, ['site' => $this->config['current_site']]);

        return $response;
    }

    public function refreshToken($token)
    {
        $url = $this->main_host.'/sso/refreshToken';
        $response = Http::acceptJson()->withToken($token)->post($url, ['site' => $this->config['current_site']]);

        return $response;
    }

    public function redirectTo($loginTo)
    {
        $domain = $this->config['sites']['main']['domain'] ?? '';

        return sprintf('%s?loginTo=%s&site=%s&scheme=%s', $domain.'/login', $loginTo, $this->config['current_site'], request()->getScheme());
    }
}
