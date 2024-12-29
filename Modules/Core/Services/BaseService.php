<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     *
     * @throws Exception
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Configure the Model.
     *
     * @return string
     */
    abstract public function model();

    /**
     * Make Model instance.
     *
     * @return Model
     *
     * @throws Exception
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if ( ! $model instanceof Model) {
            throw new Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Paginate records for scaffold.
     *
     * @param int   $perPage
     * @param array $columns
     *
     * @return LengthAwarePaginator
     */
    public function paginate($perPage, $columns = ['*'])
    {
        $query = $this->allQuery();

        return $query->paginate($perPage, $columns);
    }

    /**
     * Build a query for retrieving all records.
     *
     * @param array    $search
     * @param int|null $skip
     * @param int|null $limit
     *
     * @return Builder
     */
    public function allQuery($search = [], $skip = null, $limit = null)
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

    /**
     * Retrieve all records with given filter criteria.
     *
     * @param array    $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array    $columns
     *
     * @return LengthAwarePaginator|Builder[]|Collection
     */
    public function all($search = [], $skip = null, $limit = null, $columns = ['*'])
    {
        $query = $this->allQuery($search, $skip, $limit);

        return $query->get($columns);
    }

    /**
     * Create model record.
     *
     * @param array $validatedInput
     *
     * @return Model
     */
    public function create(array $validatedInput)
    {
        $model = $this->model->newInstance($validatedInput);

        $model->save();

        return $model;
    }

    /**
     * Find model record for given id.
     *
     * @param int   $id
     * @param array $columns
     *
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function find($id, $columns = ['*'])
    {
        $query = $this->model->newQuery();

        return $query->find($id, $columns);
    }

    /**
     * Update model record for given id.
     *
     * @param array $input
     * @param Model $model
     *
     * @return Builder|Builder[]|Collection|Model
     */
    public function update(array $input, $model)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($model);

        $model->fill($input);

        $model->save();

        return $model;
    }

    /**
     * @param int $id
     *
     * @return bool|mixed|null
     *
     * @throws Exception
     */
    public function delete($id)
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        return $model->delete();
    }

    private function getFieldsSearchable(): void {}
}
