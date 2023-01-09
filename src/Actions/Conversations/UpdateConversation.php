<?php

namespace Musonza\LaravelDynamodbChat\Actions\Conversations;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class UpdateConversation extends Action
{
    protected readonly Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function execute(): ?bool
    {
        $conversationPartitionKey = array_values($this->conversation->getPartitionKey())[0];
        $item = $this->getTable()
            ->query()
            ->key($conversationPartitionKey)
            ->condition(Condition::attribute('SK')->eq($conversationPartitionKey))
            ->fetch()
            ->first();

        foreach ($this->conversation->getAttributes() as $attribute => $value) {
            $item->set($attribute, $value);
        }

        return $this->getTable()->update($item);
    }
}
