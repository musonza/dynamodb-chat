<?php

namespace Musonza\LaravelDynamodbChat\Tests;

use Musonza\LaravelDynamodbChat\ChatServiceProvider;
use Illuminate\Foundation\Application;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ChatServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('musonza_dynamodb_chat.table_name', 'musonza_chat');
        $app['config']->set('musonza_dynamodb_chat.endpoint', 'http://localhost:8000');
    }
}