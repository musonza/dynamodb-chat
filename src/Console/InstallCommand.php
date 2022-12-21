<?php

namespace Musonza\LaravelDynamodbChat\Console;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'dynamo:chat:install';

    public function handle()
    {
        $client = new DynamoDbClient([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'endpoint' => 'http://localhost:8000'
        ]);

        $client->createTable([
            'TableName' => 'musonza_chat',
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
                    'AttributeName' => 'GSI1PK',
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => 'GSI1SK',
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => 'GSI2PK',
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => 'GSI2SK',
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
                    'IndexName' => 'GS1',
                    'KeySchema' => [
                        [
                            'AttributeName' => 'GSI1PK',
                            'KeyType'       => 'HASH'
                        ],
                        [
                            'AttributeName' => 'GSI1SK',
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
                    'IndexName' => 'GSI2',
                    'KeySchema' => [
                        [
                            'AttributeName' => 'GSI2PK',
                            'KeyType'       => 'HASH'
                        ],
                        [
                            'AttributeName' => 'GSI2SK',
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