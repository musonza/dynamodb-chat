<?php

namespace Musonza\LaravelDynamodbChat\Actions;

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
    protected ?Conversation $conversation;
    protected array $attributes = [];

//    public function __construct(string $conversationId = null)
//    {
//        if ($conversationId) {
//            $this->conversation = Conversation::newInstance(['Id' => $conversationId]);
//        }
//    }

    public function first(): Conversation
    {
        return (new GetConversation($this->conversation))->execute();
    }

    public function create(): Conversation
    {
        return (new CreateConversation($this->attributes))->execute();
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

    public function update(): bool
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
        $this->conversation = Conversation::newInstance([
            'Id' => Helpers::directConversationKey($participantOne, $participantTwo)
        ]);

        $this->conversation = $this->first();

        if ($this->conversation->getResultSet()->count() == 0) {
            throw new ConversationNotFoundException($this->conversation);
        }

        return $this->conversation;
    }

    public function clear(string $participantId): void
    {
        $participation = Participation::newInstance([
            'ConversationId' => $this->conversation->getId(),
            'Id' => $participantId,
        ]);
        (new ClearConversation($this->conversation, $participation))->execute();
    }

    public function setConversationId(?string $conversationId): self
    {
        $this->conversation = Conversation::newInstance(['Id' => $conversationId]);
        return $this;
    }
}