<?php

namespace Musonza\LaravelDynamodbChat\Actions;

use Aws\Result;
use Bego\Component\Resultset;
use Musonza\LaravelDynamodbChat\Actions\Messages\CreateMessage;
use Musonza\LaravelDynamodbChat\Actions\Messages\DeleteMessage;
use Musonza\LaravelDynamodbChat\Actions\Messages\GetMessages;
use Musonza\LaravelDynamodbChat\Actions\Messages\UpdateMessage;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Entity;
use Musonza\LaravelDynamodbChat\Entities\Message;
use Musonza\LaravelDynamodbChat\Entities\Participation;

class MessageClient
{
    private Conversation $conversation;
    private ?string $messageId;
    private Participation $participation;
    private string $text = '';
    private array $data = [];

//    public function __construct(Conversation $conversation, string $messageId = null)
//    {
//        $this->conversation = $conversation;
//        $this->messageId = $messageId;
//    }

    public function message(string $participant, string $text, array $data = []): self
    {
        $this->participation = app(Participation::class)->newInstance([
            'Id' => $participant,
            'ConversationId' => $this->conversation->getId(),
        ]);

        $this->text = $text;
        $this->data = $data;
        return $this;
    }

    public function delete(string $participantExternalId): Result
    {
        $participation = app(Participation::class)->newInstance([
            'ConversationId' => $this->conversation->getId(),
            'Id' => $participantExternalId,
        ]);
        return (new DeleteMessage($this->conversation, $this->messageId, $participation->getParticipantExternalId()))
            ->execute();
    }

    public function send(): Entity
    {
        return (new CreateMessage($this->conversation, $this->participation, $this->text, $this->data))->execute();
    }

    public function markAsRead(string $participantExternalId): bool
    {
        $participation = app(Participation::class)->newInstance([
            'ConversationId' => $this->conversation->getId(),
            'Id' => $participantExternalId,
        ]);

        return (new UpdateMessage($this->conversation, $participation, $this->messageId, ['Read' => true]))
            ->execute();
    }

    public function getMessages(string $participantExternalId, array $offset = null): Resultset
    {
        $participation = app(Participation::class)->newInstance([
            'ConversationId' => $this->conversation->getId(),
            'Id' => $participantExternalId,
        ]);
        return (new GetMessages($this->conversation, $participation))->execute($offset);
    }

    public function setConversation(Conversation $conversation): self
    {
        $this->conversation = $conversation;
        return $this;
    }

    public function setMessageId(?string $messageId): self
    {
        $this->messageId = $messageId;
        return $this;
    }
}