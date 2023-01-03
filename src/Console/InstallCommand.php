<?php

namespace Musonza\LaravelDynamodbChat\Console;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Console\Command;
use Musonza\LaravelDynamodbChat\Configuration;
use Musonza\LaravelDynamodbChat\Entities\Entity;

class InstallCommand extends Command
{
    protected $signature = 'dynamo:chat:install';

    public function handle(): void
    {
        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);
        $client->createTable([
            'TableName' => Configuration::getTableName(),
            'AttributeDefinitions' => [
                [
                    'AttributeName' => Entity::PARTITION_KEY,
                    'AttributeType' => 'S',
                ],
                [
                    'AttributeName' => Entity::SORT_KEY,
                    'AttributeType' => 'S',
                ],
                [
                    'AttributeName' => Entity::GSI1_PARTITION_KEY,
                    'AttributeType' => 'S',
                ],
                [
                    'AttributeName' => Entity::GSI1_SORT_KEY,
                    'AttributeType' => 'S',
                ],
                [
                    'AttributeName' => Entity::GSI2_PARTITION_KEY,
                    'AttributeType' => 'S',
                ],
                [
                    'AttributeName' => Entity::GSI2_SORT_KEY,
                    'AttributeType' => 'S',
                ],
            ],
            'KeySchema' => [
                [
                    'AttributeName' => Entity::PARTITION_KEY,
                    'KeyType' => 'HASH',
                ],
                [
                    'AttributeName' => Entity::SORT_KEY,
                    'KeyType' => 'RANGE',
                ],
            ],
            'ProvisionedThroughput' => Configuration::getProvisionedThroughput(),
            'GlobalSecondaryIndexes' => [
                [
                    'IndexName' => Entity::GSI1_NAME,
                    'KeySchema' => [
                        [
                            'AttributeName' => Entity::GSI1_PARTITION_KEY,
                            'KeyType' => 'HASH',
                        ],
                        [
                            'AttributeName' => Entity::GSI1_SORT_KEY,
                            'KeyType' => 'RANGE',
                        ],
                    ],
                    'Projection' => [
                        'ProjectionType' => 'ALL',
                    ],
                    'ProvisionedThroughput' => Configuration::getGlobalSecondaryIndex1ProvisionedThroughput(),
                ],
                [
                    'IndexName' => Entity::GSI2_NAME,
                    'KeySchema' => [
                        [
                            'AttributeName' => Entity::GSI2_PARTITION_KEY,
                            'KeyType' => 'HASH',
                        ],
                        [
                            'AttributeName' => Entity::GSI2_SORT_KEY,
                            'KeyType' => 'RANGE',
                        ],
                    ],
                    'Projection' => [
                        'ProjectionType' => 'ALL',
                    ],
                    'ProvisionedThroughput' => Configuration::getGlobalSecondaryIndex2ProvisionedThroughput(),
                ],
            ],
        ]);

        $client->updateTimeToLive([
            'TableName' => Configuration::getTableName(),
            'TimeToLiveSpecification' => [
                'AttributeName' => 'TTL',
                'Enabled' => true,
            ],
        ]);
    }
}
