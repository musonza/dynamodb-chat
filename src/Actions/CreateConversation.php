<?php

namespace Musonza\LaravelDynamodbChat\Actions;

use Aws\DynamoDb\DynamoDbClient;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class CreateConversation
{
    public function execute(string $subject = 'Conversation'): Conversation
    {
        $conversation = new Conversation();
        $conversation->setSubject($subject);
        app(DynamoDbClient::class)->putItem([
            'TableName' => ConfigurationManager::getTableName(),
            'Item' => $conversation->toItem(),
        ]);

        return $conversation;
    }
}