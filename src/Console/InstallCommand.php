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

        $client->createTable(array(
            'TableName' => 'musonza_chat',
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'PK',
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => 'SK',
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
                'ReadCapacityUnits'  => 10,
                'WriteCapacityUnits' => 20
            ]
        ));
    }
}