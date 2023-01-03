<?php

namespace Musonza\LaravelDynamodbChat;

use Musonza\LaravelDynamodbChat\Actions\ConversationClient;
use Musonza\LaravelDynamodbChat\Actions\MessageClient;
use Musonza\LaravelDynamodbChat\Actions\Participants\AddParticipants;
use Musonza\LaravelDynamodbChat\Actions\Participants\DeleteParticipants;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Message;
use Musonza\LaravelDynamodbChat\Entities\Participation;

final class Chat
{
    protected ConversationClient $conversationClient;
    protected MessageClient $messageClient;
    protected Conversation $conversation;
    protected Message $message;
    protected Participation $participation;

    public function __construct(
        ConversationClient $conversationClient,
        MessageClient $messageClient,
        Conversation $conversation,
        Message $message,
        Participation $participation
    ) {
        $this->conversationClient = $conversationClient;
        $this->messageClient = $messageClient;
        $this->conversation = $conversation;
        $this->message = $message;
        $this->participation = $participation;
    }

    public function conversation(string $conversationId = null): ConversationClient
    {
        return $this->conversationClient->setConversationId($conversationId);
    }

    public function addParticipants(string $conversationId, array $participantIds): void
    {
        $conversation = $this->conversation->newInstance(['Id' => $conversationId], true);
        (new AddParticipants($this->conversationClient, $conversation, $this->participation, $participantIds))
            ->execute();
    }

    public function deleteParticipants(string $conversationId, array $participantIds): void
    {
        $conversation =  $this->conversation->newInstance(['Id' => $conversationId], true);
        (new DeleteParticipants($this->conversationClient, $conversation, $this->participation, $participantIds))
            ->execute();
    }

    public function messaging(string $conversationId, string $messageId = null): MessageClient
    {
        $conversation =  $this->conversation->newInstance(['Id' => $conversationId], true);
        $message =  $this->message->newInstance([
            'Id' => $messageId,
            'ConversationId' => $conversationId,
        ], true);

        return $this->messageClient
            ->setConversation($conversation)
            ->setMessage($message);
    }
}