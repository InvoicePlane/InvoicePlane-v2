<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class BaseService
{
    protected Model $model;

    protected ?int $companyId;

    protected Application $app;

    /**
     * @param Application $app
     *
     * @throws Exception
     */
    public function __construct(Application $app)
    {
        $this->app       = $app;
        $this->companyId = $this->determineCompanyId();
        $this->makeModel();
    }

    abstract public function model(): string;

    public function makeModel(): Model
    {
        $model = $this->app->make($this->model());

        if ( ! $model instanceof Model) {
            throw new Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    public function paginate($perPage, $columns = ['*']): LengthAwarePaginator
    {
        return $this->allQuery()->paginate($perPage, $columns);
    }

    public function allQuery($search = [], $skip = null, $limit = null): Collection
    {
        $query = $this->model->newQuery();

        if (count($search)) {
            foreach ($search as $key => $value) {
                if (in_array($key, $this->getFieldsSearchable())) {
                    $query->where($key, $value);
                }
            }
        }

        if (null !== $skip) {
            $query->skip($skip);
        }

        if (null !== $limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function all($search = [], $skip = null, $limit = null, $columns = ['*'])
    {
        return $this->allQuery($search, $skip, $limit)->get($columns);
    }

    public function create(array $validatedInput): Model
    {
        $model = $this->model->newInstance($validatedInput);

        $model->save();

        return $model;
    }

    public function find($id, $columns = ['*'])
    {
        return $this->model->newQuery()->find($id, $columns);
    }

    public function update(array $input, $model)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($model);

        $model->fill($input);

        $model->save();

        return $model;
    }

    public function delete($id): ?bool
    {
        $query = $this->model->newQuery();

        return $query->findOrFail($id)?->delete();
    }

    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    protected function determineCompanyId(): ?int
    {
        // Check if the company_id is stored in the session
        if (session()?->has('current_company_id')) {
            return session('current_company_id');
        }

        // Fallback: use the user's first associated company
        $user = Auth::user();
        if ($user) {
            $company = $user->companies()->first();
            if ($company) {
                return $company->id;
            }
        }

        return null;  // No company available
    }

    private function getFieldsSearchable(): array
    {
        return [];
    }
}
