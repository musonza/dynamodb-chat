<?php

namespace Musonza\LaravelDynamodbChat\Actions\Participants;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Result;
use Illuminate\Support\Str;
use Musonza\LaravelDynamodbChat\Actions\Action;
use Musonza\LaravelDynamodbChat\Actions\ConversationClient;
use Musonza\LaravelDynamodbChat\Configuration;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Entities\Participation;
use Musonza\LaravelDynamodbChat\Exceptions\InvalidConversationParticipants;

class AddParticipants extends Action
{
    protected ConversationClient $conversationClient;

    protected readonly Conversation $conversation;

    protected readonly Participation $participation;

    protected readonly array $participantIds;

    public function __construct(
        ConversationClient $conversationClient,
        Conversation $conversation,
        Participation $participation,
        array $participantIds
    ) {
        $this->conversationClient = $conversationClient;
        $this->conversation = $conversation;
        $this->participation = $participation;
        $this->participantIds = $participantIds;
    }

    public function execute(): void
    {
        $item = $this->conversationClient->conversationToItem($this->conversation->getId());

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
            if ($batchItemsCount == Configuration::getBatchLimit()) {
                $this->saveItems($batchItems);
                $batchItems = [];
                $batchItemsCount = 0;
            }

            $participation = $this->participation->newInstance([
                'ConversationId' => $this->conversation->getId(),
                'Id' => $id,
            ]);

            $batchItems[] = $participation->toArray();

            $batchItemsCount++;
        }

        if (! empty($batchItems)) {
            $this->saveItems($batchItems);
        }

        $this->increment($this->conversation, count($this->participantIds));
    }

    protected function increment(Conversation $conversation, int $count): Result
    {
        /** @var DynamoDbClient $client */
        $client = app(DynamoDbClient::class);
        $params = [
            'TableName' => Configuration::getTableName(),
            'Key' => $conversation->getPrimaryKey(),
            'ExpressionAttributeValues' => [
                ':inc' => ['N' => $count],
            ],
            'UpdateExpression' => 'SET ParticipantCount = ParticipantCount + :inc',
            'ReturnValues' => 'UPDATED_NEW',
        ];

        return $client->updateItem($params);
    }
}
