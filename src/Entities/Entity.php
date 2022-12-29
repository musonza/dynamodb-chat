<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Bego\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Musonza\LaravelDynamodbChat\ConfigurationManager;

class Entity extends Model
{
    public const GLOBAL_INDEX1 = 'GSI1';
    public const GLOBAL_INDEX1_PK = 'GSI1PK';
    public const GLOBAL_INDEX1_SK = 'GSI1SK';
    public const GLOBAL_INDEX2 = 'GSI2';
    public const GLOBAL_INDEX2_PK = 'GSI2PK';
    public const GLOBAL_INDEX2_SK = 'GSI2SK';

    /**
     * Table name
     */
    protected $_name = 'musonza_chat';

    protected $_partition = 'PK';

    protected $_sort = 'SK';

    protected $_indexes = [
        self::GLOBAL_INDEX1 => ['key' => self::GLOBAL_INDEX1_PK]
    ];

    /**
     * Entity attributes to update / create
     * @var array
     */
    private array $attributes = [];

    public function toArray(array $only = []): array
    {
        $item = $this->toItem();
        $arr = [];

        foreach ($item as $key => $value) {
            $arr[$key] = array_values($value)[0];
        }

        $arr = array_merge($arr, $this->attributes);

        if (!empty($only)) {
            return Arr::only($arr, $only);
        }

        return  $arr;
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