<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Aws\Result;
use Bego\Condition;
use Illuminate\Support\Str;
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
        $this->participation = new Participation($this->conversation, $participant);
        $this->text = $text;
        $this->data = $data;
        return $this;
    }

    public function delete(string $participant): Result
    {
        $participation = new Participation($this->conversation, $participant);
        return (new DeleteMessage($this->conversation, $this->messageId, $participation->getParticipantIdentifier()))
            ->execute();
    }

    public function send(): Message
    {
        return (new CreateMessage($this->conversation, $this->participation, $this->text, $this->data))->execute();
    }

    public function markAsRead(string $participant): bool
    {
        $participation = new Participation($this->conversation, $participant);
        return (new UpdateMessage($this->conversation, $participation, $this->messageId, ['Read' => true]))
            ->execute();
    }
}