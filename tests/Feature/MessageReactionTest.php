<?php

namespace Musonza\LaravelDynamodbChat\Tests\Feature;

use Bego\Condition;
use Musonza\LaravelDynamodbChat\Entities\MessageReaction;
use Musonza\LaravelDynamodbChat\Tests\TestCase;

class MessageReactionTest extends TestCase
{
    public function testAddReactionToMessage()
    {
        $conversation = $this->createConversation();
        $conversationId = $conversation->getId();
        $message = $this->chat->messaging($conversationId)
            ->message(self::PARTICIPANTS[0], 'Congratulations')
            ->send();

        $messageReaction = $this->chat->messaging($conversationId, $message->getId())
            ->react('THUMBS_UP', self::PARTICIPANTS[0]);

        $this->assertInstanceOf(MessageReaction::class, $messageReaction);
    }

    public function testReactionIsAddedToOriginalMessage()
    {
        $conversation = $this->createConversation();
        $senderMessage = $this->chat->messaging($conversation->getId())
            ->message(self::PARTICIPANTS[0], 'Hello')
            ->send();

        $response = $this->query(
            $conversation->getPK(),
            [Condition::attribute('SK')->beginsWith('MSG#')],
            null,
            Condition::attribute('ParticipantId')->eq(self::PARTICIPANTS[1])
        );

        $item = $response->first();
        $recipientMessage = $this->chat->messaging($conversation->getId(), $item->attribute('Id'))
            ->first();

        $messageReaction = $this->chat->messaging($conversation->getId(), $recipientMessage->getId())
            ->react('THUMBS_UP', self::PARTICIPANTS[0]);

        $this->assertEquals($senderMessage->getId(), $messageReaction->attribute('MessageId'));
        $this->assertEquals($recipientMessage->getId(), $messageReaction->attribute('ReactingParticipantMessageId'));
    }

    public function testAddReactionToMessageDoesntDuplicate()
    {
        $this->expectExceptionMessage('Unable to create reaction. Reaction might already exist');

        $conversation = $this->createConversation();
        $conversationId = $conversation->getId();

        $message = $this->chat->messaging($conversationId)
            ->message(self::PARTICIPANTS[0], 'Congratulations')
            ->send();

        $this->chat->messaging($conversationId, $message->getId())
            ->react('THUMBS_UP', self::PARTICIPANTS[0]);
        $this->chat->messaging($conversationId, $message->getId())
            ->react('THUMBS_UP', self::PARTICIPANTS[0]);
    }

    public function testRemoveMessageReaction()
    {
        $conversation = $this->createConversation();
        $conversationId = $conversation->getId();
        $message = $this->chat->messaging($conversationId)
            ->message(self::PARTICIPANTS[0], 'Congratulations')
            ->send();

        $messageReaction = $this->chat->messaging($conversationId, $message->getId())
            ->react('THUMBS_UP', self::PARTICIPANTS[0]);

        $this->chat->messaging($conversationId, $message->getId())
            ->unreact('THUMBS_UP', self::PARTICIPANTS[0]);

        $conditions = [Condition::attribute('SK')->eq($messageReaction->getSK())];
        $response = $this->query(
            $messageReaction->getPK(),
            $conditions
        );

        $this->assertEquals(0, $response->count());
    }
}

/**
 * MessageReaction
 * user_id
 * message_id
 * created_at
 * reaction_type
 *
 * Conversation
 *
 * bio_text / description / topic
 * profile_image_url
 *
 *
 * Participant
 *
 * bio_text / description
 * profile_image_url
 * real_name
 * last_login_timestamp
 *
 * Message
 * ---------------
 * status - sent, delivered, read
 * :sent - the server has received the message
 * :delivered - the message has been delivered to the recipient device
 * :read - the message has been read by the recipient / opened the app and clicked conversation after receiving the message
 * ---------------
 *
 * retrieve messages by sending last message timestamp / offset
 */
