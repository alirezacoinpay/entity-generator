<?php

namespace App\Repositories;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseRepository
{
    public function __construct(protected Model $model)
    {}
    public function findByIdLight($id)
    {
       $query = $this->model->newQuery();

       if ($this->supportsSoftDeletes()) {
           $query->withTrashed();
       }

       return $query->find($id);
    }
    public function create(array $data)
    {
        return $this->model->create($data);
    }
    public function update($id, array $data)
    {
        return $this->model->find($id)?->update($data);
    }
    public function delete($id)
    {
        return $this->model->find($id)?->delete();
    }
    public function restore($id)
    {
        return $this->model->onlyTrashed()->find($id)?->restore();
    }

    protected function supportsSoftDeletes(): bool
    {
       return in_array(SoftDeletes::class, class_uses_recursive($this->model));
    }

}
