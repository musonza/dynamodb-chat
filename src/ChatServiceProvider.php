<?php

namespace Musonza\LaravelDynamodbChat;

use Illuminate\Support\ServiceProvider;
use Aws\DynamoDb\DynamoDbClient;

class ChatServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('\Musonza\LaravelDynamodbChat\Chat', function () {
            return $this->app->make(Chat::class);
        });

        $this->app->singleton(DynamoDbClient::class, function () {
            dd("ok");
            return null;
        });
    }
}