<?php

namespace Musonza\LaravelDynamodbChat\Actions\Conversations;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Exceptions\ConversationExistsException;
use Musonza\LaravelDynamodbChat\Exceptions\InvalidConversationParticipants;
use Musonza\LaravelDynamodbChat\Facades\ChatFacade as Chat;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class CreateConversation extends Action
{
    protected readonly Conversation $conversation;

    private readonly array $participantIds;

    private array $conditions = [];

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
        $this->participantIds = $this->conversation->attribute('Participants', []);
    }

    public function execute(): Conversation
    {
        if ($this->conversation->isDirect()) {
            $this->handleDirectConversation();
        }

        $this->saveConversation();

        $this->addParticipants();

        return $this->conversation;
    }

    private function handleDirectConversation(): void
    {
        $this->validateParticipantsInDirectConversation();
        $this->generateAndAssignIdToDirectConversation();
        $this->conditions = [
            Condition::attribute('PK')->notEq($this->conversation->getPK()),
        ];
    }

    private function validateParticipantsInDirectConversation(): void
    {
        if (empty($this->participantIds) || count($this->participantIds) !== 2) {
            throw new InvalidConversationParticipants(
                InvalidConversationParticipants::REQUIRED_PARTICIPANT_COUNT
            );
        }
    }

    private function generateAndAssignIdToDirectConversation(): void
    {
        $this->conversation->setAttribute(
            'Id',
            Helpers::directConversationKey($this->participantIds[0], $this->participantIds[1])
        );
    }

    private function saveConversation(): void
    {
        if (! $this->getTable()->put($this->conversation->toArray(), $this->conditions)) {
            throw new ConversationExistsException($this->conversation);
        }
    }

    private function addParticipants(): void
    {
        if (! empty($this->participantIds)) {
            Chat::addParticipants($this->conversation->getId(), $this->participantIds);
        }
    }
}
