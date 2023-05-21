<?php

namespace MisfitPixel\Common\Model\Entity;


use MisfitPixel\Common\Model\Entity\Abstraction\Respondent;

/**
 * Class Status
 * @package MisfitPixel\Common\Model\Entity
 */
class Status
{
    use Respondent {
        getResponse as getDefaultResponse;
    }

    const ACTIVE = 1;
    const INACTIVE = 2;
    const EXPIRED = 3;
    const DELETED = 4;
    const COMPLETE = 5;

    /** @var ?int */
    private ?int $id;

    /** @var string */
    private string $name;

    /**
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;

        switch($this->getId()) {
            case self::ACTIVE:
                $name = 'active';

                break;

            case self::INACTIVE:
                $name = 'inactive';

                break;

            case self::EXPIRED:
                $name = 'expired';

                break;

            case self::DELETED:
                $name = 'deleted';

                break;

            case self::COMPLETE:
                $name = 'complete';

                break;

            default:
                $name = 'na';
        }

        $this->name = $name;
    }

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        $response = $this->getDefaultResponse();

        if(isset($response['status_id'])) {
            unset($response['status_id']);
        }

        return $response;
    }
}
