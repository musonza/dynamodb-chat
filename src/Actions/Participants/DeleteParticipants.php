<?php

namespace Musonza\LaravelDynamodbChat\Actions\Participants;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Result;
use Illuminate\Support\Str;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Actions\ConversationClient;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;
use Musonza\LaravelDynamodbChat\Exceptions\InvalidConversationParticipants;

class DeleteParticipants extends Action
{
    protected Conversation $conversation;
    protected array $participantIds;

    public function __construct(Conversation $conversation, array $participantIds)
    {
        $this->conversation = $conversation;
        $this->participantIds = $participantIds;
    }

    public function execute(): void
    {
        $conversationClient = (app(ConversationClient::class))->setConversationId($this->conversation->getId());
        $item = $conversationClient->first()
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
                $this->deleteItems($batchItems);
                $batchItems = [];
                $batchItemsCount = 0;
            }

            $participation = app(Participation::class)->newInstance([
                'ConversationId' => $this->conversation->getId(),
                'Id' => $id,
            ]);

            $batchItems[] = [
                'DeleteRequest' => ['Key' => $participation->getPrimaryKey()]
            ];
            $batchItemsCount++;
        }

        if (!empty($batchItems)) {
            $this->deleteItems($batchItems);
        }

        $this->decrement($this->conversation, count($this->participantIds));
    }

    protected function decrement(Conversation $conversation, int $count): Result
    {
        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);
        $params = [
            'TableName' => ConfigurationManager::getTableName(),
            'Key' => $conversation->getPrimaryKey(),
            'ExpressionAttributeValues' => [
                ':inc' => ['N' => $count]
            ],
            'UpdateExpression' => 'SET ParticipantCount = ParticipantCount - :inc',
            'ReturnValues' => 'UPDATED_NEW'
        ];

        return $client->updateItem($params);
    }
}