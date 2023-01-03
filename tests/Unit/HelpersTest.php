<?php

namespace Musonza\LaravelDynamodbChat\Tests\Unit;

use Musonza\LaravelDynamodbChat\Helpers\Helpers;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function testDirectConversationKey()
    {
        $id1 = 'id1';
        $id2 = 'id2';
        $key1 = Helpers::directConversationKey($id1, $id2);
        $key2 = Helpers::directConversationKey($id2, $id1);
        $this->assertEquals($key1, $key2);
        $this->assertEquals('CONVERSATION#DIRECT#P1#id1P2#id2', $key1);
    }
}
