<?php

namespace Musonza\LaravelDynamodbChat\Actions\Conversations;

use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class CreateConversation extends Action
{
    protected Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function execute(): Conversation
    {
        $this->getTable()->put($this->conversation->toArray());
        return $this->conversation;
    }
}