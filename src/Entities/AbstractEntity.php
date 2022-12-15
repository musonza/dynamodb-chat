<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Aws\DynamoDb\DynamoDbClient;

abstract class AbstractEntity
{
    public abstract function getPrimaryKey(): array;

    public abstract function getPartitionKey(): array;

    public abstract function toItem(): array;

    public function getClient()
    {
        $client = new DynamoDbClient([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'endpoint' => 'http://localhost:8000'
        ]);

        $response = $client->putItem(array(
            'TableName' => 'musonza_chat',
            'Item' => $this->toItem(),
        ));
    }
}