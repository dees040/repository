<?php

namespace Dees040\Repository\Tests\Unit\Criteria;

use Dees040\Repository\Tests\App\Models\Post;
use Dees040\Repository\Tests\PackageTestCase;
use Dees040\Repository\Criteria\RequestCriteria;
use Dees040\Repository\Tests\App\Providers\Contracts\PostRepository;

class RequestCriteriaTest extends PackageTestCase
{
    /**
     * Create a basic model and apply the request criteria.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function createModelAndApplyCriteria($attributes = [])
    {
        $repository = app(PostRepository::class);
        $model = $this->modelFactory->of(Post::class)->create($attributes);

        return with(new RequestCriteria)->apply($model, $repository);
    }

    /** @test */
    public function it_can_add_a_order_by_query()
    {
        request()->query->set('order_by', 'title');
        request()->query->set('sort_by', 'desc');

        $model = $this->createModelAndApplyCriteria();

        $orders = $model->getQuery()->orders;

        $values = [[
            'column' => 'title',
            'direction' => 'desc',
        ]];

        $this->assertEquals($orders, $values);
    }

    /** @test */
    public function it_orders_by_asc_by_default()
    {
        request()->query->set('order_by', 'title');

        $model = $this->createModelAndApplyCriteria();

        $orders = $model->getQuery()->orders;

        $values = [[
            'column' => 'title',
            'direction' => 'asc',
        ]];

        $this->assertEquals($orders, $values);
    }

    /** @test */
    public function it_can_search()
    {
        request()->query->set('search', 'Find me');
        request()->query->set('search_fields', 'title');

        $this->modelFactory->of(Post::class)->create([
            'title' => 'Hidden',
        ]);

        $query = $this->createModelAndApplyCriteria(['title' => 'Find me']);

        $wheres = $query->getQuery()->wheres;

        $models = $query->get();

        $values = [[
            'type' => 'Basic',
            'column' => 'title',
            'operator' => '=',
            'value' => 'Find me',
            'boolean' => 'or',
        ]];

        $this->assertEquals($wheres, $values);
        $this->assertEquals(2, $models->first()->id);
    }

    /** @test */
    public function it_search_on_all_fields_by_default()
    {
        request()->query->set('search', 'Find me');

        $query = $this->createModelAndApplyCriteria(['title' => 'Find me']);

        $wheres = $query->getQuery()->wheres;

        $models = $query->get();

        $values = [
            [
                'type' => 'Basic',
                'column' => 'title',
                'operator' => '=',
                'value' => 'Find me',
                'boolean' => 'or',
            ],
            [
                'type' => 'Basic',
                'column' => 'body',
                'operator' => '=',
                'value' => 'Find me',
                'boolean' => 'or',
            ]
        ];


        $this->assertEquals($wheres, $values);
        $this->assertEquals(1, $models->first()->id);
    }

    /** @test */
    public function it_can_search_with_like_operator()
    {
        request()->query->set('search', 'me');
        request()->query->set('search_fields', 'title:like');

        $query = $this->createModelAndApplyCriteria(['title' => 'Find me']);

        $wheres = $query->getQuery()->wheres;

        $models = $query->get();

        $values = [[
            'type' => 'Basic',
            'column' => 'title',
            'operator' => 'like',
            'value' => '%me%',
            'boolean' => 'or',
        ]];

        $this->assertEquals($wheres, $values);
        $this->assertEquals(1, $models->first()->id);
    }

    /** @test */
    public function it_can_only_search_on_searchable_fields()
    {
        request()->query->set('search', 'me');
        request()->query->set('search_fields', 'title:like,user_id');

        $query = $this->createModelAndApplyCriteria(['title' => 'Find me']);

        $wheres = $query->getQuery()->wheres;

        $models = $query->get();

        $values = [[
            'type' => 'Basic',
            'column' => 'title',
            'operator' => 'like',
            'value' => '%me%',
            'boolean' => 'or',
        ]];

        $this->assertEquals($wheres, $values);
        $this->assertEquals(1, $models->first()->id);
    }
}
