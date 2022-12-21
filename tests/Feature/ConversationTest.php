<?php

namespace Musonza\LaravelDynamodbChat\Tests\Feature;

use Bego\Condition;
use Illuminate\Support\Str;
use Musonza\LaravelDynamodbChat\Chat;
use Musonza\LaravelDynamodbChat\Tests\TestCase;

class ConversationTest extends TestCase
{
    protected Chat $chat;

    public function setUp(): void
    {
        parent::setUp();
        $this->chat = app(Chat::class);
    }

    public function testCreateConversation()
    {
        $subject = 'Conversation 1';
        $conversation = $this->chat->createConversation($subject);
        $conversationPartitionKey = array_values($conversation->getPartitionKey())[0];
        $response = $this->query(
            $conversationPartitionKey,
            Condition::attribute('SK')->eq($conversationPartitionKey)
        );

        $this->assertEquals($subject, $response->first()->attribute('Subject'));
        $this->assertEquals(1, $response->count(), 'One conversation created');
    }

    public function testCreateConversationWithParticipants()
    {
        $conversation = $this->chat->createConversation('Group Chat One', ['jane', 'john']);
        $this->assertEquals('Group Chat One', $conversation->getSubject());

        $conversationPartitionKey = array_values($conversation->getPartitionKey())[0];
        $response = $this->query(
            $conversationPartitionKey,
            Condition::attribute('SK')->beginsWith('PARTICIPANT#')
        );

        $this->assertEquals(2, $response->count(), 'Two participants created');

        $items = [];

        foreach ($response->toArrayOfObjects() as $object) {
            $items[Str::replace('PARTICIPANT#', '', $object->SK)] = $object;
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
        $conversation = $this->chat->createConversation('Conversation');
        $this->chat->addParticipants($conversation->getConversationId(), [
            'james',
            'jane',
            'john'
        ]);

        $conversationPartitionKey = array_values($conversation->getPartitionKey())[0];
        $response = $this->query(
            $conversationPartitionKey,
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

        $conversation = $this->chat->createConversation('Conversation', $participants);

        $this->chat->deleteParticipants($conversation->getConversationId(), ['user0', 'user1']);

        $conversationPartitionKey = array_values($conversation->getPartitionKey())[0];
        $response = $this->query(
            $conversationPartitionKey,
            Condition::attribute('SK')->beginsWith('PARTICIPANT#')
        );

        $this->assertEquals(8, $response->count());
    }

    public function testSendMessage()
    {
        $conversation = $this->chat->createConversation('Group Two', ['messi', 'ronaldo', 'aguero']);
        $conversationId = $conversation->getConversationId();
        $this->chat->messaging($conversationId)
            ->fromParticipant('ronaldo')
            ->message('Congratulations you are the G.O.A.T')
            ->send();

        $response = $this->query(
            array_values($conversation->getPartitionKey())[0],
            Condition::attribute('SK')->beginsWith('MSG#')
        );

        $this->assertEquals(3, $response->count(), 'Each participant receives a message');

        $items = [];

        foreach ($response->toArrayOfObjects() as $item) {
            $items[$item->GSI2SK] = $item;
        }

        $this->assertEquals(1, $items['PARTICIPANT#ronaldo']->Read, 'Sender message marked as read');
        $this->assertEquals(0, $items['PARTICIPANT#messi']->Read);
        $this->assertEquals(0, $items['PARTICIPANT#aguero']->Read);
    }

    public function testWithParticipantsExceedingBatchLimit()
    {
        $participants = [];

        for ($i = 0; $i < 50; $i++) {
            $participants[] = "user{$i}";
        }

        $conversation = $this->chat->createConversation('Group Two', $participants);
        $conversationId = $conversation->getConversationId();
        $this->chat->messaging($conversationId)
            ->fromParticipant('user10')
            ->message('Hello')
            ->send();

        $response = $this->query(
            array_values($conversation->getPartitionKey())[0],
            Condition::attribute('SK')->beginsWith('MSG#')
        );

        $this->assertEquals(50, $response->count(), 'Each participant receives a message');
    }

    public function testUpdateConversation()
    {
        $subject = 'Conversation 1';
        $conversation = $this->chat->createConversation($subject);
        $conversationPartitionKey = array_values($conversation->getPartitionKey())[0];

        $newSubject = 'Conversation updated';
        $this->chat->updateConversation($conversation->getConversationId(), [
            'Subject' => $newSubject
        ]);

        $response = $this->query(
            $conversationPartitionKey,
            Condition::attribute('SK')->eq($conversationPartitionKey)
        );

        $this->assertEquals($newSubject, $response->first()->attribute('Subject'));
    }
}
