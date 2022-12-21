<?php

return [
    /**
     * Single table to hold all your chat entities.
     * Learn more on single table design
     * @link https://aws.amazon.com/blogs/compute/creating-a-single-table-design-with-amazon-dynamodb/
     */
    'table_name' => 'musonza_chat',

    /**
     * Your DynamoDB endpoint
     * example: 'endpoint' => env('DYNAMO_DB_ENDPOINT', 'http://localhost:8000'),
     * You can test with local dynamoDB
     * @link https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/DynamoDBLocal.html
     */
    'endpoint' => 'http://localhost:8000',

    'region' => 'us-east-1',

    /**
     * BatchWriteItem can transmit up to 16MB of data over the network, consisting of up to 25 items
     * The library will chunk the items for you using this limit
     * @link https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_BatchWriteItem.html
     */
    'batch_limit' => 25,
];