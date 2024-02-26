<?php

namespace yangweijie\jwt;

class JwtSso
{
    private JwtSsoClient $client;
    private JwtSsoServer $server;
    const TOKEN_ERROR_CODE = 419;

    public function __construct()
    {
        $config = config('jwt');
        $this->client = new JwtSsoClient($config) ;
        $this->server = new JwtSsoServer($config);
    }

    public function client(){
        return $this->client;
    }

    public function server()
    {
        return $this->server;
    }
}
