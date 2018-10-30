<?php

namespace Dees040\Repository\Contracts;

interface CriteriaRepository
{
    /**
     * Push a new criteria into the criteria stack.
     *
     * @param  \Dees040\Repository\Contracts\Criteria|string  $criteria
     * @return $this
     *
     * @throws \Throwable
     */
    public function pushCriteria($criteria);

    /**
     * Pop the given criteria from the criteria stack.
     *
     * @param  \Dees040\Repository\Contracts\Criteria|string  $criteria
     * @return $this
     */
    public function popCriteria($criteria);

    /**
     * Find entities based on a single criteria.
     *
     * @param  \Dees040\Repository\Contracts\Criteria|string  $criteria
     * @param  array  $columns
     * @return mixed
     *
     * @throws \Throwable
     */
    public function findByCriteria($criteria, array $columns = ['*']);

    /**
     * Find entities based on a single criteria.
     *
     * @param  \Dees040\Repository\Contracts\Criteria|string  $criteria
     * @param  bool  $perPage
     * @param  array  $columns
     * @return mixed
     *
     * @throws \Throwable
     */
    public function paginateByCriteria($criteria, $perPage = null, array $columns = ['*']);

    /**
     * Clear all pushed criteria from the stack.
     *
     * @return $this
     */
    public function clearCriteria();
}
