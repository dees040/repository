<?php

namespace Dees040\Repository\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Dees040\Repository\Contracts\Criteria;
use Dees040\Repository\Contracts\Repository;
use Dees040\Repository\Contracts\CriteriaRepository;

abstract class BaseRepository implements Repository, CriteriaRepository
{
    /**
     * The Eloquent model to work with.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * The criteria stack.
     *
     * @var \Dees040\Repository\Contracts\Criteria[]
     */
    private $criteria = [];

    /**
     * BaseRepository constructor.
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function __construct()
    {
        $this->buildModel();

        $this->boot();
    }

    /**
     * Boot the repository.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Get the base model.
     *
     * @return string
     */
    public abstract function getModel();

    /**
     * Get all entities.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all($columns = ['*'])
    {
        $this->applyCriteria();

        $models = $this->model->get($columns);

        $this->resetModel();

        return $models;
    }

    /**
     * Get paginated entities.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'])
    {
        $this->applyCriteria();

        $models = $this->model->paginate($perPage, $columns);

        $this->resetModel();

        return $models;
    }

    /**
     * Find the first entity.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function first($columns = ['*'])
    {
        $this->applyCriteria();

        $model = $this->model->first($columns);

        $this->resetModel();

        return $model;
    }

    /**
     * Find an entity by it's given primary key.
     *
     * @param  int  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->newQuery()->find($id, $columns);
    }

    /**
     * Find an entity by its primary key or throw an exception.
     *
     * @param  int  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function findOrFail($id, $columns = ['*'])
    {
        return $this->model->newQuery()->findOrFail($id, $columns);
    }

    /**
     * Find entities by field and value.
     *
     * @param  string  $field
     * @param  mixed  $value
     * @param  array  $columns
     * @return mixed
     */
    public function findByField($field, $value, $columns = ['*'])
    {
        $this->applyCriteria();

        $models = $this->model->where($field, '=', $value)
            ->get($columns);

        $this->resetModel();

        return $models;
    }

    /**
     * Find entities based on an array of where clauses.
     *
     * @param  array  $where
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findWhere(array $where, $columns = ['*'])
    {
        $this->applyCriteria();

        $models = $this->model->where($where)
            ->get($columns);

        $this->resetModel();

        return $models;
    }

    /**
     * Get the first entity matching the attributes or create it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(array $attributes, array $values = [])
    {
        return $this->model->newQuery()->firstOrCreate($attributes, $values);
    }

    /**
     * Create a new entity by the given attributes.
     *
     * @param  array  $attributes
     * @return mixed
     */
    public function create(array $attributes = [])
    {
        $model = $this->model->newInstance($attributes);

        $model->save();

        return $model;
    }

    /**
     * Create multiple new entities by the given attributes.
     *
     * @param  array  $attributes
     * @return bool
     */
    public function insert(array $attributes = [])
    {
        return $this->model->insert($attributes);
    }

    /**
     * Update the given attributes for the entity matching the given primary
     * key.
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($id, array $attributes)
    {
        $model = $this->findOrFail($id);

        $model->update($attributes);

        return $model;
    }

    /**
     * Create or update an entity matching the attributes, and fill it with
     * values.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        return $this->model->newQuery()->updateOrCreate($attributes, $values);
    }

    /**
     * Delete the entity matching the associated primary key.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete($id)
    {
        $model = $this->findOrFail($id);

        try {
            return $model->delete();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete entities based on an array of where clauses.
     *
     * @param  array  $where
     * @return int
     */
    public function deleteWhere(array $where)
    {
        $deletedCount = $this->model->where($where)
            ->delete();

        $this->resetModel();

        return $deletedCount;
    }

    /**
     * Add an order by filter to the entity query.
     *
     * @param  string  $column
     * @param  string  $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->model = $this->model->orderBy($column, $direction);

        return $this;
    }

    /**
     * Add eager loading to a model.
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    /**
     * Check if existence of relationship exists in the entity.
     *
     * @param  string  $relation
     * @return $this
     */
    public function has($relation)
    {
        $this->model = $this->model->has($relation);

        return $this;
    }

    /**
     * Check if existence of relationship exists in the entity with a callback
     * query.
     *
     * @param  string  $relation
     * @param  \Closure  $closure
     * @return $this
     */
    public function whereHas($relation, \Closure $closure)
    {
        $this->model = $this->model->whereHas($relation, $closure);

        return $this;
    }

