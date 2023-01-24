<?php

namespace Musonza\LaravelDynamodbChat\Tests\Feature;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Tests\TestCase;

class MessageDeliveryStatusTest extends TestCase
{
    public function testMarkMessageAsSentWhenRecipientEntriesCreated()
    {
        $conversation = $this->createConversation();
        $senderMessage = $this->chat->messaging($conversation->getId())
            ->message(self::PARTICIPANTS[0], 'Hello')
            ->send();

        $conditions = [Condition::attribute('SK')->beginsWith('MSG#')];
        $response = $this->query(
            $conversation->getPK(),
            $conditions,
            null,
            Condition::attribute('ParticipantId')->eq(self::PARTICIPANTS[0])
        );

        $this->assertEquals('SENT', $response->first()->attribute('Status'));
    }
}
