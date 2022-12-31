<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Bego\Component\Resultset;
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

    protected array $gsi1 = [];

    protected array $gsi2 = [];

    protected string $entityType = 'CHAT_ENTITY';

    protected string $keyPrefix = 'CHAT';

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

        foreach ($attributes as $key => $val) {
            if (!empty($allowList) && !in_array($key, ConfigurationManager::getAttributesAllowed())) {
                throw new InvalidArgumentException("Attribute {$key} is not in the allowed list.");
            }

            $this->attributes[$key] = $val;
        }

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
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
            $model->setAttribute('Id', Helpers::generateId($model->keyPrefix, now()));
            $model->setAttribute('CreatedAt', now()->toISOString());
        }

        foreach ($attributes as $key => $value) {
            $model->setAttribute($key, $value);
        }

        return $model;
    }

    public function getId(): string
    {
        return $this->getAttribute('Id');
    }

    public function getKeyPrefix(): string
    {
        return '';
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function setResultSet(Resultset $resultset)
    {
        $this->resultset = $resultset;
    }

    public function getResultSet(): ?Resultset
    {
        return $this->resultset;
    }

    public function getGSI1(): array
    {
        return $this->gsi1;
    }

    public function getGSI2(): array
    {
        return $this->gsi2;
    }

    public function setGSI2(array $gsi)
    {
        $this->gsi2 = $gsi;
    }

    public function setGSI1(array $gsi)
    {
        $this->gsi1 = $gsi;
    }
}