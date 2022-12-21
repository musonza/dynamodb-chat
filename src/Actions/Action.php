<?php

namespace Musonza\LaravelDynamodbChat\Actions;

use Aws\DynamoDb\DynamoDbClient;
use Bego\Database;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Entity;

abstract class Action
{
    abstract public function execute();

    protected function saveItems(array $batchItems): void
    {
        /** @var Database $db */
        $db = app(Database::class);
        $table = $db->table(app(Entity::class));
        $table->putBatch($batchItems);
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