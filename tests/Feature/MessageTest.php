<?php

namespace Musonza\LaravelDynamodbChat\Tests\Feature;

use Aws\DynamoDb\Exception\DynamoDbException;
use Bego\Condition;
use Musonza\LaravelDynamodbChat\Exceptions\ResourceNotFoundException;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;
use Musonza\LaravelDynamodbChat\Tests\TestCase;

class MessageTest extends TestCase
{
    public function testSendMessage()
    {
        $conversation = $this->createConversation();
        $conversationId = $conversation->getId();

        $this->chat->messaging($conversationId)
            ->message(self::PARTICIPANTS[0], 'Congratulations')
            ->send();

        $conditions = [Condition::attribute('SK')->beginsWith('MSG#')];
        $response = $this->query(
            $conversation->getPK(),
            $conditions
        );

        $this->assertEquals(count(self::PARTICIPANTS), $response->count(), 'Each participant receives a message');

        $items = [];

        foreach ($response->toArrayOfObjects() as $item) {
            $items[$item->GSI2SK] = $item;
        }

        $this->assertEquals(1, $items[Helpers::gs1skFromParticipantIdentifier(self::PARTICIPANTS[0])]->attribute('Read'), 'Sender message marked as read');
        $this->assertEquals(0, $items[Helpers::gs1skFromParticipantIdentifier(self::PARTICIPANTS[1])]->attribute('Read'));
        $this->assertEquals(0, $items[Helpers::gs1skFromParticipantIdentifier(self::PARTICIPANTS[2])]->attribute('Read'));
        $this->assertEquals('MSG', $response->item(0)->attribute('Type'));
    }

    public function testOnlyParticipantsCanSendMessages()
    {
        $this->expectExceptionMessage('Participant is not part of the conversation');

        $conversation = $this->createConversation();
        $conversationId = $conversation->getId();

        $this->chat->messaging($conversationId)
            ->message('randomUser', 'Hello')
            ->send();
    }

    public function testSendMessageWithAdditionalDetails()
    {
        $conversation = $this->createConversation();
        $data = [
            'images' => [
                [
                    'file_name' => 'post_image.jpg',
                    'file_url' => 'http://example.com/post_img.jpg',
                ],
                [
                    'file_name' => 'post_image2.jpg',
                    'file_url' => 'http://example.com/post_img2.jpg',
                ],
            ],
        ];

        $message = $this->chat->messaging($conversation->getId())
            ->message(self::PARTICIPANTS[0], 'Hello', $data)
            ->send();

        $conditions = [Condition::attribute('SK')->eq($message->getSK())];
        $query = $this->query(
            $message->getPK(),
            $conditions
        );

        $item = $query->first()->attribute('Data');

        $this->assertEquals($item, $data);
    }

    public function testDeleteMessage()
    {
        $conversation = $this->createConversation();
        $message = $this->chat->messaging($conversation->getId())
            ->message(self::PARTICIPANTS[0], 'Hello')
            ->send();

        $this->chat->messaging($conversation->getId(), $message->getId())
         ->delete(self::PARTICIPANTS[0]);

        $conditions = [Condition::attribute('SK')->eq($message->getSK())];
        $query = $this->query(
            $message->getPK(),
            $conditions
        );

        $this->assertEquals(0, $query->count());
    }

    public function testClearConversation()
    {
        $conversation = $this->createConversation(2);

        for ($i = 0; $i < 10; $i++) {
            $sender = $i % 2 ? self::PARTICIPANTS[0] : self::PARTICIPANTS[1];
            $this->chat->messaging($conversation->getId())
                ->message($sender, 'Hello'.$i)
                ->send();
        }

        $this->chat->conversation($conversation->getId())->clear(self::PARTICIPANTS[0]);

        $sk = 'PARTICIPANT#'.self::PARTICIPANTS[0];

        $result = $this->query(
            $conversation->getPK(),
            [Condition::attribute('GSI1SK')->beginsWith($sk)],
            'GSI1'
        );

        $this->assertEquals(0, $result->count());
    }

