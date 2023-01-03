<?php

namespace Musonza\LaravelDynamodbChat\Actions\Conversations;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Entity;

class GetConversation extends Action
{
    protected readonly Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function execute(): Conversation
    {
        $resultSet = $this->getTable()
            ->query()
            ->key($this->conversation->getPK())
            ->condition(Condition::attribute(Entity::SORT_KEY)->eq($this->conversation->getSK()))
            ->fetch();

        $this->conversation->setResultSet($resultSet);

        return $this->conversation;
    }
}
