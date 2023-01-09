<?php

namespace Musonza\LaravelDynamodbChat;

use Bego\Component\Resultset;
use Musonza\LaravelDynamodbChat\Entities\Entity;
use Musonza\LaravelDynamodbChat\Exceptions\ResourceNotFoundException;

class DynamodbResult
{
    protected Resultset $resultSet;

    public function __construct(Resultset $resultSet)
    {
        $this->resultSet = $resultSet;
    }

    public function first(Entity $entity): Entity
    {
        $result = $entity->getResultSet();
        $item = $result->first();

        if (is_null($item)) {
            throw new ResourceNotFoundException('Resource not found');
        }

        $instance = $entity->newInstance($item->attributes(), true);
        $instance->setResultSet($result);

        return $instance;
    }
}
