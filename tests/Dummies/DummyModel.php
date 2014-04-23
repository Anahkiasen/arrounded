<?php
namespace Arrounded\Dummies;

use Illuminate\Database\Eloquent\Model;
use Arrounded\Traits\ReflectionModel;

/**
 * A dummy model to test on
 */
class DummyModel extends Model
{
	use ReflectionModel;

	/**
	 * The guarded attributes
	 *
	 * @var array
	 */
	protected $guarded = array();
}
