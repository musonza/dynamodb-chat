<?php

namespace Musonza\LaravelDynamodbChat;

use Musonza\LaravelDynamodbChat\Actions\Messages\MessageClient;
use Musonza\LaravelDynamodbChat\Actions\Participants\AddParticipants;
use Musonza\LaravelDynamodbChat\Actions\Participants\DeleteParticipants;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class Chat
{
    public function conversation(string $conversationId = null): Conversation
    {
        return new Conversation($conversationId);
    }

    public function addParticipants(string $conversationId, array $participantIds): void
    {
        $conversation = new Conversation($conversationId);
        (new AddParticipants($conversation, $participantIds))->execute();
    }

    public function deleteParticipants(string $conversationId, array $participantIds): void
    {
        $conversation = new Conversation($conversationId);
        (new DeleteParticipants($conversation, $participantIds))->execute();
    }

    public function messaging(string $conversationId, string $messageId = null): MessageClient
    {
        $conversation = new Conversation($conversationId);
        return new MessageClient($conversation, $messageId);
    }
}