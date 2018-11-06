<?php

namespace Dees040\Repository\Criteria;

use Dees040\Repository\Contracts\Criteria;

class RequestCriteria implements Criteria
{
    const SEARCH_STRICT = '=';
    const SEARCH_LOOSE = 'like';

    /**
     * The model to work with.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $model;

    /**
     * The repository instance.
     *
     * @var \Dees040\Repository\Contracts\Repository
     */
    private $repository;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Execute the criteria.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Dees040\Repository\Contracts\Repository  $repository
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function apply($model, $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->request = request();

        if ($this->request->has('order_by')) {
            $this->addOrderBy();
        }
        
        if ($this->request->has('search')) {
            $this->addSearch();
        }

        return $this->model;
    }

    /**
     * Add an order by to the query.
     *
     * @return void
     */
    protected function addOrderBy()
    {
        $orderBy = $this->request->get('order_by');
        $sortBy = $this->getSortBy();

        $this->model = $this->model->orderBy($orderBy, $sortBy);
    }

    /**
     * Add search queries to the model.
     *
     * @return void
     */
    protected function addSearch()
    {
        $searchValue = $this->request->get('search');

        $fields = $this->getFieldsFromRequest();

        foreach ($fields as $rawField) {
            // Firstly, we parse the query parameter to get the field which we
            // need to search on and to get the operator type: strictly or
            // loose.
            list($field, $operator) = $this->parseSearchField($rawField);

            // Secondly, we're going to determine if the field uses a custom
            // search query (contract) based on the given field. Also we
            // immediately check if the key actually exists in the search fields
            // of the current repository.
            list($keyFound, $contract) = $this->getSearchContract($field);

            // As last, we're going to add the where clause to the search query,
            // but only if the search key is found.
            if ($keyFound) {
                $this->addWhereClause($searchValue, $field, $operator, $contract);
            }
        }
    }

    /**
     * Get all fields given in the search_fields query parameter and split them
     * on the delimiter.
     *
     * @return array
     */
    protected function getFieldsFromRequest()
    {
        $givenFields = $this->request->get('search_fields', null);

        // If the given fields is a string with more than 0 characters we can
        // split the fields based on the delimiter.
        $fields = is_string($givenFields) && strlen($givenFields) > 0
            ? explode(',', $givenFields)
            : [];

        if (count($fields) > 0) {
            return $fields;
        }

        // If no fields are given we'll search in all searchable fields.
        return $this->repository->getSearchableFields();
    }

    /**
     * Parse the search field so that we get ready to use data for the search.
     *
     * @param  string  $rawField
     * @return array
     */
    protected function parseSearchField($rawField)
    {
        // If no option is defined for the search field we'll give it the
        // strict search option by default.
        if (! str_contains($rawField, ':')) {
            return [$rawField, static::SEARCH_STRICT];
        }

        $values = explode(':', $rawField);

        // If a search option is given we'll check if it is the 'like' operator.
        // If so, it will get the loose search operator, otherwise the strict
        // search operator.
        $operator = last($values) === static::SEARCH_LOOSE
            ? static::SEARCH_LOOSE
            : static::SEARCH_STRICT;

        return [head($values), $operator];
    }

    /**
     * Get the search contract, if it exists.
     *
     * @param  string  $field
     * @return array
     */
    protected function getSearchContract($field)
    {
        $searchableFields = $this->repository->getSearchableFields();

        foreach ($searchableFields as $key => $contract) {
            $searchableField = is_string($key) ? $key : $contract;

            // It could happen that the searchable field has a search operator
            // specified. If that's the case we need to get the actual field
            // from the string.
            if (str_contains($searchableField, ':')) {
                $searchableField = head(explode(':', $searchableField));
            }

            if ($searchableField == $field) {
                // At this point we know that the field is searchable and we can
                // check if the key is a contract or not.
                return [true, is_string($key) ? $contract : null];
            }
        }

        // If we reach this point we know that the field is not allowed for
        // searching.
        return [false, null];
    }

    /**
     * Add the actual search where clause to the model.
     *
     * @param  mixed  $searchValue
     * @param  string  $field
     * @param  int  $operator
     * @param  string  $contract
     * @return void
     */
    protected function addWhereClause($searchValue, $field, $operator, $contract)
    {
        // If the integer is an int we can presume that the current where clause
        // can be executed on a field immediately.
        if (is_null($contract)) {
            // TODO: check if or where needs to be grouped.
            $this->model = $this->model->orWhere($field, $operator, $this->parseSearchValue($searchValue, $operator));

            return;
        }

        // If the key is not an int we assume it's a string representing a where
        // clause contract.
        $this->model = call_user_func_array([$contract, 'apply'], [$this->model, $searchValue, $operator]);
    }

    /**
     * Parse the search value based on the operator.
     *
     * @param  string  $searchValue
     * @param  int  $operator
     * @return string
     */
    protected function parseSearchValue($searchValue, $operator)
    {
        if ($operator == static::SEARCH_STRICT) {
            return $searchValue;
        }

        return "%{$searchValue}%";
    }

    /**
     * Get the sort by option from the request.
     *
     * @return string
     */
    protected function getSortBy()
    {
        $sortBy = $this->request->get('sort_by');

        if (is_null($sortBy)) {
            return 'asc';
        }

        // Validate if the sort options are valid for MySQL usage.
        if (in_array($sortBy, ['asc', 'desc'])) {
            return $sortBy;
        }

        return 'asc';
    }
}
