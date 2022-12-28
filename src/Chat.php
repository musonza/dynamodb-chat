<?php

namespace Musonza\LaravelDynamodbChat;

use Musonza\LaravelDynamodbChat\Actions\Conversations\CreateConversation;
use Musonza\LaravelDynamodbChat\Actions\Messages\CreateMessage;
use Musonza\LaravelDynamodbChat\Actions\Messages\DeleteMessage;
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

    public function messaging(string $conversationId): CreateMessage
    {
        $conversation = new Conversation($conversationId);
        return new CreateMessage($conversation);
    }

    public function deleteMessage(string $conversationId, string $messageId, string $participantId): void
    {
        $conversation = new Conversation($conversationId);
        (new DeleteMessage($conversation, $messageId, $participantId))->execute();
    }
}