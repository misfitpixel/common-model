<?php

namespace MisfitPixel\Common\Model\Entity\Abstraction;

/**
 * Class BaseMeta
 * @package MisfitPixel\Common\Model\Entity\Abstraction
 */
abstract class BaseMeta implements Meta
{
    use Dated, Persistent;

    /** @var ?int */
    protected ?int $id;

    /** @var string */
    protected string $field;

    /** @var ?string */
    protected ?string $value1;

    /** @var ?string */
    protected ?string $value2;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue1(): string
    {
        return $this->value1;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function setValue1(?string $value): self
    {
        $this->value1 = $value;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue2(): ?string
    {
        return $this->value2;
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function setValue2(?string $value): self
    {
        $this->value2 = $value;

        return $this;
    }
}
