<?php

namespace Musonza\LaravelDynamodbChat;

use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Repositories\ConversationParticipationRepository;
use Musonza\LaravelDynamodbChat\Repositories\ConversationRepository;

class Chat
{
    public function createConversation(): ConversationRepository
    {
        return app(ConversationRepository::class);
    }

    public function addParticipants(string $conversationId, array $participants)
    {
        $conversation = new Conversation($conversationId);
        (new ConversationParticipationRepository($conversation))->addParticipants($participants);
    }
}