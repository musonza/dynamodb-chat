<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Result;
use Bego\Item;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Message;
use Musonza\LaravelDynamodbChat\Entities\Participation;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class DeleteMessage extends Action
{
    protected Conversation $conversation;
    protected Participation $participation;
    protected Message $message;

    public function __construct(Conversation $conversation, Participation $participation, Message $message)
    {
        $this->conversation = $conversation;
        $this->message = $message;
        $this->participation = $participation;
    }

    public function execute(): Result
    {
        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);
        $params = [
            'TableName' => ConfigurationManager::getTableName(),
            'Key' => $this->message->getPrimaryKey(),
            'ExpressionAttributeValues' => [
                ':SK' => ['S' => $this->message->getSK()],
                // Only delete if the message is owned by the participant
                ':GSI2SK' => ['S' => Helpers::gsi2SKForMessage($this->participation)]
            ],
            'ConditionExpression' => 'SK = :SK AND GSI2SK = :GSI2SK',
        ];

        return $client->deleteItem($params);
    }
}