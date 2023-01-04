<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Bego\Component\Resultset;
use Bego\Condition;
use Bego\Exception;
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

        $attributes = $this->getMessageAttributes();

        $message = $this->createAndSaveMessageFromSender($attributes);

        $this->batchSaveMessagesForRecipients($attributes, $message);

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
            throw new \Exception('Participant is not part of the conversation');
        }
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

    private function createAndSaveMessageFromSender(array $attributes): Entity
    {
        $message = $this->message->newInstance($attributes);
        $message = $message->setSender($this->participation, $this->participation, $message->getId())
            ->setAttribute('Read', true);

        $this->getTable()->put($message->toArray());

        return $message;
    }

    /**
     * @throws Exception
     */
    private function batchSaveMessagesForRecipients(array $attributes, Entity $message): void
    {
        $participants = $this->getConversationParticipants($message);
        $table = $this->getTable();
        $index = 0;
        $batchItems = [];
        $batchCount = 0;

        do {
            if ($batchCount == Configuration::getBatchLimit()) {
                $table->putBatch($batchItems);
                $batchItems = [];
                $batchCount = 0;
            }

            $item = $participants->item($index);
            $recipient = $this->participation->newInstance([
                'Id' => $item->attribute('ParticipantId'),
                'ConversationId' => $this->conversation->getId(),
            ]);

            // Sender already has an entry for the message
            if (($this->participation->getId() !== $recipient->getId())) {
                $attributes['ParticipantId'] = $recipient->getId();
                $attributes['IsSender'] = false;
                $attributes['Read'] = false;
                $attributes['ParentId'] = $message->getId();
                $recipientMsg = $this->message->newInstance($attributes);
                $batchItems[] = $recipientMsg->setSender($this->participation, $recipient, $message->getId())
                    ->toArray();

                $batchCount++;
            }

            $index++;
        } while ($index < $participants->count());

        if (! empty($batchItems)) {
            $table->putBatch($batchItems);
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
}
