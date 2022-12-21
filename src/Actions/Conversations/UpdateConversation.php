<?php

namespace Musonza\LaravelDynamodbChat\Actions\Conversations;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class UpdateConversation extends Action
{
    protected Conversation $conversation;
    protected array $attributes;

    public function __construct(Conversation $conversation, array $attributes)
    {
        $this->conversation = $conversation;
        $this->attributes = $attributes;
    }

    public function execute()
    {
        $conversationPartitionKey = array_values($this->conversation->getPartitionKey())[0];
        $response = $this->getTable()
            ->query()
            ->key($conversationPartitionKey)
            ->condition(Condition::attribute('SK')->eq($conversationPartitionKey))
            ->fetch();

        $item = $response->first();

        foreach ($this->attributes as $attribute => $value) {
            $item->set($attribute, $value);
        }

        $this->getTable()->update($item);
    }
}