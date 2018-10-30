<?php

namespace Dees040\Repository\Tests\App\Providers;

use Dees040\Repository\Tests\App\Models\Post;
use Dees040\Repository\Eloquent\BaseRepository;
use Dees040\Repository\Tests\App\Providers\Contracts\PostRepository;

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
}
