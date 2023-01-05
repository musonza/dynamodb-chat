<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Bego\Condition;
use Bego\Item;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Configuration;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Entity;
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
        $item = $this->getMessageItem();

        if (is_null($item)) {
            throw new ResourceNotFoundException('Message not found');
        }

        $allowedAttributes = array_flip($this->allowedAttributes);
        $attributesToUpdate = array_intersect_key($this->attributes, $allowedAttributes);

        foreach ($attributesToUpdate as $attribute => $value) {
            $item->set($attribute, $value);
        }

        $updated = $this->getTable()->update($item);

        if ($updated && isset($this->attributes['Read']) && Configuration::shouldIncrementParentMessageReadCount()) {
            $this->incrementReadCount($this->conversation, $item->attribute('ParentId'));
        }

        return (bool) $updated;
    }

    private function getMessageItem(): ?Item
    {
        $gsi1sk = "PARTICIPANT#{$this->participation->getId()}{$this->message->getId()}";
        $query = $this->getTable()
            ->query(Entity::GSI1_NAME)
            ->key($this->conversation->getPK())
            ->condition(Condition::attribute(Entity::GSI1_SORT_KEY)->eq($gsi1sk));

        return $query->fetch()->first();
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
