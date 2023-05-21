<?php

namespace MisfitPixel\Common\Model\Entity\Abstraction;


use MisfitPixel\Common\Model\Entity\Status;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Trait Statused
 * @package MisfitPixel\Entity\Abstraction
 */
trait Statused
{
    /** @var int|null  */
    protected ?int $statusId = null;

    /** @var Status */
    protected Status $status;

    /**
     * @return int
     */
    public function getStatusId(): int
    {
        return $this->statusId;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setStatusId(int $id): self
    {
        $this->statusId = $id;

        return $this;
    }

    /**
     * @return Status
     */
    public function getStatus(): Status
    {
        if($this->getStatusId()) {
            $this->status = new Status($this->getStatusId());
        }

        return $this->status;
    }

    /**
     * @param Status $status
     * @return $this
     */
    public function setStatus(Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param LifecycleEventArgs $event
     * @return $this
     */
    public function setDefaultStatus(LifecycleEventArgs $event): self
    {
        if($this->status === null) {
            $this->status = $event->getObjectManager()->getRepository(Status::class)
                ->find(Status::ACTIVE)
            ;
        }

        return $this;
    }

    /**
     * @param LifecycleEventArgs $event
     * @return $this
     */
    public function setDefaultStatusId(LifecycleEventArgs $event): self
    {
        if($this->statusId === null) {
            $this->statusId = Status::ACTIVE;
        }

        return $this;
    }
}
