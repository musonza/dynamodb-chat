<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Aws\DynamoDb\DynamoDbClient;
use Bego\Model;

class Entity extends Model
{
    /**
     * Table name
     */
    protected $_name = 'musonza_chat';

    protected $_partition = 'PK';

    protected $_sort = 'SK';

//    public abstract function getPrimaryKey(): array;

//    public abstract function toItem(): array;

    public function toArray(): array
    {
        $item = $this->toItem();
        $arr = [];

        foreach ($item as $key => $value) {
            $arr[$key] = array_values($value)[0];
        }

        return $arr;
    }
}