<?php

namespace App\Traits;

trait HasIndexRules
{
    protected function indexRules(): array
    {
        return [
            'trashed' => [ 'nullable', 'boolean'],
            'sort' => ['in:asc,desc'],
            'paginate' => ['integer'],
            'page' => ['integer'],
        ];
    }

    protected function mergeRules(array $rules): array
    {
        return array_merge($this->indexRules(), $rules);
    }
}
