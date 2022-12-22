<?php

namespace Musonza\LaravelDynamodbChat\Tests\Feature;

use Bego\Component\Resultset;
use Bego\Condition;
use Musonza\LaravelDynamodbChat\Chat;
use Musonza\LaravelDynamodbChat\Entities\Conversation;
use Musonza\LaravelDynamodbChat\Exceptions\ConversationExistsException;
use Musonza\LaravelDynamodbChat\Exceptions\InvalidConversationParticipants;
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

        $response = $this->query(
            $conversation->getPK(),
            Condition::attribute('SK')->eq($conversation->getSK())
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

        $conversation = $this->chat->getConversationById($conversation->getConversationId());
        $this->assertInstanceOf(Resultset::class, $conversation->getResultSet());
    }

    public function testCreateConversationWithParticipants()
    {
        $conversation = $this->chat->conversation()
            ->setSubject('Group ChatFacade One')
            ->setParticipants(['jane', 'john'])
            ->create();

        $this->assertEquals('Group ChatFacade One', $conversation->getSubject());

        $conversationPartitionKey = $conversation->getPK();
        $response = $this->query(
            $conversationPartitionKey,
            Condition::attribute('SK')->beginsWith('PARTICIPANT#')
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
    }

    public function testAddConversationParticipants()
    {
        $conversation = $this->chat->conversation()
            ->setSubject('Conversation')
            ->create();

        $this->chat->addParticipants($conversation->getConversationId(), [
            'james',
            'jane',
            'john'
        ]);

        $response = $this->query(
            $conversation->getPK(),
            Condition::attribute('SK')->beginsWith('PARTICIPANT#')
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

        $this->chat->deleteParticipants($conversation->getConversationId(), ['user0', 'user1']);
        $response = $this->query(
            $conversation->getPK(),
            Condition::attribute('SK')->beginsWith('PARTICIPANT#')
        );

        $this->assertEquals(8, $response->count());
    }

    public function testSendMessage()
    {
        $conversation = $this->chat->conversation()
            ->setSubject('Group Two')
            ->setParticipants(['messi', 'ronaldo', 'aguero'])
            ->create();

        $conversationId = $conversation->getConversationId();

        $this->chat->messaging($conversationId)
            ->fromParticipant('ronaldo')
            ->message('Congratulations you are the G.O.A.T')
            ->send();

        $response = $this->query(
            $conversation->getPK(),
            Condition::attribute('SK')->beginsWith('MSG#')
        );

        $this->assertEquals(3, $response->count(), 'Each participant receives a message');

        $items = [];

        foreach ($response->toArrayOfObjects() as $item) {
            $items[$item->GSI2SK] = $item;
        }

        $this->assertEquals(1, $items['PARTICIPANT#ronaldo']->attribute('Read'), 'Sender message marked as read');
        $this->assertEquals(0, $items['PARTICIPANT#messi']->attribute('Read'));
        $this->assertEquals(0, $items['PARTICIPANT#aguero']->attribute('Read'));
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

        $this->chat->messaging($conversation->getConversationId())
            ->fromParticipant('user10')
            ->message('Hello')
            ->send();

        $response = $this->query(
            $conversation->getPK(),
            Condition::attribute('SK')->beginsWith('MSG#')
        );

        $this->assertEquals(50, $response->count(), 'Each participant receives a message');
    }

    public function testUpdateConversation()
    {
        $subject = 'Conversation 1';
        $conversation = $this->chat->conversation()
            ->setSubject($subject)
            ->create();

        $conversationId = $conversation->getConversationId();
        $newSubject = 'Conversation updated';
        $description = 'This is a description.';

        $updated = $this->chat->conversation($conversationId)
            ->setAttributes([
                'Subject' => $newSubject,
                'Description' => $description,
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
