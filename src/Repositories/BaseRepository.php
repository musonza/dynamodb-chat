<?php

namespace Musonza\LaravelDynamodbChat\Repositories;

use Aws\DynamoDb\DynamoDbClient;
use Musonza\LaravelDynamodbChat\ConfigurationManager;

abstract class BaseRepository
{
    public function getClient(): DynamoDbClient
    {
        // TODO move to singleton
        return new DynamoDbClient([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'endpoint' => ConfigurationManager::getDynamodbEndpoint(),
        ]);
    }
}