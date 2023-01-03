<?php

namespace Musonza\LaravelDynamodbChat\Tests\Feature;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Tests\TestCase;

class ParticipantsTest extends TestCase
{
    public function testCreateConversationWithParticipants()
    {
        $conversation = $this->chat->conversation()
            ->setAttributes([
                'Subject' => 'Group Chat One',
            ])
            ->setParticipants(['jane', 'john'])
            ->create();

        $this->assertEquals('Group Chat One', $conversation->getSubject());

        $conversationPartitionKey = $conversation->getPK();
        $conditions = [Condition::attribute('SK')->beginsWith('PARTICIPANT#')];
        $response = $this->query(
            $conversationPartitionKey,
            $conditions
        );

        $this->assertEquals(2, $response->count(), 'Two participants created');

        $items = [];

        foreach ($response->toArrayOfObjects() as $object) {
            $items[$object->attribute('ParticipantId')] = $object;
        }

        $jane = $items['jane'];
        $john = $items['john'];

        $this->assertEquals('PARTICIPANT#jane', $jane->attribute('GSI1PK'));
        $this->assertEquals($conversationPartitionKey, $jane->attribute('GSI1SK'));
        $this->assertEquals('PARTICIPANT#jane', $jane->attribute('SK'));
        $this->assertEquals('PARTICIPATION', $jane->attribute('Type'));

        $this->assertEquals('PARTICIPANT#john', $john->attribute('GSI1PK'));
        $this->assertEquals($conversationPartitionKey, $john->attribute('GSI1SK'));
        $this->assertEquals('PARTICIPANT#john', $john->attribute('SK'));
        $this->assertEquals('PARTICIPATION', $john->attribute('Type'));

        $c = $this->chat->conversation($conversation->getId())->first();

        $this->assertEquals(
            2,
            $c->getResultSet()->first()->attribute('ParticipantCount')
        );
    }

    public function testWithParticipantsExceedingBatchLimit()
    {
        $participants = [];

        for ($i = 0; $i < 50; $i++) {
            $participants[] = "user{$i}";
        }

        $conversation = $this->chat->conversation()
            ->setAttributes([
                'Subject' => 'Group Two',
            ])
            ->setParticipants($participants)
            ->create();

        // 'Participants' => $participants,

        $this->chat->messaging($conversation->getId())
            ->message('user10', 'Hello')
            ->send();

        $conditions = [Condition::attribute('SK')->beginsWith('MSG#')];
        $response = $this->query(
            $conversation->getPK(),
            $conditions
        );

        $this->assertEquals(50, $response->count(), 'Each participant receives a message');
    }

    public function testRemoveConversationParticipants()
    {
        $participants = [];

        for ($i = 0; $i < 10; $i++) {
            $participants[] = "user{$i}";
        }

        $conversation = $this->chat->conversation()
            ->setAttributes([
                'Subject' => 'Conversation',
            ])
            ->setParticipants($participants)
            ->create();

        $this->chat->deleteParticipants($conversation->getId(), ['user0', 'user1']);

        $conditions = [
            Condition::attribute('SK')->beginsWith('PARTICIPANT#'),
        ];
        $response = $this->query(
            $conversation->getPK(),
            $conditions,
        );

        $this->assertEquals(8, $response->count());

        $c = $this->chat->conversation($conversation->getId())->first();
        $this->assertEquals(
            8,
            $c->getResultSet()->first()->attribute('ParticipantCount')
        );
    }

    public function testAddConversationParticipants()
    {
        $conversation = $this->chat->conversation()
            ->setAttributes(['Subject' => 'Conversation'])
            ->create();

        $this->chat->addParticipants($conversation->getId(), [
            'james',
            'jane',
            'john',
        ]);

        $conditions = [Condition::attribute('SK')->beginsWith('PARTICIPANT#')];
        $response = $this->query(
            $conversation->getPK(),
            $conditions
        );

        $this->assertEquals(3, $response->count());
    }
}