    /**
     * Check if existence of relationship exists in the entity with a callback
     * query and an "or" in the clause.
     *
     * @param  string  $relation
     * @param  \Closure  $closure
     * @return $this
     */
    public function orWhereHas($relation, \Closure $closure)
    {
        $this->model = $this->model->orWhereHas($relation, $closure);

        return $this;
    }

    /**
     * Sync the intermediate entities with a list of IDs or collection of models.
     *
     * @param  mixed  $id
     * @param  string  $relation
     * @param  array  $attributes
     * @param  bool  $detaching
     * @return mixed
     */
    public function sync($id, $relation, $attributes, $detaching = true)
    {
        return $this->find($id)->{$relation}()->sync($attributes, $detaching);
    }
    /**
     * Sync the intermediate entities with a list of IDs without detaching.
     *
     * @param  mixed  $id
     * @param  string  $relation
     * @param  array  $attributes
     * @return mixed
     */
    public function syncWithoutDetaching($id, $relation, $attributes)
    {
        return $this->sync($id, $relation, $attributes, false);
    }

    /**
     * Push a new criteria into the criteria stack.
     *
     * @param  \Dees040\Repository\Contracts\Criteria|string  $criteria
     * @return $this
     *
     * @throws \Throwable
     */
    public function pushCriteria($criteria)
    {
        $criteria = $this->buildCriteria($criteria);

        data_set($this->criteria, get_class($criteria), $criteria);

        return $this;
    }

    /**
     * Pop the given criteria from the criteria stack.
     *
     * @param  \Dees040\Repository\Contracts\Criteria|string  $criteria
     * @return $this
     */
    public function popCriteria($criteria)
    {
        $criteria = is_string($criteria) ? $criteria : get_class($criteria);

        unset($this->criteria[$criteria]);

        return $this;
    }

    /**
     * Find entities based on a single criteria.
     *
     * @param  \Dees040\Repository\Contracts\Criteria|string  $criteria
     * @param  array  $columns
     * @return mixed
     *
     * @throws \Throwable
     */
    public function findByCriteria($criteria, array $columns = ['*'])
    {
        return $this->executeSingleCriteria($criteria, function ($model) use ($columns) {
            return $model->get($columns);
        });
    }

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
    public function paginateByCriteria($criteria, $perPage = null, array $columns = ['*'])
    {
        return $this->executeSingleCriteria($criteria, function ($model) use ($perPage, $columns) {
            return $model->paginate($perPage, $columns);
        });
    }

    /**
     * Find entities based on given criteria and use a callback to decide how
     * the models are retrieved. Useful to easily switch between pagination or
     * normal results.
     *
     * @param  \Dees040\Repository\Contracts\Criteria|string  $criteria
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Throwable
     */
    private function executeSingleCriteria($criteria, $callback)
    {
        $models = $callback($this->buildCriteria($criteria)->apply($this->model));

        $this->resetModel();

        return $models;
    }

    /**
     * Clear all pushed criteria from the stack.
     *
     * @return $this
     */
    public function clearCriteria()
    {
        $this->criteria = [];

        return $this;
    }

    /**
     * Apply all the criteria found in the criteria array to the current model.
     *
     * @return $this
     */
    protected function applyCriteria()
    {
        foreach ($this->criteria as $criteria) {
            $this->model = $criteria->apply($this->model, $this);
        }

        return $this;
    }
    
    /**
     * Build the criteria class.
     *
     * @param  \Dees040\Repository\Contracts\Criteria  $criteria
     * @return \Dees040\Repository\Contracts\Criteria
     *
     * @throws \Throwable
     */
    protected function buildCriteria($criteria)
    {
        $criteria = is_string($criteria) ? new $criteria : $criteria;

        // We need to make sure that the given model is indeed an Eloquent
        // model, otherwise we will throw an exception.
        throw_if(
            ! $criteria instanceof Criteria,
            new \Exception(sprintf("The given object is not a child class of the criteria contract."))
        );

        return $criteria;
    }

    /**
     * Get the name of the model class and build it.
     *
     * @return void
     *
     * @throws \Throwable
     */
    private function buildModel()
    {
        $this->model = app()->make($this->getModel());

        // We need to make sure that the given model is indeed an Eloquent
        // model, otherwise we will throw an exception.
        throw_if(
            ! $this->model instanceof Model,
            new \Exception(sprintf("The given model class '%s' is not a child class of the Eloquent Model.", $this->getModel()))
        );
    }

    /**
     * Reset the model.
     *
     * @return void
     */
    public function resetModel()
    {
        $this->buildModel();
    }

    /**
     * Get all fields which are searchable.
     *
     * @return array
     */
    public function getSearchableFields()
    {
        return [
            //
        ];
    }
}
