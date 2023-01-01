<?php

namespace Musonza\LaravelDynamodbChat;

use Musonza\LaravelDynamodbChat\Actions\ConversationClient;
use Musonza\LaravelDynamodbChat\Actions\MessageClient;
use Musonza\LaravelDynamodbChat\Actions\Participants\AddParticipants;
use Musonza\LaravelDynamodbChat\Actions\Participants\DeleteParticipants;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

class Chat
{
    protected ConversationClient $conversationClient;
    protected MessageClient $messageClient;

    public function __construct(ConversationClient $conversationClient, MessageClient $messageClient)
    {
        $this->conversationClient = $conversationClient;
        $this->messageClient = $messageClient;
    }

    public function conversation(string $conversationId = null): ConversationClient
    {
        return $this->conversationClient->setConversationId($conversationId);
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
        return $this->messageClient
            ->setConversation($conversation)
            ->setMessageId($messageId);
    }
}