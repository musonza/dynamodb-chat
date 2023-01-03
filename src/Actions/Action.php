<?php

namespace Musonza\LaravelDynamodbChat\Actions;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Result;
use Bego\Database;
use Bego\Table;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

abstract class Action
{
    /**
     * @psalm-suppress MissingReturnType
     * @phpstan-ignore-next-line
     */
    abstract public function execute();

    protected function getTable(): Table
    {
        /** @var Database $db */
        $db = app(Database::class);
        return $db->table(app(Conversation::class));
    }

    protected function saveItems(array $batchItems): void
    {
        $this->getTable()->putBatch($batchItems);
    }

    protected function deleteItems(array $batchItems): void
    {
        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);

        $client->batchWriteItem([
            'RequestItems' => [
                ConfigurationManager::getTableName() => [
                    ...$batchItems
                ]
            ]
        ]);
    }
}