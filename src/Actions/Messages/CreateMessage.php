<?php

namespace Musonza\LaravelDynamodbChat\Actions\Messages;

use Bego\Condition;
use Bego\Database;
use Bego\Exception;
use Illuminate\Support\Str;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Message;
use Musonza\LaravelDynamodbChat\Entities\Participation;

class CreateMessage extends Action
{
    protected Message $message;
    protected Conversation $conversation;
    protected Participation $participation;
    protected string $text = '';

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function fromParticipant($participant): self
    {
        $this->participation = new Participation($this->conversation, $participant);
        return $this;
    }

    public function message(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function send()
    {
        $this->execute();
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $message = (new Message($this->participation, $this->text, true))
            ->setRead(1);

        $table = $this->getTable();
        $table->put($message->toArray());

        // get all participants
        $participantsQuery = $table->query()
            ->key($message->toArray()['PK'])
            ->condition(Condition::attribute('SK')->beginsWith('PARTICIPANT#'))
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
                $batchItems[] = (new Message($recipient, $this->text))
                    ->setSender($this->participation, $recipient)
                    ->toArray();

                $batchCount++;
            }

            $index++;
        } while($index < $participantsQuery->count());

        if (!empty($batchItems)) {
            $table->putBatch($batchItems);
        }
    }
}