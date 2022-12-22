<?php

namespace Musonza\LaravelDynamodbChat\Exceptions;

use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConversationNotFoundException extends NotFoundHttpException
{
    public Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        parent::__construct();
        $this->conversation = $conversation;
    }
}