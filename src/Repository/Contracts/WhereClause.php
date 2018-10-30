<?php

namespace Dees040\Repository\Contracts;

interface WhereClause
{
    /**
     * Apply the where clause to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  mixed  $searchValue
     * @param  string  $operator
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function apply($model, $searchValue, $operator);
}