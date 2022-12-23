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

    public function execute()
    {
        $participant = new Participation($this->conversation, $this->participantId);
        $message = Message::from($participant, $this->messageId);

        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);
        $params = [
            'TableName' => ConfigurationManager::getTableName(),
            'Key' => $message->getPrimaryKey(),
            'ExpressionAttributeValues' => [
                ':SK' => ['S' => $message->getSK()],
                ':GSI2SK' => ['S' => "PARTICIPANT#{$participant->getParticipantIdentifier()}"]
            ],
            'ConditionExpression' => 'SK = :SK AND GSI2SK = :GSI2SK',
        ];

        $client->deleteItem($params);
    }
}