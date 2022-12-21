<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Bego\Model;
use InvalidArgumentException;
use Musonza\LaravelDynamodbChat\ConfigurationManager;

class Entity extends Model
{
    /**
     * Table name
     */
    protected $_name = 'musonza_chat';

    protected $_partition = 'PK';

    protected $_sort = 'SK';

    /**
     * Entity attributes to update / create
     * @var array
     */
    private array $attributes = [];

    public function toArray(): array
    {
        $item = $this->toItem();
        $arr = [];

        foreach ($item as $key => $value) {
            $arr[$key] = array_values($value)[0];
        }

        return array_merge($arr, $this->attributes);
    }

    public function setAttributes(array $attributes): self
    {
        $allowList = ConfigurationManager::getAttributesAllowed();

        if(!empty($allowList)) {
            foreach ($attributes as $key => $val) {
                if (!in_array($key, ConfigurationManager::getAttributesAllowed())) {
                    throw new InvalidArgumentException("Attribute {$key} is not in the allowed list.");
                }
            }
        }

        $this->attributes = $attributes;
        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}