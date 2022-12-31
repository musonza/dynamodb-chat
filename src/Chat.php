<?php

namespace Musonza\LaravelDynamodbChat;

use Musonza\LaravelDynamodbChat\Actions\Conversations\ConversationClient;
use Musonza\LaravelDynamodbChat\Actions\Messages\MessageClient;
use Musonza\LaravelDynamodbChat\Actions\Participants\AddParticipants;
use Musonza\LaravelDynamodbChat\Actions\Participants\DeleteParticipants;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class Chat
{
    public function conversation(string $conversationId = null): ConversationClient
    {
        return new ConversationClient($conversationId);
    }

    public function addParticipants(string $conversationId, array $participantIds): void
    {
        $conversation = Conversation::newInstance(['Id' => $conversationId], true);
        (new AddParticipants($conversation, $participantIds))->execute();
    }

    public function deleteParticipants(string $conversationId, array $participantIds): void
    {
        $conversation = Conversation::newInstance(['Id' => $conversationId], true);
        (new DeleteParticipants($conversation, $participantIds))->execute();
    }

    public function messaging(string $conversationId, string $messageId = null): MessageClient
    {
        $conversation = Conversation::newInstance(['Id' => $conversationId], true);
        return new MessageClient($conversation, $messageId);
    }
}