<?php

namespace Musonza\LaravelDynamodbChat;

use Musonza\LaravelDynamodbChat\Actions\Conversations\CreateConversation;
use Musonza\LaravelDynamodbChat\Actions\Conversations\GetConversation;
use Musonza\LaravelDynamodbChat\Actions\Conversations\UpdateConversation;
use Musonza\LaravelDynamodbChat\Actions\Messages\CreateMessage;
use Musonza\LaravelDynamodbChat\Actions\Participants\AddParticipants;
use Musonza\LaravelDynamodbChat\Actions\Participants\DeleteParticipants;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class Chat
{
    protected CreateConversation $createConversationAction;

    public function __construct(CreateConversation $createConversation)
    {
        $this->createConversationAction = $createConversation;
    }

    public function conversation(string $conversationId = null): Conversation
    {
        return new Conversation($conversationId);
    }

    public function getConversationById(string $conversationId): Conversation
    {
        return (new Conversation($conversationId))->first();
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

    public function messaging(string $conversation): CreateMessage
    {
        $conversation = new Conversation($conversation);
        return new CreateMessage($conversation);
    }
}