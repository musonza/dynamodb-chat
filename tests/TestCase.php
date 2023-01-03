<?php

namespace Musonza\LaravelDynamodbChat\Tests;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Bego\Database;
use Exception;
use Illuminate\Foundation\Application;
use Musonza\LaravelDynamodbChat\Chat;
use Musonza\LaravelDynamodbChat\ChatServiceProvider;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Console\InstallCommand;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Facades\ChatFacade;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected Database $database;

    protected Chat $chat;

    protected Marshaler $marshaler;

    public const PARTICIPANTS = [
        'user1',
        'user2',
        'user3',
        'user4',
    ];

    public function tearDown(): void
    {
        $this->checkEnvironment();

        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);
        $client->deleteTable([
            'TableName' => ConfigurationManager::getTableName(),
        ]);
        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->checkEnvironment();

        $this->database = app(Database::class);
        $this->chat = app(Chat::class);
        $this->marshaler = new Marshaler();
        $this->createTable();
    }

    private function checkEnvironment()
    {
        if (! app()->environment('testing')) {
            throw new Exception('You can only run these tests in a testing environment');
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
            'Chat' => ChatFacade::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('musonza_dynamodb_chat', [
            'table_name' => 'musonza_chat',
            'endpoint' => 'http://localhost:8000',
            'region' => 'us-east-1',
            'batch_limit' => 25,
            'default_pagination_limit' => 10,
            'default_pagination_pages' => 1,
            'attributes_allowed_list' => [
                'Subject',
                'Description',
                'IsPrivate',
            ],
            'increment_parent_message_read_count' => true,

            'provisioned_throughput' => [
                'ReadCapacityUnits' => 5,
                'WriteCapacityUnits' => 5,
            ],

            'gsi1_provisioned_throughput' => [
                'ReadCapacityUnits' => 5,
                'WriteCapacityUnits' => 5,
            ],
            'gsi2_provisioned_throughput' => [
                'ReadCapacityUnits' => 5,
                'WriteCapacityUnits' => 5,
            ],
        ]);
    }

    protected function query($key, $conditions = [], $index = null)
    {
        $statement = $this->database->table(app(Conversation::class))
            ->query($index)
            ->key($key);

        foreach ($conditions as $condition) {
            $statement->condition($condition);
        }

        return $statement->fetch();
    }

    protected function createConversation(int $participantCount = null): Conversation
    {
        $participants = ! is_null($participantCount)
            ? array_slice(self::PARTICIPANTS, 0, $participantCount)
            : self::PARTICIPANTS;

        return $this->chat->conversation()
            ->setAttributes([
                'Subject' => 'Hello',
            ])->setParticipants($participants)
            ->create();
    }
}
