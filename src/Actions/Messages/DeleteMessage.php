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
    protected string $messageId;
    protected string $participantId;

    public function __construct(Conversation $conversation, string $messageId, string $participantId)
    {
        $this->conversation = $conversation;
        $this->messageId = $messageId;
        $this->participantId = $participantId;
    }

    public function execute(): Result
    {
        $participant = Participation::newInstance([
            'Id' => $this->participantId,
            'ConversationId' => $this->conversation->getId(),
        ]);

        $message = Message::newInstance([
            'Id' => $this->messageId,
            'ConversationId' => $this->conversation->getId(),
        ], true);

        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);
        $params = [
            'TableName' => ConfigurationManager::getTableName(),
            'Key' => $message->getPrimaryKey(),
            'ExpressionAttributeValues' => [
                ':SK' => ['S' => $message->getSK()],
                // Only delete if the message is owned by the participant
                ':GSI2SK' => ['S' => Helpers::gsi2SKForMessage($participant)]
            ],
            'ConditionExpression' => 'SK = :SK AND GSI2SK = :GSI2SK',
        ];

        return $client->deleteItem($params);
    }
}