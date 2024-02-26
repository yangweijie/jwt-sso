<?php
namespace yangweijie\jwt\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Mockery\Exception;
use yangweijie\jwt\JwtSso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class SsoController extends Controller
{
    public $client;
    public $server;

    public function __construct(JwtSso $jwtSso)
    {
        $this->server = $jwtSso->server();
        $this->client = $jwtSso->client();
    }

    // token获取用户信息
    public function parseToken(Request $request){
        $error = '';
        try {
            $token = $this->server->getRequestToken();

            $payload = $this->server->parseToken($token);
            Log::info('payload');
            Log::info($payload);
            if($this->server->hasTokenBlack($token, $request->input('site', 'main'))){
                throw new Exception($error = "{$token} 无效或已过期1");
            }
            $userData = Cache::get($payload['jti'].'_data', null);
        }catch (\Exception $e){
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $error = "{$token} 无效或已过期2";
            Log::error($error);
        }
        if($error){
            return $this->error($error);
        }else{
            return $this->success(['payload'=>$payload, 'userData'=>$userData]);
        }
    }

    public function logout(Request $request)
    {
        $success = false;
        try {
            $error = '';
            $token = $this->server->getRequestToken();
            $success = $this->server->logout($request->input('site', 'main'), $token);
        }catch (\Exception $e){
            $error = "{$token} 无效或已过期";
            Log::error($error);
        }
        if($error){
            return $this->error($error);
        }else{
            if($success){
                return $this->success();
            }else{
                return $this->error('登出失败');
            }
        }
    }

    public function refreshToken(Request $request)
    {
        $error = '';
        try {
            $token = $this->server->getRequestToken();
            $newToken = $this->server->refreshToken($request->input('site', 'main'), $token);
        }catch (\Exception $e){
            $error = $e->getMessage();
            Log::error($error);
        }
        if($error){
            return $this->error($error);
        }else{
            return $this->success(['token'=>$newToken]);
        }
    }

    public function success($data = [], $msg='')
    {
        return $this->result(1, $msg, $data);
    }

    public function error($msg)
    {
        return $this->result(0, $msg, []);
    }

    public function result($code,  $msg, $data = [])
    {
        $data = [
            'code'=>$code,
            'msg'=>$msg,
            'data'=>$data,
        ];
        return new JsonResponse($data, 200, [
            'source'=>env('APP_URL'),
        ], JSON_UNESCAPED_UNICODE);
    }
}
