<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Bego\Component\Resultset;
use Chat;
use Illuminate\Support\Carbon;
use Musonza\LaravelDynamodbChat\Actions\Conversations\ClearConversation;
use Musonza\LaravelDynamodbChat\Actions\Conversations\GetConversation;
use Musonza\LaravelDynamodbChat\Actions\Conversations\UpdateConversation;
use Musonza\LaravelDynamodbChat\Exceptions\ConversationNotFoundException;

class Conversation extends Entity implements Contract
{
    const ENTITY_TYPE_DIRECT = 'CONVERSATION_DIRECT';
    protected string $subject = 'Conversation';
    protected Carbon $createdAt;
    /**
     * Result from query on a Conversation.
     * @var Resultset|null
     */
    protected ?Resultset $resultset = null;

    /**
     * Specifies whether the conversation is private or public.
     * @var bool
     */
    protected bool $isPrivate = false;
    protected string $entityType = 'CONVERSATION';

    public function isDirect(): bool
    {
        return $this->getAttribute('IsDirect', false);
    }

    public function getSubject(): ?string
    {
        return $this->getAttribute('Subject');
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
            'S' => $this->getId()
        ];
    }

    public function toItem(): array
    {
        return[
            ...$this->getPrimaryKey(),
            'Type' => ['S' => $this->getEntityType()],
            'Subject' => ['S' => $this->getAttribute('Subject')],
            'ParticipantCount' => ['N' => 0],
            'CreatedAt' => ['S' => $this->getAttribute('CreatedAt')],
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
}