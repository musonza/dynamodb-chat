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
        $data = [
            'settings' => [
                [
                    'name' => 'foo',
                ],
                [
                    'name' => 'bar',
                ],
            ],
        ];

        $conversation = $this->chat->conversation()
            ->setAttributes([
                'Subject' => $subject,
                'IsPrivate' => 1,
                'Description' => 'My description',
                'Data' => $data,
            ])
            ->create();

        $conditions = [Condition::attribute('SK')->eq($conversation->getSK())];
        $response = $this->query(
            $conversation->getPK(),
            $conditions
        );

        $this->assertEquals($subject, $response->first()->attribute('Subject'));
        $this->assertEquals('CONVERSATION', $response->first()->attribute('Type'));
        $this->assertEquals(1, $response->first()->attribute('IsPrivate'));
        $this->assertEquals('My description', $response->first()->attribute('Description'));
        $this->assertEquals(1, $response->count(), 'One conversation created');

        $this->assertEquals($data, $response->first()->attribute('Data'));
    }

    public function testGetConversationById()
    {
        $conversation = $this->createConversation();

        $this->assertNull($conversation->getResultSet());

        $conversation = $this->chat->conversation($conversation->getId())->first();

        $this->assertInstanceOf(Resultset::class, $conversation->getResultSet());
    }

    public function testUpdateConversation()
    {
        $conversation = $this->createConversation();
        $newSubject = 'Conversation updated';
        $description = 'This is a description.';

        $updated = $this->chat->conversation($conversation->getId())
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
