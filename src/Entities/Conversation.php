<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Aws\DynamoDb\DynamoDbClient;
use Bego\Component\Resultset;
use Chat;
use Illuminate\Support\Carbon;
use Musonza\LaravelDynamodbChat\Actions\Conversations\ClearConversation;
use Musonza\LaravelDynamodbChat\Actions\Conversations\CreateConversation;
use Musonza\LaravelDynamodbChat\Actions\Conversations\GetConversation;
use Musonza\LaravelDynamodbChat\Actions\Conversations\UpdateConversation;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Exceptions\ConversationNotFoundException;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class Conversation extends Entity implements Contract
{
    const CONVERSATION_PK_PREFIX = 'CONVERSATION#%s';

    const ENTITY_TYPE_DIRECT = 'CONVERSATION_DIRECT';

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

    protected bool $isDirect = false;

    protected string $entityType = 'CONVERSATION';

    public function __construct($conversationId = null, Carbon $createdAt = null)
    {
        $this->createdAt = $createdAt ?? now();
        $this->conversationId = $conversationId ?? Helpers::generateKSUID($this->createdAt);
    }

    public function setId(string $id)
    {
        $this->conversationId = $id;
    }

    public function getId(): string
    {
        return $this->conversationId;
    }

    public function setSubject(string $subject): Conversation
    {
        $this->subject = $subject;
        return $this;
    }

    public function setIsDirect(bool $isDirect): Conversation
    {
        $this->isDirect = $isDirect;
        return $this;
    }

    public function isDirect(): bool
    {
        return $this->isDirect;
    }

    public function makePrivate(bool $isPrivate): Conversation
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
            'S' => sprintf(self::CONVERSATION_PK_PREFIX,  $this->getId())
        ];
    }

    public function getSortKey(): array
    {
        return [
            'S' => sprintf(self::CONVERSATION_PK_PREFIX,  $this->getId())
        ];
    }

    public function toItem(): array
    {
        return[
            ...$this->getPrimaryKey(),
            'Type' => ['S' => $this->getEntityType()],
            'Subject' => ['S' => $this->getSubject()],
            'ParticipantCount' => ['N' => 0],
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

    public function setParticipants(array $participantIds): Conversation
    {
        $this->participantIds = $participantIds;
        return $this;
    }

    public function getParticipantIds(): array
    {
        return $this->participantIds;
    }

    public function getResultSet(): ?Resultset
    {
       return $this->resultset;
    }

    public function create(): Conversation
    {
        $conversation = (new CreateConversation($this))->execute();

        if (!empty($this->participantIds)) {
            Chat::addParticipants($conversation->getId(), $this->participantIds);
        }

        return $conversation;
    }

    public function update(): ?bool
    {
        return (new UpdateConversation($this))->execute();
    }

    public function clear(string $participantId)
    {
        (new ClearConversation($this, new Participation($this, $participantId)))->execute();
    }

    public function first(): Conversation
    {
        (new GetConversation($this))->execute();
        return $this;
    }

    public function firstOrFail(): Conversation
    {
        $this->first();

        if (is_null($this->resultset->first())) {
            throw new ConversationNotFoundException($this);
        }

        return $this;
    }

    /**
     * Order of participant Ids doesn't matter
     *
     * @param string $participantOne
     * @param string $participantTwo
     * @return Conversation
     */
    public function getDirectConversation(string $participantOne, string $participantTwo): Conversation
    {
        $this->setId(Helpers::directConversationKey($participantOne, $participantTwo));
        $this->first();

        if ($this->getResultSet()->count() == 0) {
            throw new ConversationNotFoundException($this);
        }

        return $this;
    }

    public function setType(string $entityType)
    {
        $this->entityType = $entityType;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }
}