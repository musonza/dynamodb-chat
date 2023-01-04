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

        $attributes = [
            'ConversationId' => $this->conversation->getId(),
            'ParticipantId' => $this->participation->getParticipantExternalId(),
            'Message' => $this->text,
            'IsSender' => true,
            'ParentId' => null,
        ];

        if (! empty($this->data)) {
            $attributes['Data'] = $this->data;
        }

        $message = $this->createSenderMessage($attributes);

        $this->getTable()->put($message->toArray());

        $participantsResult = $this->getConversationParticipants($message);

        $this->batchSaveMessages($participantsResult, $attributes, $message);

        return $message;
    }

    private function createSenderMessage(array $attributes): Entity
    {
        $message = $this->message->newInstance($attributes);

        return $message->setSender($this->participation, $this->participation, $message->getId())
            ->setAttribute('Read', true);
    }

    /**
     * @throws Exception
     */
    private function validateParticipant(): void
    {
        $participant = $this->getTable()->query()
            ->key($this->conversation->getPK())
            ->condition(
                Condition::attribute('SK')->eq(Helpers::gs1skFromParticipantIdentifier($this->participation->getParticipantExternalId()))
            )->fetch();

        if (! $participant->count()) {
            throw new \Exception('Participant is not part of the conversation');
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

    private function batchSaveMessages(Resultset $participants, array $attributes, Entity $message): void
    {
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
            if (($this->participation->getParticipantExternalId() !== $recipient->getParticipantExternalId())) {
                $attributes['ParticipantId'] = $recipient->getParticipantExternalId();
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
}
