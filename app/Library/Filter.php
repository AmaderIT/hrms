<?php

namespace App\Library;

class Filter
{
    /**
     * @var null
     */
    protected $model = null;

    /**
     * @var null
     */
    protected $columns = null;

    /**
     * @var null
     */
    protected $value = null;

    /**
     * Filter constructor.
     *
     * @param $model
     * @param $columns
     * @param $value
     */
    public function __construct($model, $columns, $value)
    {
        $this->model    = $model::query();
        $this->columns  = $columns;
        $this->value    = $value;

        $this->filter();
    }

    /**
     * @return null
     */
    public function filter()
    {
        foreach ($this->columns as $column) {
            $this->model = $this->model->orWhere($column, "LIKE", "%" . $this->value . "%");
        }
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->model, $method], $arguments);
    }

    /**
     * @param $model
     * @param $columns
     * @param $value
     */
    public static function filterData($model, $columns, $value)
    {
        foreach ($columns as $column) {
            $model = $model->orWhere($column, "LIKE", "%" . $value . "%");
        }
    }
}
