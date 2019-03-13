<?php

namespace Dees040\Repository\Tests\Unit\Eloquent;

use Dees040\Repository\Tests\App\Models\Post;
use Dees040\Repository\Tests\PackageTestCase;
use Dees040\Repository\Tests\App\Providers\Contracts\PostRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BaseRepositoryTest extends PackageTestCase
{
    /** @test */
    public function it_has_a_model_to_work_with()
    {
        $repository = app(PostRepository::class);

        $this->assertEquals(Post::class, $repository->getModel());
    }

    /** @test */
    public function it_can_find_all_models()
    {
        $models = $this->modelFactory->of(Post::class)->times(10)->create();

        $repository = app(PostRepository::class);

        $results = $repository->all();

        $this->assertCount(10, $results);
        $this->assertEquals($models->first()->id, $results->first()->id);
    }

    /** @test */
    public function it_can_paginate_all_models()
    {
        $this->modelFactory->of(Post::class)->times(20)->create();

        $repository = app(PostRepository::class);

        $results = $repository->paginate()->toArray();

        $this->assertEquals($results['total'], 20);
    }

    /** @test */
    public function it_can_find_models_where_in()
    {
        $strings = [str_random(10), str_random(10)];

        foreach ($strings as $string) {
            $this->modelFactory->of(Post::class)->times(10)->create([
                'title' => $string,
            ]);
        }

        $repository = app(PostRepository::class);

        $results = $repository->findWhereIn([
            'title' => [$strings[0]],
        ])->toArray();

        $this->assertCount(10, $results);
    }

    /** @test */
    public function it_can_find_models_where_in_multiple()
    {
        $strings = [str_random(10), str_random(10)];

        foreach ($strings as $string) {
            $this->modelFactory->of(Post::class)->times(5)->create([
                'title' => $string,
            ]);

            $this->modelFactory->of(Post::class)->times(5)->create([
                'title' => $string,
                'body' => $string,
            ]);
        }

        $repository = app(PostRepository::class);

        $results = $repository->findWhereIn([
            'title' => [$strings[0]],
            'body' => [$strings[0]],
        ])->toArray();

        $this->assertCount(5, $results);
    }

    /** @test */
    public function it_can_find_a_model_by_id()
    {
        $models = $this->modelFactory->of(Post::class)->times(10)->create();

        $repository = app(PostRepository::class);

        $model = $repository->find($models[2]->id);

        $this->assertEquals($models[2]->id, $model->id);
    }

    /** @test */
    public function it_can_find_the_first_model()
    {
        $models = $this->modelFactory->of(Post::class)->times(2)->create();

        $repository = app(PostRepository::class);

        $model = $repository->first();

        $this->assertEquals($models[0]->id, $model->id);
    }

    /** @test */
    public function it_throws_an_exception_if_the_find_and_fail_cant_find_the_model()
    {
        $this->expectException(ModelNotFoundException::class);

        $repository = app(PostRepository::class);

        $repository->findOrFail(0);
    }

    /** @test */
    public function it_can_create_a_model()
    {
        $repository = app(PostRepository::class);

        $attributes = [
            'title' => 'First post',
            'body' => 'Lorem ipsum',
        ];

        $model = $repository->create($attributes);

        $this->assertDatabaseHas('posts', $attributes);
        $this->assertEquals($model->title, $attributes['title']);
    }
}
