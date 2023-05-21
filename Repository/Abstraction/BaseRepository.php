<?php

namespace MisfitPixel\Common\Model\Repository\Abstraction;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MisfitPixel\Common\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BaseRepository
 * @package MisfitPixel\Common\Repository\Abstraction
 */
abstract class BaseRepository extends ServiceEntityRepository
{
    /** @var ContainerInterface  */
    private ContainerInterface $container;

    /** @var int */
    private int $offset;

    /** @var int|null  */
    private ?int $limit = null;

    /** @var array */
    private array $order = [];

    /**
     * @param ManagerRegistry $registry
     * @param ContainerInterface $container
     */
    public function __construct(ManagerRegistry $registry, ContainerInterface $container)
    {
        parent::__construct($registry, $this->getEntityClassName());

        $this->container = $container;
        $this->offset = 0;
    }

    /**
     * @return string
     */
    public abstract function getEntityClassName(): string;

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int|null $limit
     * @return $this
     */
    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return array
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * @param array|null $order
     * @return $this
     */
    public function setOrder(?array $order): self
    {
        if($order === null) {
            $this->order = [];
        }

        foreach($order as $field => $direction) {
            if(!in_array(strtolower($direction), ['asc', 'desc'])) {
                throw new Exception\BadRequestException('Sort order direction must be one-of: \'asc\' or \'desc\' ');
            }
        }

        $this->order = $order;

        return $this;
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        return parent::findBy([], null, $this->getLimit(), $this->getOffset());
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
