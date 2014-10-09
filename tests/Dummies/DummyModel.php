<?php
namespace Arrounded\Dummies;

use Arrounded\Traits\Serializable;
use Illuminate\Database\Eloquent\Model;

/**
 * A dummy model to test on
 */
class DummyModel extends Model
{
	use \Arrounded\Traits\Reflection\ReflectionModel;
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
