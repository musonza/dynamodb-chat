<?php

namespace Musonza\LaravelDynamodbChat;

class ConfigurationManager
{
    const TABLE_NAME = 'musonza_chat';

    public static function getTableName()
    {
        return config('musonza_dynamodb_chat.table_name', self::TABLE_NAME);
    }

    public static function getDynamodbEndpoint()
    {
        return config('musonza_dynamodb_chat.endpoint');
    }

    public static function getBatchLimit()
    {
        return config('musonza_dynamodb_chat.batch_limit');
    }

    public static function getRegion()
    {
        return config('musonza_dynamodb_chat.region');
    }

    public static function getAttributesAllowed()
    {
        return config('musonza_dynamodb_chat.attributes_allowed_list');
    }

    public static function getIncrementParentMessageReadCount()
    {
        return config('musonza_dynamodb_chat.increment_parent_message_read_count');
    }

    public static function getProvisionedThroughput()
    {
        return config('musonza_dynamodb_chat.provisioned_throughput');
    }

    public static function getGlobalSecondaryIndex1ProvisionedThroughput()
    {
        return config('musonza_dynamodb_chat.gsi1_provisioned_throughput');
    }

    public static function getGlobalSecondaryIndex2ProvisionedThroughput()
    {
        return config('musonza_dynamodb_chat.gsi2_provisioned_throughput');
    }

    public static function getPaginatorLimit()
    {
        return config('musonza_dynamodb_chat.default_pagination_limit');
    }

    public static function getPaginatorPages()
    {
        return config('musonza_dynamodb_chat.default_pagination_pages');
    }
}