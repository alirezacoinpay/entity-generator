<?php

namespace App\Repositories;

class BaseCacheRepository
{
    public function __construct(
        protected BaseRepository $repository,
    )
    {}
    public function update($id, $data)
    {

        return $this->repository->update($id, $data);
    }
    public function delete($id)
    {
        return $this->repository->delete($id);
    }
    public function restore($id)
    {
        return $this->repository->restore($id);
    }
    public function create($data)
    {
        return $this->repository->create($data);
    }

    protected function generateKey($data): string
    {
        $backtrace = debug_backtrace();

        $methodName = $backtrace[1]['function'] ?? 'unknown';

        $prefix = $backtrace[0]['object']->prefixes[$methodName] ?? $methodName;

        $flatParams = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


        return md5($prefix .':'. $flatParams);
    }



}
