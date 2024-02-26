<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'sso',
    'namespace' => 'yangweijie\\jwt\\Controller',
    'as' => 'sso.',
], function ($router) {

    // 登陆
    $router->any('/parseToken', 'SsoController@parseToken')->name('parseToken');
    $router->post('/logout', 'SsoController@logout')->name('logout');
    $router->post('/refreshToken', 'SsoController@refreshToken')->name('refreshToken');

});
