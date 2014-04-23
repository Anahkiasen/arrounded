<?php
namespace Arrounded\Dummies;

use Illuminate\Database\Eloquent\Model;
use Arrounded\Traits\ReflectionModel;
use Arrounded\Traits\Serializable;

/**
 * A dummy model to test on
 */
class DummyModel extends Model
{
	use ReflectionModel;
	use Serializable;

	protected $casts = array(
		'integer' => 'id',
		'boolean' => ['status'],
	);

	/**
	 * The guarded attributes
	 *
	 * @var array
	 */
	protected $guarded = array();
}
