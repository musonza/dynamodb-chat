<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Bego\Component\Resultset;
use Bego\Condition;
use Bego\Exception;
use Bego\Item;
use Illuminate\Support\Collection;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Configuration;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Entity;
use Musonza\LaravelDynamodbChat\Entities\Message;
use Musonza\LaravelDynamodbChat\Entities\Participation;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class CreateMessage extends Action
{
    protected readonly Conversation $conversation;

    protected readonly Participation $participation;

    protected readonly Message $message;

    protected string $text = '';

    protected array $data = [];

    public function __construct(
        Conversation $conversation,
        Participation $participation,
        Message $message,
        string $text,
        array $data = []
    ) {
        $this->conversation = $conversation;
        $this->participation = $participation;
        $this->message = $message;
        $this->text = $text;
        $this->data = $data;
    }

    /**
     * @throws Exception
     */
    public function execute(): Entity
    {
        $this->validateParticipant();

        $message = $this->createAndSaveMessageFromSender();

        $this->batchSaveMessagesForRecipients($message);

        return $message;
    }

    /**
     * @throws Exception
     */
    private function validateParticipant(): void
    {
        $participant = $this->getTable()->query()
            ->key($this->conversation->getPK())
            ->condition(
                Condition::attribute('SK')->eq(Helpers::gs1skFromParticipantIdentifier($this->participation->getId()))
            )->fetch();

        if (! $participant->count()) {
            throw new Exception('Participant is not part of the conversation');
        }
    }

    private function createAndSaveMessageFromSender(): Entity
    {
        $message = $this->message->newInstance($this->getMessageAttributes());
        $message = $message->setSender($this->participation, $this->participation, $message->getId())
            ->setAttribute('Read', true);

        $this->getTable()->put($message->toArray());

        return $message;
    }

    protected function getMessageAttributes(): array
    {
        $attributes = [
            'ConversationId' => $this->conversation->getId(),
            'ParticipantId' => $this->participation->getId(),
            'Message' => $this->text,
            'IsSender' => true,
            'ParentId' => null,
        ];

        if (! empty($this->data)) {
            $attributes['Data'] = $this->data;
        }

        return $attributes;
    }

    /**
     * @throws Exception
     */
    private function batchSaveMessagesForRecipients(Entity $message): void
    {
        $participants = $this->getConversationParticipants($message);

        $recipientMessageData = (new Collection($participants))
            ->filter(fn (Item $item) => $item->attribute('ParticipantId') !== $this->participation->getId())
            ->map(fn ($item) => $this->recipientMessageData($item, $message));

        $table = $this->getTable();

        foreach ($recipientMessageData->chunk(Configuration::getBatchLimit()) as $chunk) {
            $table->putBatch($chunk->toArray());
        }
    }

    /**
     * @throws Exception
     */
    private function getConversationParticipants(Entity $message): Resultset
    {
        return $this->getTable()->query()
            ->key($message->toArray()[Entity::PARTITION_KEY])
            ->condition(Condition::attribute(Entity::SORT_KEY)->beginsWith('PARTICIPANT#'))
            ->fetch();
    }

    private function recipientMessageData(Item $item, Entity $message): array
    {
        $recipient = $this->participation->newInstance([
            'Id' => $item->attribute('ParticipantId'),
            'ConversationId' => $this->conversation->getId(),
        ]);

        $attributes = $this->getMessageAttributes();
        $attributes['ParticipantId'] = $recipient->getId();
        $attributes['IsSender'] = false;
        $attributes['Read'] = false;
        $attributes['ParentId'] = $message->getId();

        $recipientMessage = $this->message->newInstance($attributes);

        return $recipientMessage->setSender($this->participation, $recipient, $message->getId())
            ->toArray();
    }
}
