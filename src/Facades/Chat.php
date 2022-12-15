<?php

namespace Musonza\LaravelDynamodbChat\Facades;

use Illuminate\Support\Facades\Facade;

class Chat extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Musonza\LaravelDynamodbChat\Chat::class;
    }
}