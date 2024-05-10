<?php
declare(strict_types=1);

namespace App\Api\DTO\Query;

use App\Enum\EntityType;
use Symfony\Component\Validator\Constraints as Assert;

class QueryRequest
{
    #[Assert\NotBlank]
    private EntityType $type;

    private string $alias = 't';

    private ?string $query = null;

    private ?array $joins = null;

    private ?array $orderBy = null;

    private ?array $parameters = null;

    #[Assert\PositiveOrZero]
    private int $limit = 10;

    #[Assert\PositiveOrZero]
    private int $offset = 0;

    public function getType(): EntityType
    {
        return $this->type;
    }

    public function setType(EntityType $type): void
    {
        $this->type = $type;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): void
    {
        $this->query = $query;
    }

    public function getJoins(): ?array
    {
        return $this->joins;
    }

    public function setJoins(?array $joins): void
    {
        $this->joins = $joins;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParameters(?array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    public function getOrderBy(): ?array
    {
        return $this->orderBy;
    }

    public function setOrderBy(?array $orderBy): void
    {
        $this->orderBy = $orderBy;
    }
}