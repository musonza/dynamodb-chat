<?php

namespace Musonza\LaravelDynamodbChat;

class Configuration
{
    public static function getTableName(): string
    {
        return config('musonza_dynamodb_chat.table_name', 'musonza_chat');
    }

    public static function getDynamodbEndpoint(): string
    {
        return config('musonza_dynamodb_chat.endpoint', 'http://localhost:8000');
    }

    public static function getBatchLimit(): int
    {
        return config('musonza_dynamodb_chat.batch_limit', 25);
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
        return config('musonza_dynamodb_chat.increment_parent_message_read_count', false);
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
        return config('musonza_dynamodb_chat.default_pagination_pages', 1);
    }

    public static function getMaxParticipants(): int
    {
        return config('musonza_dynamodb_chat.max_participants');
    }
}
