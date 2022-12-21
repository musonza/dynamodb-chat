<?php

namespace Musonza\LaravelDynamodbChat\Actions;

use Aws\DynamoDb\DynamoDbClient;
use Musonza\LaravelDynamodbChat\ConfigurationManager;

abstract class Action
{

    abstract public function execute();
}