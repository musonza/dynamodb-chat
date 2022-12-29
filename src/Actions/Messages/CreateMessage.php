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

class CreateMessage extends Action
{
    protected Message $message;
    protected Conversation $conversation;
    protected Participation $participation;
    protected string $text = '';
    protected array $data = [];

    public function __construct(
        Conversation $conversation,
        Participation $participation,
        string $text,
        array $data = []
    ) {
        $this->conversation = $conversation;
        $this->participation = $participation;
        $this->text = $text;
        $this->data = $data;
    }

    /**
     * @throws Exception
     */
    public function execute(): Message
    {
        $message = new Message($this->participation, $this->text, true);
        $message = $message->setData($this->data)
            ->setOriginalAndClonedMessageKeys($message->getId(), $message->getId())
            ->setRead(1);

        $table = $this->getTable();
        // Check if user can send a message
        $participant = $table->query()
            ->key($this->conversation->getPK())
            ->condition(
                Condition::attribute('SK')->eq("PARTICIPANT#{$this->participation->getParticipantIdentifier()}")
            )->fetch();

        if (!$participant->count()) {
            throw new \Exception("Participant is not part of the conversation");
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
            $recipient = new Participation($this->conversation, $item->attribute('ParticipantId'));

            // Sender already has an entry for the message
            if (($this->participation->getParticipantIdentifier() !== $recipient->getParticipantIdentifier())) {
                $recipientMsg = (new Message($recipient, $this->text));
                $batchItems[] = $recipientMsg->setSender($this->participation, $recipient)
                    ->setData($this->data)
                    ->setOriginalAndClonedMessageKeys($message->getId(), $recipientMsg->getId())
                    ->toArray();

                $batchCount++;
            }

            $index++;
        } while($index < $participantsQuery->count());

        if (!empty($batchItems)) {
            $table->putBatch($batchItems);
        }

        return $message;
    }
}