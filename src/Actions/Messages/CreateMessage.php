<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Bego\Condition;
use Bego\Exception;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
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
     * @throws \Exception
     */
    public function execute(): Entity
    {
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

        $message = $this->message->newInstance($attributes);
        $message = $message->setSender($this->participation, $this->participation, $message->getId())
            ->setAttribute('Read', true);

        $table = $this->getTable();
        // Check if user can send a message
        $participant = $table->query()
            ->key($this->conversation->getPK())
            ->condition(
                Condition::attribute('SK')->eq(Helpers::gs1skFromParticipantIdentifier($this->participation->getParticipantExternalId()))
            )->fetch();

        if (! $participant->count()) {
            throw new \Exception('Participant is not part of the conversation');
        }

        $table->put($message->toArray());

        // get all participants
        $participantsQuery = $table->query()
            ->key($message->toArray()[Entity::PARTITION_KEY])
            ->condition(Condition::attribute(Entity::SORT_KEY)->beginsWith('PARTICIPANT#'))
            ->fetch();

        $index = 0;
        $batchItems = [];
        $batchCount = 0;

        do {
            if ($batchCount == ConfigurationManager::getBatchLimit()) {
                $table->putBatch($batchItems);
                $batchItems = [];
                $batchCount = 0;
            }

            $item = $participantsQuery->item($index);
            $recipient = $this->participation->newInstance([
                'Id' => $item->attribute('ParticipantId'),
                'ConversationId' => $this->conversation->getId(),
            ]);

            // Sender already has an entry for the message
            if (($this->participation->getParticipantExternalId() !== $recipient->getParticipantExternalId())) {
                $attributes['ParticipantId'] = $recipient->getParticipantExternalId();
                $attributes['IsSender'] = false;
                $attributes['ParentId'] = $message->getId();
                $recipientMsg = $this->message->newInstance($attributes);
                $batchItems[] = $recipientMsg->setSender($this->participation, $recipient, $message->getId())
                    ->toArray();

                $batchCount++;
            }

            $index++;
        } while ($index < $participantsQuery->count());

        if (! empty($batchItems)) {
            $table->putBatch($batchItems);
        }

        return $message;
    }
}
