<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Bego\Component\Resultset;
use Chat;
use Illuminate\Support\Carbon;
use Musonza\LaravelDynamodbChat\Actions\Conversations\CreateConversation;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class Conversation extends Entity implements Contract
{
    const CONVERSATION_PK_PREFIX = 'CONVERSATION#%s';

    const ENTITY_TYPE = 'CONVERSATION';

    protected string $conversationId;

    protected string $subject = 'Conversation';

    protected Carbon $createdAt;

    /**
     * Result from query on a Conversation.
     * @var Resultset|null
     */
    protected ?Resultset $resultset = null;

    /**
     * IDs of participants to add to a conversation.
     * @var array
     */
    protected array $participantIds = [];

    /**
     * Specifies whether the conversation is private or public.
     * @var bool
     */
    protected bool $isPrivate = false;

    public function __construct($conversationId = null, Carbon $createdAt = null)
    {
        $this->createdAt = $createdAt ?? now();
        $this->conversationId = $conversationId ?? Helpers::generateKSUID($this->createdAt);
    }

    public function getConversationId(): string
    {
        return $this->conversationId;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function makePrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;
        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getPrimaryKey(): array
    {
        return [
            'PK' => $this->getPartitionKey(),
            'SK' => $this->getPartitionKey(),
        ];
    }

    public function getPartitionKey(): array
    {
        return [
            'S' => sprintf(self::CONVERSATION_PK_PREFIX,  $this->getConversationId())
        ];
    }

    public function toItem(): array
    {
        return[
            ...$this->getPrimaryKey(),
            'Type' => ['S' => self::ENTITY_TYPE],
            'Subject' => ['S' => $this->getSubject()],
            'CreatedAt' => ['S' => $this->createdAt->toISOString()],
        ];
    }

    public function getPK(): string
    {
        return array_values($this->getPartitionKey())[0];
    }

    public function getSK(): string
    {
        return $this->getPK();
    }

    public function setResultSet(Resultset $resultset)
    {
        $this->resultset = $resultset;
    }

    public function setParticipants(array $participantIds): self
    {
        $this->participantIds = $participantIds;
        return $this;
    }

    public function getResultSet(): ?Resultset
    {
       return $this->resultset;
    }

    public function create(): Conversation
    {
        $conversation = (new CreateConversation($this))->execute();

        if (!empty($this->participantIds)) {
            Chat::addParticipants($conversation->getConversationId(), $this->participantIds);
        }

        return $conversation;
    }

    public function update()
    {
        Chat::updateConversation($this->getConversationId(), $this->getAttributes());
    }
}