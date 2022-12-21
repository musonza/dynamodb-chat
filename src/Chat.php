<?php

namespace Musonza\LaravelDynamodbChat;

use Musonza\LaravelDynamodbChat\Actions\AddParticipants;
use Musonza\LaravelDynamodbChat\Actions\CreateConversation;
use Musonza\LaravelDynamodbChat\Actions\CreateMessage;
use Musonza\LaravelDynamodbChat\Actions\DeleteParticipants;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class Chat
{
    protected CreateConversation $createConversationAction;

    public function __construct(CreateConversation $createConversation)
    {
        $this->createConversationAction = $createConversation;
    }

    public function createConversation(string $subject,  array $participantIds = []): Conversation
    {
        $conversation = (new CreateConversation())->execute($subject);

        if (!empty($participantIds)) {
            $this->addParticipants($conversation->getConversationId(), $participantIds);
        }

        return $conversation;
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