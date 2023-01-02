<?php

namespace Musonza\LaravelDynamodbChat\Actions;

use Bego\Item;
use Musonza\LaravelDynamodbChat\Actions\Conversations\ClearConversation;
use Musonza\LaravelDynamodbChat\Actions\Conversations\CreateConversation;
use Musonza\LaravelDynamodbChat\Actions\Conversations\GetConversation;
use Musonza\LaravelDynamodbChat\Actions\Conversations\UpdateConversation;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;
use Musonza\LaravelDynamodbChat\Exceptions\ConversationNotFoundException;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class ConversationClient
{
    protected Conversation $conversation;
    protected Participation $participation;
    protected array $attributes = [];

    public function __construct(Conversation $conversation, Participation $participation)
    {
        $this->conversation = $conversation;
        $this->participation = $participation;
    }

    public function conversationToItem(string $conversationId): Item
    {
        return $this->setConversationId($conversationId)
            ->first()
            ->getResultSet()
            ->first();
    }

    public function first(): Conversation
    {
        return (new GetConversation($this->conversation))->execute();
    }

    public function create(): Conversation
    {
        $conversation = $this->conversation->newInstance($this->attributes);
        return (new CreateConversation($conversation))->execute();
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function setParticipants(array $participants): static
    {
        $this->attributes['Participants'] = $participants;
        return $this;
    }

    public function setIsDirect(bool $isDirect): static
    {
        $this->attributes['IsDirect'] = $isDirect;
        return $this;
    }

    public function update(): ?bool
    {
        $this->conversation->setAttributes($this->attributes);
        return (new UpdateConversation($this->conversation))->execute();
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
        $this->conversation = $this->conversation->newInstance([
            'Id' => Helpers::directConversationKey($participantOne, $participantTwo)
        ]);

        $this->conversation = $this->first();

        $resultSet = $this->conversation->getResultSet();

        if ($resultSet && $resultSet->count() == 0) {
            throw new ConversationNotFoundException($this->conversation);
        }

        return $this->conversation;
    }

    public function clear(string $participantId): void
    {
        $participation = $this->participation->newInstance([
            'ConversationId' => $this->conversation->getId(),
            'Id' => $participantId,
        ]);
        (new ClearConversation($this->conversation, $participation))->execute();
    }

    public function setConversationId(?string $conversationId): self
    {
        $this->conversation = $this->conversation->newInstance(['Id' => $conversationId]);
        return $this;
    }
}