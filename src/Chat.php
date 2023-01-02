<?php

namespace Musonza\LaravelDynamodbChat;

use Musonza\LaravelDynamodbChat\Actions\ConversationClient;
use Musonza\LaravelDynamodbChat\Actions\MessageClient;
use Musonza\LaravelDynamodbChat\Actions\Participants\AddParticipants;
use Musonza\LaravelDynamodbChat\Actions\Participants\DeleteParticipants;
use Musonza\LaravelDynamodbChat\Entities\Conversation;

final class Chat
{
    protected ConversationClient $conversationClient;
    protected MessageClient $messageClient;
    protected Conversation $conversation;

    public function __construct(
        ConversationClient $conversationClient,
        MessageClient $messageClient,
        Conversation $conversation
    ) {
        $this->conversationClient = $conversationClient;
        $this->messageClient = $messageClient;
        $this->conversation = $conversation;
    }

    public function conversation(string $conversationId = null): ConversationClient
    {
        return $this->conversationClient->setConversationId($conversationId);
    }

    public function addParticipants(string $conversationId, array $participantIds): void
    {
        $conversation = $this->conversation->newInstance(['Id' => $conversationId], true);
        (new AddParticipants($this->conversationClient, $conversation, $participantIds))->execute();
    }

    public function deleteParticipants(string $conversationId, array $participantIds): void
    {
        $conversation =  $this->conversation->newInstance(['Id' => $conversationId], true);
        (new DeleteParticipants($this->conversationClient, $conversation, $participantIds))->execute();
    }

    public function messaging(string $conversationId, string $messageId = null): MessageClient
    {
        $conversation =  $this->conversation->newInstance(['Id' => $conversationId], true);
        return $this->messageClient
            ->setConversation($conversation)
            ->setMessageId($messageId);
    }
}