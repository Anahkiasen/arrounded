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

	/**
	 * The guarded attributes
	 *
	 * @type array
	 */
	protected $guarded = array();
}
