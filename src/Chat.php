<?php

namespace Musonza\LaravelDynamodbChat;

use Musonza\LaravelDynamodbChat\Repositories\ConversationRepository;

class Chat
{
    public function createConversation(): ConversationRepository
    {
        return app(ConversationRepository::class);
    }
}