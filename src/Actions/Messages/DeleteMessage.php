<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Aws\Result;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Configuration;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Message;
use Musonza\LaravelDynamodbChat\Entities\Participation;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class DeleteMessage extends Action
{
    protected readonly Conversation $conversation;

    protected readonly Participation $participation;

    protected readonly Message $message;

    public function __construct(Conversation $conversation, Participation $participation, Message $message)
    {
        $this->conversation = $conversation;
        $this->message = $message;
        $this->participation = $participation;
    }

    public function execute(): Result
    {
        $params = [
            'TableName' => Configuration::getTableName(),
            'Key' => $this->message->getPrimaryKey(),
            'ExpressionAttributeValues' => [
                ':SK' => ['S' => $this->message->getSK()],
                // Only delete if the message is owned by the participant
                ':GSI2SK' => ['S' => Helpers::gsi2SKForMessage($this->participation)],
            ],
            'ConditionExpression' => 'SK = :SK AND GSI2SK = :GSI2SK',
        ];

        return $this->getDynamoDbClient()->deleteItem($params);
    }
}
