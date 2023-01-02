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
    private Message $message;
    private Participation $participation;
    private string $text = '';
    private array $data = [];

    public function __construct(Participation $participation, Conversation $conversation, Message $message)
    {
        $this->participation = $participation;
        $this->conversation = $conversation;
        $this->message = $message;
    }

    public function message(string $participant, string $text, array $data = []): self
    {
        $this->participation = $this->participation->newInstance([
            'Id' => $participant,
            'ConversationId' => $this->conversation->getId(),
        ]);

        $this->text = $text;
        $this->data = $data;
        return $this;
    }

    public function delete(string $participant): Result
    {
        $participation = $this->participation->newInstance([
            'Id' => $participant,
            'ConversationId' => $this->conversation->getId(),
        ]);

        return (new DeleteMessage($this->conversation, $participation, $this->message))
            ->execute();
    }

    public function send(): Entity
    {
        return (new CreateMessage($this->conversation, $this->participation, $this->message, $this->text, $this->data))->execute();
    }

    public function markAsRead(string $participant): bool
    {
        $participation = $this->participation->newInstance([
            'Id' => $participant,
            'ConversationId' => $this->conversation->getId(),
        ]);

        return (new UpdateMessage($this->conversation, $participation, $this->message, ['Read' => true]))
            ->execute();
    }

    public function getMessages(string $participant, array $offset = null): Resultset
    {
        $participation = $this->participation->newInstance([
            'Id' => $participant,
            'ConversationId' => $this->conversation->getId(),
        ]);

        return (new GetMessages($this->conversation, $participation))->execute($offset);
    }

    public function setConversation(Conversation $conversation): self
    {
        $this->conversation = $conversation;
        return $this;
    }

    public function setMessage(Message $message): self
    {
        $this->message = $message;
        return $this;
    }
}