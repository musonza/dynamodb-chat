<?php

namespace Musonza\LaravelDynamodbChat\Exceptions;

use LogicException;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class ConversationExistsException extends LogicException
{
    public Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        parent::__construct();
        $this->conversation = $conversation;
    }
}
