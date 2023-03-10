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
        return $this->conversationClient->setConversation($conversationId);
    }

    public function addParticipants(string $conversationId, array $participantIds): void
    {
        $conversation = $this->conversation->getInstance(['Id' => $conversationId]);
        (new AddParticipants($this->conversationClient, $conversation, $this->participation, $participantIds))
            ->execute();
    }

    public function deleteParticipants(string $conversationId, array $participantIds): void
    {
        $conversation = $this->conversation->getInstance(['Id' => $conversationId]);
        (new DeleteParticipants($this->conversationClient, $conversation, $this->participation, $participantIds))
            ->execute();
    }

    public function messaging(string $conversationId, string $messageId = null): MessageClient
    {
        $conversation = $this->conversation->getInstance(['Id' => $conversationId]);
        $message = $this->message->getInstance([
            'Id' => $messageId,
            'ConversationId' => $conversationId,
        ]);

        return $this->messageClient
            ->setConversation($conversation)
            ->setMessage($message);
    }
}