    public function testGetMessage()
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
            Condition::attribute('ParticipantId')->eq(self::PARTICIPANTS[1])
        );

        $item = $response->first();
        $message = $this->chat->messaging($conversation->getId(), $item->attribute('Id'))
            ->first();

        $this->assertEquals($senderMessage->getId(), $message->attribute('ParentId'));
    }

    public function testCanOnlyDeleteOwnMessage()
    {
        $this->expectException(DynamoDbException::class);

        $conversation = $this->createConversation();
        $message = $this->chat->messaging($conversation->getId())
            ->message(self::PARTICIPANTS[1], 'Hello')
            ->send();

        $this->chat->messaging($conversation->getId(), $message->getId())
            ->delete(self::PARTICIPANTS[0]);
    }

    public function testMarkMessageRead()
    {
        $conversation = $this->chat->conversation()
            ->setAttributes(['subject' => 'Group'])
            ->setParticipants(['jane', 'john', 'james'])
            ->create();

        $this->chat->messaging($conversation->getId())
            ->message('jane', 'Hello')
            ->send();

        $conditions = [Condition::attribute('GSI1SK')->beginsWith(Helpers::gs1skFromParticipantIdentifier('john'))];
        $response = $this->query(
            $conversation->getPK(),
            $conditions,
            'GSI1'
        );

        $this->assertEquals(0, $response->item(0)->attribute('Read'));
        $messageId = $response->item(0)->attribute('SK');
        $this->chat->messaging($conversation->getId(), $messageId)
            ->markAsRead('john');

        $response = $this->query($conversation->getPK(), $conditions, 'GSI1');
        $this->assertEquals(1, $response->item(0)->attribute('Read'));
    }

    public function testIncrementsParentMessageReadCount()
    {
        // Create a conversation with 3 participants
        $conversation = $this->chat->conversation()
            ->setAttributes(['Subject' => 'Group'])
            ->setParticipants(['jane', 'john', 'james'])
            ->create();

        // Send a message from Jane
        $this->chat->messaging($conversation->getId())
            ->message('jane', 'Hello')
            ->send();

        $response = $this->query(
            $conversation->getPK(),
            [Condition::attribute('GSI1SK')->beginsWith(Helpers::gs1skFromParticipantIdentifier('james'))],
            'GSI1'
        );

        $jamesMessageId = $response->item(0)->attribute('SK');
        $this->chat->messaging($conversation->getId(), $jamesMessageId)
            ->markAsRead('james');

        $parentMessage = $this->query(
            $conversation->getPK(),
            [Condition::attribute('GSI1SK')->beginsWith(Helpers::gs1skFromParticipantIdentifier('jane'))],
            'GSI1'
        )->first();

        $this->assertEquals(1, $parentMessage->attribute('ReadCount'));
    }

    public function testMarksOnlyOwnedMessageRead()
    {
        $conversation = $this->chat->conversation()
            ->setAttributes(['Subject' => 'Group'])
            ->setParticipants(['jane', 'john'])
            ->create();

        $this->chat->messaging($conversation->getId())
            ->message('jane', 'Hello')
            ->send();

        $response = $this->query(
            $conversation->getPK(),
            [Condition::attribute('GSI1SK')->beginsWith(Helpers::gs1skFromParticipantIdentifier('john'))],
            'GSI1'
        );

        $johnMessageId = $response->item(0)->attribute('SK');

        $this->expectException(ResourceNotFoundException::class);
        $this->chat->messaging($conversation->getId(), $johnMessageId)
            ->markAsRead('jane');
    }

    public function testGetMessagesReturnsSortedItems()
    {
        $conversation = $this->chat->conversation()
            ->setAttributes(['Subject' => 'Group'])
            ->setParticipants(['jane', 'john'])
            ->create();

        $totalMessages = 3;

        for ($i = 0; $i < $totalMessages; $i++) {
            $sender = $i % 2 ? 'jane' : 'john';
            $this->chat->messaging($conversation->getId())
                ->message($sender, 'Hello'.$i)
                ->send();
            sleep(1);
        }

        $offset = null;
        $messagesCount = 0;
        $resultsCollection = [];

        do {
            $results = $this->chat->messaging($conversation->getId())
                ->getMessages('john', $offset);
            $resultsCollection[] = $results;
            $offset = $results->getLastEvaluatedKey();
            $messagesCount += $results->count();
        } while (! is_null($offset));

        $this->assertEquals($totalMessages, $messagesCount, "{$totalMessages} messages");

        $times = [];

        foreach ($resultsCollection as $collection) {
            foreach ($collection as $res) {
                $times[] = $res->CreatedAt;
            }
        }

        $sorted = $times;
        rsort($sorted);

        $this->assertEquals($sorted, $times);
    }
}
