<?php

namespace Dees040\Repository\Tests;

use Faker\Generator;
use Orchestra\Testbench\TestCase;
use Faker\Factory as FakerFactory;
use Dees040\Repository\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Dees040\Repository\Tests\App\Models\Post;
use Dees040\Repository\Tests\App\Providers\PostEloquentRepository;
use Dees040\Repository\Tests\App\Providers\Contracts\PostRepository;

abstract class PackageTestCase extends TestCase
{
    /**
     * The Eloquent model factory instance.
     *
     * @var \Illuminate\Database\Eloquent\Factory
     */
    protected $modelFactory;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $faker = FakerFactory::create();
        $this->modelFactory = new Factory($faker);
        $this->modelFactory->define(Post::class, function (Generator $faker) {
            return [
                'title' => $faker->sentence,
                'body' => $faker->realText(),
            ];
        });

    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Post::truncate();

        parent::tearDown();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application   $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->bind(PostRepository::class, PostEloquentRepository::class);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
