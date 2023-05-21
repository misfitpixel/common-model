<?php

namespace MisfitPixel\Common\Model\Entity\Abstraction;


use Symfony\Component\HttpKernel\Kernel;

/**
 * Trait Descriptive
 * @package  MisfitPixel\Common\Model\Entity\Abstraction
 */
trait Descriptive
{
    /** @var array */
    protected array $metaTree;

    /**
     * @return int
     */
    public abstract function getId(): ?int;

    /**
     * @param string $field
     * @param bool $return
     * @return Meta|null|string
     */
    public function getMeta(string $field, bool $return = false)
    {
        /** @var Kernel $app */
        global $app;

        /**
         * handle console apps.
         */
        if(get_class($app) == 'Symfony\Bundle\FrameworkBundle\Console\Application') {
            $app = $app->getKernel();
        }

        $metaEntity = sprintf('%sMeta', ucfirst(self::class));

        /** @var Meta $meta */
        $meta = $app->getContainer()->get('doctrine')->getManager()->getRepository($metaEntity)
            ->findOneBy([
                strtolower(str_replace('App\Entity\\', '', self::class)) => $this->getId(),
                'field' => $field
            ])
        ;

        return (!$return) ? $meta : (($meta != null) ? $meta->getValue1() : null);
    }

    /**
     * @param string $field
     * @param string $value1
     * @param string|null $value2
     * @param bool $override
     * @return $this
     */
    public function setMeta(string $field, string $value1, string $value2 = null, bool $override = true): self
    {
        /** @var Kernel $app */
        global $app;

        /**
         * handle console apps.
         */
        if(get_class($app) == 'Symfony\Bundle\FrameworkBundle\Console\Application') {
            $app = $app->getKernel();
        }

        $className = $this->getMetaClassName();
        $methodName = sprintf('set%s', ucfirst($this->getRootEntityName()));

        /** @var Meta $meta */
        $meta = new $className;

        if($override) {
            $meta = $app->getContainer()->get('doctrine')->getManager()->getRepository($className)
                ->findOneBy([
                    $this->getRootEntityName() => $this->getId(),
                    'field' => $field
                ])
            ;

            if($meta === null) {
                $meta = new $className;
            }
        }

        $meta->$methodName($this)
            ->setField($field)
            ->setValue1($value1)
            ->setValue2($value2)
            ->save()
        ;

        return $this;
    }

    /**
     * @param string $field
     * @return bool
     */
    public function deleteMeta(string $field): bool
    {
        /** @var Kernel $app */
        global $app;

        $className = $this->getMetaClassName();

        $meta = $app->getContainer()->get('doctrine')->getManager()->getRepository($className)
            ->findOneBy([
                $this->getRootEntityName() => $this->getId(),
                'field' => $field
            ])
        ;

        if($meta === null) {
            return false;
        }

        return $meta->delete();
    }

    /**
     * @param bool $force
     * @return array
     */
    public function getMetaTree(bool $force = false): array
    {
        /** @var Kernel $app */
        global $app;

        /**
         * handle console apps.
         */
        if(get_class($app) == 'Symfony\Bundle\FrameworkBundle\Console\Application') {
            $app = $app->getKernel();
        }

        if($this->metaTree !== null && !$force) {
            return $this->metaTree;
        }

        $metaEntity = sprintf('%sMeta', ucfirst(self::class));
        $metaTree = [];

        /**
         * build the entire metadata tree for this resource.
         */
        $meta = $app->getContainer()->get('doctrine')->getManager()->getRepository($metaEntity)
            ->findBy([
                strtolower(str_replace('App\Entity\\', '', self::class)) => $this->getId(),
            ])
        ;

        /** @var Meta $item */
        foreach($meta as $item) {
            $keys = explode('.', $item->getField());

            $chain = "";

            for($i=0; $i<sizeof($keys); $i++) {
                $chain .= sprintf("['%s']", $keys[$i]);
            }

            eval(sprintf('$metaTree%s = $item;',
                $chain
            ));
        }

        $this->metaTree = $metaTree;

        return $this->metaTree;
    }

    /**
     * @return string
     */
    public function getRootEntityName(): string
    {
        return strtolower(str_replace('App\Entity\\', '', self::class));
    }

    /**
     * @return string
     */
    public function getMetaClassName(): string
    {
        return sprintf('%sMeta', ucfirst(self::class));
    }
}
