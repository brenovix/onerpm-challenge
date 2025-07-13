<?php

namespace App\Repositories;

use App\Domain\Entity;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

abstract class Repository
{
    protected string $table;

    public function __invoke()
    {
        $this->query();
    }

    public function query(bool $includeAlias = true): Builder
    {
        $alias = $includeAlias ? strtoupper(substr($this->table, 0, 1)) : null;
        return DB::table($this->table, $alias);
    }

    public function fromId($id): object
    {
        $clause = is_array($id) ? $id : ['id' => $id];
        return $this->query()->where($clause)->get()->first();
    }

    public function delete(int $id)
    {
        $this->query()->delete($id);
    }

    public function insert(array|Entity $data): array
    {
        if ($data instanceof Entity) $data = $data->__serialize();
        $filteredData = $this->filterDataInsert($data);
        $id = $this->query(false)->insertGetId($filteredData);
        $data['id'] = $id;
        return $data;
    }
    
    public function update(Entity $entity): Entity
    {
        $id = $entity->getId();
        $this->query()->where('id', '=', $id)->update($entity->__serialize());
        return $entity;
    }

    public function getAll()
    {
        return $this->query()->get();
    }

    private function filterDataInsert(array $data)
    {
        return array_filter($data, fn ($item) => !is_array($item) && !empty($item) && !$item instanceof Entity);
    }
}