<?php

namespace Musonza\LaravelDynamodbChat\Tests\Feature;

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
