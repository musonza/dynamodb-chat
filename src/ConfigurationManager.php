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
        return config('musonza_dynamondb_chat.batch_limit');
    }
}