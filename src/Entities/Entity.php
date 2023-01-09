<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Bego\Component\Resultset;
use Bego\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Musonza\LaravelDynamodbChat\Configuration;
use Musonza\LaravelDynamodbChat\DynamodbResult;
use Musonza\LaravelDynamodbChat\Helpers\Helpers;

abstract class Entity extends Model
{
    public const PARTITION_KEY = 'PK';

    public const SORT_KEY = 'SK';

    public const GSI1_NAME = 'GSI1';

    public const GSI1_PARTITION_KEY = 'GSI1PK';

    public const GSI1_SORT_KEY = 'GSI1SK';

    public const GSI2_NAME = 'GSI2';

    public const GSI2_PARTITION_KEY = 'GSI2PK';

    public const GSI2_SORT_KEY = 'GSI2SK';

    /**
     * @psalm-suppress MissingPropertyType
     *
     * @phpstan-ignore-next-line
     */
    protected $_name = 'musonza_chat';

    /**
     * @psalm-suppress MissingPropertyType
     *
     * @phpstan-ignore-next-line
     */
    protected $_partition = self::PARTITION_KEY;

    /**
     * @psalm-suppress MissingPropertyType
     *
     * @phpstan-ignore-next-line
     */
    protected $_sort = self::SORT_KEY;

    /**
     * @psalm-suppress MissingPropertyType
     *
     * @phpstan-ignore-next-line
     */
    protected $_indexes = [
        self::GSI1_NAME => ['key' => self::GSI1_PARTITION_KEY],
    ];

    /**
     * @var Resultset|null
     */
    protected ?Resultset $resultset = null;

    protected array $gsi1 = [];

    protected array $gsi2 = [];

    protected string $entityType = 'CHAT_ENTITY';

    protected string $keyPrefix = 'CHAT';

    /**
     * Entity attributes to update / create
     *
     * @var array
     */
    private array $attributes = [];

    private ?DynamodbResult $result = null;

    final public function __construct()
    {
    }

    public function toArray(array $only = []): array
    {
        $item = $this->toItem();
        $arr = [];

        foreach ($item as $key => $value) {
            $arr[$key] = array_values($value)[0];
        }

        $arr = array_merge($arr, $this->attributes);

        if (! empty($only)) {
            return Arr::only($arr, $only);
        }

        return  $arr;
    }

    protected function toItem(): array
    {
        return [];
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): self
    {
        $allowList = Configuration::getAttributesAllowed();

        foreach ($attributes as $key => $val) {
            if (! empty($allowList) && ! in_array($key, Configuration::getAttributesAllowed())) {
                throw new InvalidArgumentException("Attribute {$key} is not in the allowed list.");
            }

            $this->attributes[$key] = $val;
        }

        return $this;
    }

    public function getInstance(array $attributes): static
    {
        return $this->newInstance($attributes, true);
    }

    /**
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static
     */
    public function newInstance(array $attributes = [], bool $exists = false): static
    {
        $model = new static();

        if (! $exists || ! $model->attribute('Id')) {
            $model->setAttribute('Id', Helpers::generateId($model->keyPrefix, now()));
            $model->setAttribute('CreatedAt', now()->toISOString());
        }

        foreach ($attributes as $key => $value) {
            $model->setAttribute($key, $value);
        }

        return $model;
    }

    public function attribute(string $key, string|bool|array|null $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function setAttribute(string $key, string|bool|array|null $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function getId(): string
    {
        return $this->attribute('Id');
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function first(): static
    {
        /** @phpstan-ignore-next-line */
        return $this->result->first($this);
    }

    public function getResultSet(): ?Resultset
    {
        return $this->resultset;
    }

    public function setResultSet(Resultset $resultset): void
    {
        $this->result = new DynamodbResult($resultset);
        $this->resultset = $resultset;
    }

    public function getGSI1(): array
    {
        return $this->gsi1;
    }

    public function setGSI1(array $gsi): void
    {
        $this->gsi1 = $gsi;
    }

    public function getGSI2(): array
    {
        return $this->gsi2;
    }

    public function setGSI2(array $gsi): void
    {
        $this->gsi2 = $gsi;
    }

    abstract public function getPrimaryKey(): array;

    abstract public function getPK(): string;

    abstract public function getSK(): string;
}
