<?php

namespace Musonza\LaravelDynamodbChat;

use Aws\DynamoDb\Marshaler;
use Bego\Database;
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
            return new DynamoDbClient([
                'version' => 'latest',
                'region'  => ConfigurationManager::getRegion(),
                'endpoint' => ConfigurationManager::getDynamodbEndpoint(),
            ]);
        });

        $this->app->singleton(Database::class, function () {
            return new Database(app(DynamoDbClient::class), new Marshaler());
        });
    }
}