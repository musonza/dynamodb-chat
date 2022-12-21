<?php

namespace Musonza\LaravelDynamodbChat\Tests;

use Bego\Condition;
use Bego\Database;
use Musonza\LaravelDynamodbChat\ChatServiceProvider;
use Illuminate\Foundation\Application;
use Musonza\LaravelDynamodbChat\Entities\Entity;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected Database $database;

    protected function setUp(): void
    {
        parent::setUp();
        if (!app()->environment('testing')) {
            throw new \Exception("You can only run these tests in a testing environment");
        }

        $this->database = app(Database::class);
    }

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
        $app['config']->set('musonza_dynamondb_chat.batch_limit', 3);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    protected function query($key, $condition = null)
    {
        return $this->database->table(app(Entity::class))
            ->query()
            ->key($key)
            ->condition($condition)
            ->fetch();
    }
}