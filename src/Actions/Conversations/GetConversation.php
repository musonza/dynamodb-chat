<?php

namespace Musonza\LaravelDynamodbChat\Actions\Conversations;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class GetConversation extends Action
{
    protected Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function execute()
    {
        $resultSet = $this->getTable()
            ->query()
            ->key($this->conversation->getPK())
            ->condition(Condition::attribute('SK')->eq($this->conversation->getSK()))
            ->fetch();

        $this->conversation->setResultSet($resultSet);
    }
}