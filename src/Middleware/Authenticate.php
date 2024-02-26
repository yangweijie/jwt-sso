<?php

declare (strict_types = 1);

namespace yangweijie\jwt\Middleware;

use Closure;

use yangweijie\jwt\JwtSso;
use yangweijie\jwt\Traits\ResponseJson as ResponseJsonTrait;

class Authenticate
{
    use ResponseJsonTrait;

    protected $jwtServer;
    protected $jwtClient;

    public function __construct(JwtSso $jwtSso)
    {
        $this->jwtServer = $jwtSso->server();
        $this->jwtClient = $jwtSso->client();
    }

    public function handle($request, Closure $next)
    {
        if (! $this->shouldPassThrough($request)) {
            if (($res = $this->jwtCheck()) !== null) {
                return $res;
            }
        }

        return $next($request);
    }

    /*
     * jwt验证
     */
    protected function jwtCheck()
    {
        $authorization = request()->header('Authorization');
        if (!$authorization) {
            return $this->error(__('token不能为空'), JwtSso::TOKEN_ERROR_CODE);
        }

        $authorizationArr = explode(' ', $authorization);
        if (count($authorizationArr) != 2) {
            return $this->error(__('token不能为空'), JwtSso::TOKEN_ERROR_CODE);
        }
        if ($authorizationArr[0] != 'Bearer') {
            return $this->error(__('token格式错误'), JwtSso::TOKEN_ERROR_CODE);
        }

        $accessToken = $authorizationArr[1];
        if (!$accessToken) {
            return $this->error(__('token不能为空'), JwtSso::TOKEN_ERROR_CODE);
        }

        if (count(explode('.', $accessToken)) <> 3) {
            return $this->error(__('token格式错误'), JwtSso::TOKEN_ERROR_CODE);
        }

        try {
            $this->jwtServer->parseToken($accessToken);
        } catch(\Exception $e) {
            return $this->error(__('token已失效'), JwtSso::TOKEN_ERROR_CODE);
        }
        if($this->jwtServer->hasTokenBlack($accessToken, config('jwt.current_site', 'main'))){
            return $this->error(__('token已失效'), JwtSso::TOKEN_ERROR_CODE);
        }

        return null;
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        $excepts = array_merge(config('jwt.authenticate_excepts', []), [
            'system.set-lang',
            'passport.passkey',
            'passport.captcha',
            'passport.login',
            'passport.loginPost',
            'passport.refresh-token',
            'attachment.download',
        ]);

        return collect($excepts)
            ->contains(function ($except) {
                $requestUrl = \Route::currentRouteName();
                return ($except == $requestUrl);
            });
    }

    /**
     * 格式化路由标识
     */
    protected function formatRouteSlug($slug = '')
    {
        return RouteService::formatRouteSlug($slug);
    }

}
