<?php

namespace Musonza\LaravelDynamodbChat;

class Configuration
{
    public const TABLE_NAME = 'musonza_chat';

    public static function getTableName(): string
    {
        return config('musonza_dynamodb_chat.table_name', self::TABLE_NAME);
    }

    public static function getDynamodbEndpoint(): string
    {
        return config('musonza_dynamodb_chat.endpoint');
    }

    public static function getBatchLimit(): int
    {
        return config('musonza_dynamodb_chat.batch_limit');
    }

    public static function getRegion(): string
    {
        return config('musonza_dynamodb_chat.region');
    }

    public static function getAttributesAllowed(): array
    {
        return config('musonza_dynamodb_chat.attributes_allowed_list');
    }

    public static function shouldIncrementParentMessageReadCount(): bool
    {
        return config('musonza_dynamodb_chat.increment_parent_message_read_count');
    }

    public static function getProvisionedThroughput(): array
    {
        return config('musonza_dynamodb_chat.provisioned_throughput');
    }

    public static function getGlobalSecondaryIndex1ProvisionedThroughput(): array
    {
        return config('musonza_dynamodb_chat.gsi1_provisioned_throughput');
    }

    public static function getGlobalSecondaryIndex2ProvisionedThroughput(): array
    {
        return config('musonza_dynamodb_chat.gsi2_provisioned_throughput');
    }

    public static function getPaginatorLimit(): int
    {
        return config('musonza_dynamodb_chat.paginator_limit', 10);
    }

    public static function getPaginatorPages(): int
    {
        return config('musonza_dynamodb_chat.default_pagination_pages');
    }

    public static function getMaxParticipants(): int
    {
        return config('musonza_dynamodb_chat.max_participants');
    }
}
