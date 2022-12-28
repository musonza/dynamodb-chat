<?php

namespace Musonza\LaravelDynamodbChat\Exceptions;

use Musonza\LaravelDynamodbChat\Entities\Conversation;

class ConversationNotFoundException extends ResourceNotFoundException
{
    public Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        parent::__construct();
        $this->conversation = $conversation;
    }
}