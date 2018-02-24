<?php

namespace CrixuAMG\Decorators\Repositories;

use CrixuAMG\Decorators\Contracts\RepositoryContract;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractRepository
 *
 * @package CrixuAMG\Decorators\Repositories
 */
abstract class AbstractRepository implements RepositoryContract
{
	/**
	 * Returns the index
	 *
	 * @param $page
	 *
	 * @return mixed
	 */
	abstract public function index($page);

	/**
	 * Return a single model
	 *
	 * @param Model $model
	 *
	 * @return mixed
	 */
	public function show(Model $model)
	{
		if (method_exists(\get_class($model), 'getValidRelations')) {
			// Load relationships
			$model->load(\get_class($model)::getValidRelations());
		}

		// Return the model
		return $model;
	}

	/**
	 * Create a new model
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	abstract public function store(array $data);

	/**
	 * Update an model
	 *
	 * @param Model $model
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function update(Model $model, array $data)
	{
		// Update the model
		$model->update($data);

		if (method_exists(\get_class($model), 'getValidRelations')) {
			// Load relationships
			$model->load(\get_class($model)::getValidRelations());
		}

		// Return the new model
		return $model;
	}

	/**
	 * Delete an model
	 *
	 * @param Model $model
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function delete(Model $model)
	{
		return $model->delete();
	}
}