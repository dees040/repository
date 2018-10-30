<?php

namespace Dees040\Repository\Contracts;

interface Criteria
{
    /**
     * Execute the criteria.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Dees040\Repository\Contracts\Repository  $repository
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function apply($model, $repository);
}