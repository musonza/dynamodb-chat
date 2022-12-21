<?php

namespace Musonza\LaravelDynamodbChat\Actions;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Bego\Condition;
use Bego\Database;
use Bego\Exception;
use Illuminate\Support\Str;
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
        /** @var Database $db */
        $db = app(Database::class);
        $message = (new Message($this->participation, $this->text, true))
            ->setRead(1);

        $messagesTable = $db->table($message);
        $messagesTable->put($message->toArray());

        // get all participants
        $participantsQuery = $messagesTable->query()
            ->key($message->toArray()['PK'])
            ->condition(Condition::attribute('SK')->beginsWith('PARTICIPANT#'))
            ->fetch();

        $index = 0;
        $batchItems = [];
        $batchCount = 0;

        do {
            if ($batchCount == ConfigurationManager::getBatchLimit()) {
                $messagesTable->putBatch($batchItems);
                $batchItems = [];
                $batchCount = 0;
            }

            $item = $participantsQuery->item($index);
            $participantId = Str::replace('PARTICIPANT#', '', $item->SK);
            $participation = new Participation($this->conversation, $participantId);

            // Sender already has an entry for the message
            if (($this->participation->getParticipantIdentifier() !== $participation->getParticipantIdentifier())) {
                $m = new Message($participation, $this->text);
                $m->setSender($this->participation, $participation);
                $batchItems[] = $m->toArray();
                $batchCount++;
            }

            $index++;
        } while($index < $participantsQuery->count());

        if (!empty($batchItems)) {
            $messagesTable->putBatch($batchItems);
        }
    }
}