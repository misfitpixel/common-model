<?php

namespace MisfitPixel\Common\Model\Service\Abstraction;


use Doctrine\ORM\EntityManagerInterface;

/**
 * Class BaseSearchService
 * @package MisfitPixel\Common\Model\Service\Abstraction
 */
abstract class BaseSearchService
{
    /** @var EntityManagerInterface  */
    private EntityManagerInterface $manager;

    /**
     * ExpansionSearchService constructor.
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $query
     * @param int $offset
     * @param int $limit
     * @param array $order
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function search(string $query, int $offset, int $limit, array $order = []): array
    {
        $entities = [];
        $criteria = $this->getCriteriaStructure($query);

        /**
         * get search-friendly base query.
         */
        $stmt = $this->getManager()->getConnection()->prepare(sprintf("
            SELECT DISTINCT e.id %s
            FROM %s e
            INNER JOIN (
                %s
            ) q ON q.id=e.id
            WHERE true
            %s
            %s
            LIMIT %d, %d
            ;
        ", (!empty($order)) ? sprintf(", q.%s", array_keys($order)[0]): '', $this->getEntityTableName(), $this->getSearchInnerQuery(), $this->evaluateCriteriaAsSql($criteria), $this->evaluateOrder($order), $offset, $limit));

        $results = $stmt->executeQuery()->fetchFirstColumn();

        foreach($results as $result) {
            $entities[] = $this->getManager()->getRepository($this->getEntityClassName())->find($result);
        }

        return $entities;
    }

    /**
     * @return string
     */
    protected abstract function getSearchExpression(): string;

    /**
     * @return string
     */
    protected abstract function getEntityClassName(): string;

    /**
     * @return string
     */
    private function getEntityTableName(): string
    {
        return $this->manager->getClassMetadata($this->getEntityClassName())->getTableName();
    }

    /**
     * @return string
     */
    protected abstract function getSearchInnerQuery(): string;

    /**
     * @param array $criteria
     * @return string
     */
    protected abstract function evaluateCriteriaAsSql(array $criteria): string;

    /**
     * @param array $order
     * @return string
     */
    protected function evaluateOrder(array $order): string
    {
        $sql = 'ORDER BY ';

        if($order == null) {
            return '';
        }

        foreach($order as $column => $direction) {
            if(
                $column == null ||
                $direction == null
            ) {
                continue;
            }

            $sql .= sprintf("%s %s", $column, $direction);
        }

        return $sql;
    }

    /**
     * @param string $query
     * @return array
     */
    public function getCriteriaStructure(string $query): array
    {
        $criteria = [];
        $matches = [];

        /**
         * break the query into blocks of criteria to be evaluated.
         */
        preg_match_all($this->getSearchExpression(), $query, $matches);

        foreach($matches as $items) {
            foreach($items as $item) {
                $parts = explode(':', $item);

                if(sizeof($parts) !== 2) {
                    continue;
                }

                $criteria[$parts[0]][] = $parts[1];
            }
        }

        return $criteria;
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getManager(): EntityManagerInterface
    {
        return $this->manager;
    }
}
