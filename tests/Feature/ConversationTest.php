<?php

namespace Musonza\LaravelDynamodbChat\Tests\Feature;

use Aws\DynamoDb\Exception\DynamoDbException;
use Bego\Component\Resultset;
use Bego\Condition;
use Musonza\LaravelDynamodbChat\Tests\TestCase;

class ConversationTest extends TestCase
{
    public function testCreateConversation()
    {
        $subject = 'Conversation 1';
        $conversation = $this->chat->conversation()
            ->setSubject($subject)
            ->setAttributes([
                'IsPrivate' => 1,
                'Description' => 'My description',
            ])
            ->create();

        $conditions = [Condition::attribute('SK')->eq($conversation->getSK())];
        $response = $this->query(
            $conversation->getPK(),
            $conditions
        );

        $this->assertEquals($subject, $response->first()->attribute('Subject'));
        $this->assertEquals(1, $response->first()->attribute('IsPrivate'));
        $this->assertEquals('My description', $response->first()->attribute('Description'));
        $this->assertEquals(1, $response->count(), 'One conversation created');
    }

    public function testGetConversationById()
    {
        $conversation = $this->chat->conversation()
            ->setSubject('Hello')
            ->create();

        $this->assertNull($conversation->getResultSet());

        $conversation = $this->chat->conversation($conversation->getId())->first();
        $this->assertInstanceOf(Resultset::class, $conversation->getResultSet());
    }

    public function testCreateConversationWithParticipants()
    {
        $conversation = $this->chat->conversation()
            ->setSubject('Group Chat One')
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

    public function testAddConversationParticipants()
    {
        $conversation = $this->chat->conversation()
            ->setSubject('Conversation')
            ->create();

        $this->chat->addParticipants($conversation->getId(), [
            'james',
            'jane',
            'john'
        ]);

        $conditions = [Condition::attribute('SK')->beginsWith('PARTICIPANT#')];
        $response = $this->query(
            $conversation->getPK(),
            $conditions
        );

        $this->assertEquals(3, $response->count());
    }

    public function testRemoveConversationParticipants()
    {
        $participants = [];

        for ($i = 0; $i < 10; $i++) {
            $participants[] = "user{$i}";
        }

        $conversation = $this->chat->conversation()
            ->setSubject('Conversation')
            ->setParticipants($participants)
            ->create();

        $this->chat->deleteParticipants($conversation->getId(), ['user0', 'user1']);

        $conditions = [
            Condition::attribute('SK')->beginsWith('PARTICIPANT#')
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

    public function testWithParticipantsExceedingBatchLimit()
    {
        $participants = [];

        for ($i = 0; $i < 50; $i++) {
            $participants[] = "user{$i}";
        }

        $conversation = $this->chat->conversation()
            ->setSubject('Group Two')
            ->setParticipants($participants)
            ->create();

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

    public function testUpdateConversation()
    {
        $subject = 'Conversation 1';
        $conversation = $this->chat->conversation()
            ->setSubject($subject)
            ->create();

        $conversationId = $conversation->getId();
        $newSubject = 'Conversation updated';
        $description = 'This is a description.';

        $updated = $this->chat->conversation($conversationId)
            ->setAttributes([
                'Subject' => $newSubject,
                'Description' => $description,
                // ... unchanged data
            ])
            ->update();

        $this->assertTrue($updated);

        $response = $this->query(
            $conversation->getPK(),
            Condition::attribute('SK')->eq($conversation->getSK())
        );

        $this->assertEquals($newSubject, $response->first()->attribute('Subject'));
        $this->assertEquals($description, $response->first()->attribute('Description'));
    }
}
