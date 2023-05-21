<?php

namespace MisfitPixel\Common\Model\Entity\Abstraction;

/**
 * Trait Dated
 * @package  MisfitPixel\Common\Model\Entity\Abstraction
 */
trait Dated
{
    /** @var \DateTIme */
    protected \DateTime $dateCreated;

    /** @var \DateTime */
    protected \DateTime $dateUpdated;

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getDateCreated(): \DateTime
    {
        return new \DateTime($this->dateCreated->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getDateUpdated(): \DateTime
    {
        return new \DateTime($this->dateUpdated->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function setDefaultDateCreated(): self
    {
        $this->dateCreated = new \DateTime('now', new \DateTimeZone('UTC'));

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function setDefaultDateUpdated(): self
    {
        $this->dateUpdated = new \DateTime('now', new \DateTimeZone('UTC'));

        return $this;
    }
}
