<?php

namespace App\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class MissingIsrcRepository
{
    protected string $table = 'missing_isrcs';

    public function query(): Builder
    {
        return DB::table($this->table);
    }

    public function delete(string $code)
    {
        $this->query()->where('code', $code)->delete();
    }

    public function insert(string $code)
    {
        return $this->query(false)->insert(['code' => $code]);
    }

    public function getAll()
    {
        return $this->query()->get();
    }
}