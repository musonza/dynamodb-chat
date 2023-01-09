<?php

namespace Musonza\LaravelDynamodbChat\Facades;

use Illuminate\Support\Facades\Facade;
use Musonza\LaravelDynamodbChat\Chat;

class ChatFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Chat::class;
    }
}
