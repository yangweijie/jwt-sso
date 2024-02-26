<?php

declare(strict_types=1);

namespace yangweijie\jwt\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Response
 *
 * @create 2020-10-26
 *
 * @author deatil
 */
class Response extends Facade
{
    protected static function getFacadeAccessor()
    {
        return new \yangweijie\jwt\Response();
    }
}
