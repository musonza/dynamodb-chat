<?php

return [
    /**
     * Single table to hold all your chat entities.
     * Learn more on single table design
     *
     * @link https://aws.amazon.com/blogs/compute/creating-a-single-table-design-with-amazon-dynamodb/
     */
    'table_name' => 'musonza_chat',

    /**
     * Your DynamoDB endpoint
     * example: 'endpoint' => env('DYNAMO_DB_ENDPOINT', 'http://localhost:8000'),
     * You can test with local dynamoDB
     *
     * @link https://docs.aws.amazon.com/amazondynamodb/latest/developerguide/DynamoDBLocal.html
     */
    'endpoint' => 'http://localhost:8000',

    'region' => 'us-east-1',

    /**
     * BatchWriteItem can transmit up to 16MB of data over the network, consisting of up to 25 items
     * The library will chunk the items for you using this limit
     *
     * @link https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_BatchWriteItem.html
     */
    'batch_limit' => 25,

    /**
     * The maximum number of items to evaluate (not necessarily the number of matching items).
     */
    'default_pagination_limit' => 10,

    /**
     * Number of pages to retrieve when paginating
     */
    'default_pagination_pages' => 1,

    /**
     * DynamoDB will allow you to add any attributes to your Table at any time.
     * Add an allow list here to have more control and prevent arbitrary values .
     * Note: An empty list means allow anything
     */
    'attributes_allowed_list' => [],

    /**
     * Increment the parent message read count when a message is read
     * However, if the parent message is deleted, the read count will not be incremented
     */
    'increment_parent_message_read_count' => false,

    'provisioned_throughput' => [
        'ReadCapacityUnits' => 5,
        'WriteCapacityUnits' => 5,
    ],

    /**
     * Global secondary index 1
     */
    'gsi1_provisioned_throughput' => [
        'ReadCapacityUnits' => 5,
        'WriteCapacityUnits' => 5,
    ],

    'gsi1_projection' => [
        'ProjectionType' => 'ALL',
    ],

    /**
     * Global secondary index 2
     */
    'gsi2_provisioned_throughput' => [
        'ReadCapacityUnits' => 5,
        'WriteCapacityUnits' => 5,
    ],

    'gsi2_projection' => [
        'ProjectionType' => 'ALL',
    ],
];
