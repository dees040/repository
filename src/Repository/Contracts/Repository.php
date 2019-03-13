<?php

namespace Dees040\Repository\Contracts;

interface Repository
{
    /**
     * Get all entities.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all($columns = ['*']);

    /**
     * Get paginated entities.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*']);

    /**
     * Find the first entity.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function first($columns = ['*']);

    /**
     * Find an entity by its primary key.
     *
     * @param  int  $id
     * @param  array  $columns
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * Find an entity by its primary key or throw an exception.
     *
     * @param  int  $id
     * @param  array  $columns
     * @return mixed
     */
    public function findOrFail($id, $columns = ['*']);

    /**
     * Find entities by field and value.
     *
     * @param  string  $field
     * @param  mixed  $value
     * @param  array  $columns
     * @return mixed
     */
    public function findByField($field, $value, $columns = ['*']);

    /**
     * Find entities based on an array of where clauses.
     *
     * @param  array  $where
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findWhere(array $where, $columns = ['*']);

    /**
     * Find entities based on an array of where clauses.
     *
     * @param  array  $where
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findWhere(array $where, $columns = ['*']);

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function firstOrCreate(array $attributes, array $values = []);

    /**
     * Create a new entity by the given attributes.
     *
     * @param  array  $attributes
     * @return mixed
     */
    public function create(array $attributes = []);

    /**
     * Update the given attributes for the entity matching the given primary
     * key.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return mixed
     */
    public function update($id, array $attributes);

    /**
     * Create or update an entity matching the attributes, and fill it with
     * values.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $attributes, array $values = []);

    /**
     * Delete the entity matching the associated primary key.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete($id);

    /**
     * Delete entities based on an array of where clauses.
     *
     * @param  array  $where
     * @return int
     */
    public function deleteWhere(array $where);

    /**
     * Add an order by filter to the entity query.
     *
     * @param  string  $column
     * @param  string  $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc');

    /**
     * Add eager loading to a model.
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function with($relations);

    /**
     * Check if existence of relationship exists in the entity.
     *
     * @param  string  $relation
     * @return $this
     */
    public function has($relation);

    /**
     * Check if existence of relationship exists in the entity with a callback
     * query.
     *
     * @param  string  $relation
     * @param  \Closure  $closure
     * @return $this
     */
    public function whereHas($relation, \Closure $closure);

    /**
     * Check if existence of relationship exists in the entity with a callback
     * query and an "or" in the clause.
     *
     * @param  string  $relation
     * @param  \Closure  $closure
     * @return $this
     */
    public function orWhereHas($relation, \Closure $closure);

    /**
     * Sync the intermediate entities with a list of IDs or collection of models.
     *
     * @param  mixed  $id
     * @param  string  $relation
     * @param  array  $attributes
     * @param  bool  $detaching
     * @return mixed
     */
    public function sync($id, $relation, $attributes, $detaching = true);

    /**
     * Sync the intermediate entities with a list of IDs without detaching.
     *
     * @param  mixed  $id
     * @param  string  $relation
     * @param  array  $attributes
     * @return mixed
     */
    public function syncWithoutDetaching($id, $relation, $attributes);

    /**
     * Get all fields which are searchable.
     *
     * @return array
     */
    public function getSearchableFields();
}
