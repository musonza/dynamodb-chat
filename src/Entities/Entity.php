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

//    public abstract function getPrimaryKey(): array;

//    public abstract function toItem(): array;
}