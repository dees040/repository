# Laravel Repository Package

**THIS PACKAGE IS A WORK IN PROGRESS**

This packages helps you to easily implement the repository package in your Laravel application.

**Why use this package out of the many other Laravel repository package?** This package was inspired by multiple other Laravel repository package. I took all the good things from those packages and create one unique package for it. Also I keep maintain this package, because I use it in multiple of my own projects.

## Installation

You can install this package using composer, by running the following command:

```bash
composer require dees040/repository
```

The service provider is automatically added via Laravel package discovery. 

## Methods

**Dees040\Repository\Contracts\Repository**  
- `all($columns = ['*'])`  
- `paginate($perPage = null, $columns = ['*'])`  
- `first($columns = ['*'])`  
- `find($id, $columns = ['*'])`  
- `findOrFail($id, $columns = ['*'])`  
- `findByField($field, $value, $columns = ['*'])`  
- `findWhere(array $where, $columns = ['*'])`  
- `findWhereIn(array $where, $columns = ['*'])`  
- `firstOrCreate(array $attributes, array $values = [])`  
- `create(array $attributes = [])`  
- `update($id, array $attributes)`  
- `updateOrCreate(array $attributes, array $values = [])`  
- `delete($id)`  
- `deleteWhere(array $where)`  
- `orderBy($column, $direction = 'asc')`  
- `with($relations)`  
- `has($relation)`  
- `whereHas($relation, \Closure $closure)`  
- `orWhereHas($relation, \Closure $closure)`  
- `sync($id, $relation, $attributes, $detaching = true)`  
- `syncWithoutDetaching($id, $relation, $attributes)`  

## Usage

### Creating a repository

Repositories can be created by hand or via a command provided in this package. If you'd like to create a repository by hand you should create two files for each repository.

One class for the repository contract:

```php
<?php

namespace App\Repositories\Contracts;

use Dees040\Repository\Contracts\Repository;

interface PostRepository extends Repository
{

}

```

And one class for the actual repository:

```php
<?php

namespace App\Repositories;

use App\Post;
use App\Repositories\Contracts\PostRepository;
use Dees040\Repository\Eloquent\BaseRepository;

class PostEloquentRepository extends BaseRepository implements PostRepository
{
    /**
     * Get the base model.
     *
     * @return string
     */
    public function getModel()
    {
        return Post::class;
    }
}

```

The other option is creating the file using the `make:repository` command:

```bash
php artisan make:repository Post
```

### Usage in controllers

After creating the repository you should bind the repository contract to the correct repository using the Laravel Container. By doing this Laravel knows exactly which repository to use for which contract. This makes it very easy to switch between a eloquent repository or e.g. a MongoDB repository.

So before using any repositories in your controller make sure to bind it to the container. You can do that using the `bind($abstract, concrete)` method in the `Container`. E.g.: `App::bind(\App\Repositories\Contracts\PostRepository::class, \App\Repositories\PostEloquentRepository::class)`.

Personally I like to create a new service provider named `\App\Providers\RepositoryServiceProvider.php` and do something like this:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * The contracts with their associated repositories.
     *
     * @var array
     */
    protected $repositories = [
        \App\Repositories\Contracts\PostRepository::class => \App\Repositories\PostEloquentRepository::class,
    ];
    
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
       //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->repositories as $contract => $repository) {
            $this->app->bind($contract, $repository);
        }
    }
}
```

After binding in to the container you can use dependency injection inside your controller to get the repository.

```php
public function __contruct(PostRepository $repostiroy)
{
    $this->repository = $repostiroy;
}
```

### Using criteria

If you'd like to execute custom queries to the database call you can use criteria. You can add criteria to your call by calling the `pushCriteria` method.

```php
$this->repository->pushCriteria(new Criteria());
$this->repository->pushCriteria(Criteria::class);
```

Make sure that your custom criteria classes are implementing the `\Dees040\Repository\Contracts\Criteria` interface. An example of custom criteria is:

```php
<?php

namespace App\Criteria;

use Dees040\Repository\Contracts\Criteria;

class HasUserRelationCriteria implements Criteria
{
    /**
     * Execute the criteria.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Dees040\Repository\Contracts\Repository  $repository
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function apply($model, $repository)
    {
        return $this->model->orderBy('created_at')
            ->whereHas('users', function ($query) {
                $query->where('users.id', 1);
            });
    }
}
```

#### RequestCriteria

The package ships with a useful criteria out of the box. The `RequestCriteria`. It gives you the ability to easily perform extra actions on your queries via the route parameters. You can add order by items or search through your models. You can add the criteria by default in the constructor of a repository:

```php
public function __construct()
{
    $this->pushCriteria(\Dees040\Repository\Criteria\RequestCriteria::class);
}
```

After doing this you can add following query parameters.

- `https://example.test/posts?order_by=created_at`  
- `https://example.test/posts?order_by=created_at&sort_by=desc`  
- `https://example.test/posts?search=Lorem ipsum`  
- `https://example.test/posts?search=Lorem ipsum&search_fields=body`  
- `https://example.test/posts?search=Lorem ipsum&search_fields=title,body:like`  
- `https://example.test/posts?search=Lorem ipsum&search_fields=title,body:like&order_by=title`  

If you'd like to have the ability to search you should override the `getSearchableFields()` method in your repository. All the fields returned in the array of that method are then searchable via the route parameters.

```php
/**
 * Get all fields which are searchable.
 *
 * @return array
 */
public function getSearchableFields()
{
    return [
        'title',
        'body',
    ];
}
```

## TODOS
- [x] Create a build in criteria class for requests 
- [x] Search fields in the request criteria
- [ ] Create cache
- [ ] Parse results
- [ ] Pagination options
- [ ] Search on custom fields in request criteria
- [ ] Dynamic criteria class
- [ ] Regex split on search fields
- [ ] Add dot nation for relations in request criteria
- [ ] Create commands
