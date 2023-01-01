<?php

namespace Musonza\LaravelDynamodbChat\Actions;

use Aws\Result;
use Bego\Component\Resultset;
use Musonza\LaravelDynamodbChat\Actions\Messages\CreateMessage;
use Musonza\LaravelDynamodbChat\Actions\Messages\DeleteMessage;
use Musonza\LaravelDynamodbChat\Actions\Messages\GetMessages;
use Musonza\LaravelDynamodbChat\Actions\Messages\UpdateMessage;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Message;
use Musonza\LaravelDynamodbChat\Entities\Participation;

class MessageClient
{
    private Conversation $conversation;
    private ?string $messageId;
    private Participation $participation;
    private string $text = '';
    private array $data = [];

    public function __construct(Conversation $conversation, string $messageId = null)
    {
        $this->conversation = $conversation;
        $this->messageId = $messageId;
    }

    public function message(string $participant, string $text, array $data = []): self
    {
        $this->participation = Participation::newInstance([
            'Id' => $participant,
            'ConversationId' => $this->conversation->getId(),
        ]);

        $this->text = $text;
        $this->data = $data;
        return $this;
    }

    public function delete(string $participantExternalId): Result
    {
        $participation = Participation::newInstance([
            'ConversationId' => $this->conversation->getId(),
            'Id' => $participantExternalId,
        ]);
        return (new DeleteMessage($this->conversation, $this->messageId, $participation->getParticipantExternalId()))
            ->execute();
    }

    public function send(): Message
    {
        return (new CreateMessage($this->conversation, $this->participation, $this->text, $this->data))->execute();
    }

    public function markAsRead(string $participantExternalId): bool
    {
        $participation = Participation::newInstance([
            'ConversationId' => $this->conversation->getId(),
            'Id' => $participantExternalId,
        ]);

        return (new UpdateMessage($this->conversation, $participation, $this->messageId, ['Read' => true]))
            ->execute();
    }

    public function getMessages(string $participantExternalId, $offset = null): Resultset
    {
        $participation = Participation::newInstance([
            'ConversationId' => $this->conversation->getId(),
            'Id' => $participantExternalId,
        ]);
        return (new GetMessages($this->conversation, $participation))->execute($offset);
    }
}