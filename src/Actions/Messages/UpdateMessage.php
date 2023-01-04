<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Configuration;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Message;
use Musonza\LaravelDynamodbChat\Entities\Participation;
use Musonza\LaravelDynamodbChat\Exceptions\ResourceNotFoundException;

class UpdateMessage extends Action
{
    protected readonly Conversation $conversation;

    protected readonly Participation $participation;

    protected readonly Message $message;

    protected array $attributes;

    protected array $allowedAttributes = [
        'Read',
    ];

    public function __construct(Conversation $conversation, Participation $participation, Message $message, array $attributes)
    {
        $this->conversation = $conversation;
        $this->participation = $participation;
        $this->message = $message;
        $this->attributes = $attributes;
    }

    public function execute(): bool
    {
        // TODO resolve IDs cleanly
        $gsi1sk = "PARTICIPANT#{$this->participation->getId()}{$this->message->getId()}";

        $item = $this->getTable()
            ->query('GSI1')
            ->key($this->conversation->getPK())
            ->condition(Condition::attribute('GSI1SK')->beginsWith($gsi1sk))
            ->fetch()
            ->first();

        if (is_null($item)) {
            throw new ResourceNotFoundException('Message not found');
        }

        foreach ($this->attributes as $attribute => $value) {
            if (! in_array($attribute, $this->allowedAttributes)) {
                continue;
            }
            $item->set($attribute, $value);
        }

        $updated = $this->getTable()->update($item);

        // TODO this logic can be moved to an event listener
        // update ReadCount on parent message
        if ($updated && isset($this->attributes['Read']) && Configuration::shouldIncrementParentMessageReadCount()) {
            $this->incrementReadCount($this->conversation, $item->attribute('ParentId'));
        }

        return (bool) $updated;
    }

    private function incrementReadCount(Conversation $conversation, string $messageId): void
    {
        $key = $conversation->getPrimaryKey();
        $key['SK']['S'] = $messageId;

        $params = [
            'TableName' => Configuration::getTableName(),
            'Key' => $key,
            'ExpressionAttributeValues' => [
                ':inc' => ['N' => 1],
            ],
            'UpdateExpression' => 'SET ReadCount = ReadCount + :inc',
            'ReturnValues' => 'UPDATED_NEW',
        ];

        $this->getDynamoDbClient()->updateItem($params);
    }
}
