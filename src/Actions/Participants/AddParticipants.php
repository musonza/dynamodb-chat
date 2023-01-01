<?php

namespace Musonza\LaravelDynamodbChat\Actions\Participants;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Result;
use Illuminate\Support\Str;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Actions\Conversations\ConversationClient;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;
use Musonza\LaravelDynamodbChat\Exceptions\InvalidConversationParticipants;

class AddParticipants extends Action
{
    protected Conversation $conversation;
    protected array $participantIds;

    public function __construct(Conversation $conversation, array $participantIds)
    {
        $this->conversation = $conversation;
        $this->participantIds = $participantIds;
    }

    public function execute()
    {
        $item = (new ConversationClient($this->conversation->getId()))->first()
            ->getResultSet()
            ->first();

        if ($item->attribute('ParticipantCount')) {
            $isDirect = Str::startsWith($item->attribute('PK'), 'CONVERSATION#DIRECT');
            if ($isDirect) {
                throw new InvalidConversationParticipants(
                    $this->conversation,
                    InvalidConversationParticipants::PARTICIPANTS_IMMUTABLE
                );
            }
        }

        $batchItems = [];
        $batchItemsCount = 0;

        foreach ($this->participantIds as $id) {
            if ($batchItemsCount == ConfigurationManager::getBatchLimit()) {
                $this->saveItems($batchItems);
                $batchItems = [];
                $batchItemsCount = 0;
            }

            $participation = Participation::newInstance([
                'ConversationId' => $this->conversation->getId(),
                'Id' => $id,
            ]);

            $batchItems[] = $participation->toArray();

            $batchItemsCount++;
        }

        if (!empty($batchItems)) {
            $this->saveItems($batchItems);
        }

        $this->increment($this->conversation, count($this->participantIds));
    }

    protected function increment(Conversation $conversation, int $count): Result
    {
        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);
        $params = [
            'TableName' => ConfigurationManager::getTableName(),
            'Key' => $conversation->getPrimaryKey(),
            'ExpressionAttributeValues' => [
                ':inc' => ['N' => $count]
            ],
            'UpdateExpression' => 'SET ParticipantCount = ParticipantCount + :inc',
            'ReturnValues' => 'UPDATED_NEW'
        ];

        return $client->updateItem($params);
    }
}