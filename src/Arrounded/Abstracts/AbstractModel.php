<?php
namespace Arrounded\Abstracts;

use Arrounded\Traits\Serializable;
use Arrounded\Traits\ReflectionModel;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractModel extends Model
{
	use ReflectionModel;
	use Serializable;

	/**
	 * The attributes to cast on serialization
	 *
	 * @var array
	 */
	protected $casts = array(
		'integer' => ['id'],
	);

	/**
	 * Cast the model to an array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$model = parent::toArray();
		$model = $this->serializeEntity($model);

		return $model;
	}
}
