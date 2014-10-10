<?php
namespace Arrounded\Dummies;

use Arrounded\Traits\Reflection\ReflectionModel;
use Illuminate\Database\Eloquent\Model;

/**
 * A dummy model to test on
 */
class DummyModel extends Model
{
	use ReflectionModel;

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
