<?php

namespace Musonza\LaravelDynamodbChat\Tests;

use Aws\DynamoDb\DynamoDbClient;
use Bego\Database;
use Exception;
use Illuminate\Foundation\Application;
use Musonza\LaravelDynamodbChat\ChatServiceProvider;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Console\InstallCommand;
use Musonza\LaravelDynamodbChat\Entities\Entity;
use Musonza\LaravelDynamodbChat\Facades\Chat;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected Database $database;

    public function tearDown(): void
    {
        $this->checkEnvironment();

        /** @var DynamoDbClient $client */
//        $client = app(DynamoDbClient::class);
//        $client->deleteTable([
//            'TableName' => ConfigurationManager::getTableName(),
//        ]);
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkEnvironment();

        $this->database = app(Database::class);
//        $this->createTable();
    }

    private function checkEnvironment()
    {
        if (!app()->environment('testing')) {
            throw new Exception("You can only run these tests in a testing environment");
        }
    }

    protected function createTable()
    {
        /** @var InstallCommand $install */
        $install = app(InstallCommand::class);
        $install->handle();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ChatServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Chat' => Chat::class,
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
        $app['config']->set('musonza_dynamodb_chat.region', 'us-east-1');
        $app['config']->set('musonza_dynamondb_chat.batch_limit', 25);
        $app['config']->set('musonza_dynamondb_chat.attributes_allowed_list', [
            'Subject',
            'Description',
            'isPrivate'
        ]);
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