<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Bego\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Musonza\LaravelDynamodbChat\ConfigurationManager;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

class Entity extends Model
{
    public const PARTITION_KEY = 'PK';
    public const SORT_KEY = 'SK';
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

    protected $_partition = self::PARTITION_KEY;

    protected $_sort = self::SORT_KEY;

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

    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public static function newInstance($attributes = [], $exists = false): static
    {
        $model = new static;

        if (!$exists) {
            $model->setAttribute('Id', Helpers::generateId($model->getKeyPrefix(), now()));
        }

        foreach ($attributes as $key => $value) {
            $model->setAttribute($key, $value);
        }

        return $model;
    }
}