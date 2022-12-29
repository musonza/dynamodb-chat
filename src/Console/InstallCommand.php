<?php

namespace Musonza\LaravelDynamodbChat\Console;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Console\Command;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Entity;

class InstallCommand extends Command
{
    protected $signature = 'dynamo:chat:install';

    public function handle()
    {
        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);
        $client->createTable([
            'TableName' => ConfigurationManager::getTableName(),
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'PK',
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => 'SK',
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => Entity::GLOBAL_INDEX1_PK,
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => Entity::GLOBAL_INDEX1_SK,
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => Entity::GLOBAL_INDEX2_PK,
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => Entity::GLOBAL_INDEX2_SK,
                    'AttributeType' => 'S'
                ]
            ],
            'KeySchema' => [
                [
                    'AttributeName' => 'PK',
                    'KeyType'       => 'HASH'
                ],
                [
                    'AttributeName' => 'SK',
                    'KeyType'       => 'RANGE'
                ]
            ],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits'  => 5,
                'WriteCapacityUnits' => 5
            ],
            'GlobalSecondaryIndexes' => [
                [
                    'IndexName' => Entity::GLOBAL_INDEX1,
                    'KeySchema' => [
                        [
                            'AttributeName' => Entity::GLOBAL_INDEX1_PK,
                            'KeyType'       => 'HASH'
                        ],
                        [
                            'AttributeName' => Entity::GLOBAL_INDEX1_SK,
                            'KeyType'       => 'RANGE'
                        ]
                    ],
                    'Projection' => [
                        'ProjectionType' => 'ALL'
                    ],
                    'ProvisionedThroughput' => [
                        'ReadCapacityUnits'  => 5,
                        'WriteCapacityUnits' => 5
                    ],
                ],
                [
                    'IndexName' => Entity::GLOBAL_INDEX2,
                    'KeySchema' => [
                        [
                            'AttributeName' => Entity::GLOBAL_INDEX2_PK,
                            'KeyType'       => 'HASH'
                        ],
                        [
                            'AttributeName' => Entity::GLOBAL_INDEX2_SK,
                            'KeyType'       => 'RANGE'
                        ]
                    ],
                    'Projection' => [
                        'ProjectionType' => 'ALL'
                    ],
                    'ProvisionedThroughput' => [
                        'ReadCapacityUnits'  => 5,
                        'WriteCapacityUnits' => 5
                    ],
                ]
            ]
        ]);
    }
}