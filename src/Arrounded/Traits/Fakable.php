<?php
namespace Arrounded\Traits;

use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * Implements faking methods to a model
 */
trait Fakable
{
	/**
	 * The Faker instance
	 *
	 * @var Faker
	 */
	protected $faker;

	/**
	 * The fakable attributes
	 *
	 * @var array
	 */
	protected $fakables = array();

	/**
	 * The default fakable attributes
	 *
	 * @var array
	 */
	private $defaultFakables = array(
		'name'          => ['sentence', 5],
		'gender'        => ['randomNumber', [0, 1]],
		'age'           => ['randomNumber', [1, 90]],
		'note'          => ['randomNumber', [1, 10]],

		'contents'      => ['paragraph', 5],
		'biography'     => ['paragraph', 5],

		'email'         => 'email',
		'password'      => 'word',
		'website'       => 'url',
		'address'       => 'address',
		'country'       => 'country',
		'city'          => 'city',

		'private'       => 'boolean',
		'public'        => 'boolean',

		'created_at'    => 'dateTimeThisMonth',
		'updated_at'    => 'dateTimeThisMonth',

		'from_user_id'  => ['randomModel', 'User'],
		'user_id'       => 'randomModel',
		'discussion_id' => 'randomModel',
	);

	/**
	 * Get the Faker instance
	 *
	 * @return Faker
	 */
	protected function faker()
	{
		if (!$this->faker) {
			$this->faker = Faker::create();
		}

		return $this->faker;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////// FAKE INSTANCES ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Fake a new instance
	 *
	 * @param array   $attributes
	 * @param boolean $save
	 *
	 * @return self
	 */
	public static function fake(array $attributes = array(), $save = false)
	{
		$self = new static;

		return $self->getFakeInstance($attributes, $save);
	}

	/**
	 * Fake multiple new instances
	 *
	 * @param array    $attributes
	 * @param integer  $min
	 * @param integer  $max
	 *
	 * @return Collection
	 */
	public static function fakeMultiple(array $attributes = array(), $min = 5, $max = null)
	{
		$self  = new static;
		$max   = $max ?: $min + 5;
		$times = $self->faker()->randomNumber($min, $max);

		for ($i = 0; $i <= $times; $i++) {
			$self->getFakeInstance($attributes, true);
		}

		return static::all();
	}

	/**
	 * Fake a new instance
	 *
	 * @param array   $attributes
	 * @param boolean $save
	 *
	 * @return self
	 */
	protected function getFakeInstance(array $attributes = array(), $save = false)
	{
		// Get the fakable attributes
		$fakables = array_merge($this->defaultFakables, $this->fakables);
		$instance = new static;

		// Generate dummy attributes
		$relations  = array();
		$defaults = array();
		foreach ($fakables as $attribute => $signature) {
			$value = $this->callFromSignature($defaults, $attribute, $signature);

			if (method_exists($this, $attribute) and $signature[0] === 'randomModels') {
				$relations[$attribute] = ['sync', $value];
			}
		}

		// Fill attributes and save
		$attributes = array_merge($defaults, $attributes);
		$instance->fill($attributes);
		if ($save) {
			$instance->save();
		}

		// Set relations
		foreach($relations as $name => $signature) {
			list ($method, $value) = $signature;
			$instance->$name()->$method($value);
		}

		return $instance;
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////// RELATIONSHIPS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a random primary key of a model
	 *
	 * @param string $model
	 * @param array  $notIn
	 *
	 * @return integer
	 */
	protected function randomModel($model, array $notIn = array())
	{
		$model  = new $model;
		$models = $model::query();
		if ($notIn) {
			$models = $models->whereNotIn($model->getKeyName(), $notIn);
		}

		return $this->faker()->randomElement($models->lists('id'));
	}

	/**
	 * Get a random polymorphic relation
	 *
	 * @param string|array $models The possible models
	 *
	 * @return array [string, type]
	 */
	public function randomPolymorphic($models)
	{
		$models = (array) $models;
		$model  = $this->faker()->randomElement($models);

		return [$model, $this->randomModel($model)];
	}

	/**
	 * Return an array of random models IDs
	 *
	 * @param string $model
	 *
	 * @return array
	 */
	protected function randomModels($model, $min = 5, $max = null)
	{
		// Get a random number of elements
		$max       = $max ?: $min + 5;
		$available = $model::lists('id');
		$number    = $this->faker()->randomNumber($min, $max);

		$entries = array();
		for ($i = 0; $i <= $number; $i++) {
			$entries[] = $this->faker()->randomElement($available);
		}

		return $entries;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Transform a fakable array to a signature
	 *
	 * @param array  $attributes
	 * @param string $attribute
	 * @param array  $signature
	 *
	 * @return array
	 */
	protected function callFromSignature(array &$attributes, $attribute, $signature)
	{
		// Get the method signature
		if (is_array($signature)) {
			$method    = array_get($signature, 0);
			$arguments = (array) array_get($signature, 1, array());
		} else {
			$method    = $signature;
			$arguments = array();
		}

		// For 1:1, get model name
		$model     = $this->getModelFromAttributeName($attribute);
		$arguments = $this->getArgumentsFromMethod($method, $attribute, $arguments);

		// Get the source of the method
		$source = method_exists($this, $method) ? $this : $this->faker();
		$value  = call_user_func_array([$source, $method], $arguments);

		if ($method === 'randomPolymorphic') {
			list ($model, $key) = $value;
			$attributes[$attribute.'_type'] = $model;
			$attributes[$attribute.'_id']  = $key;
		} else {
			$attributes[$attribute] = $value;
		}

		return $value;
	}

	/**
	 * Get the model associated with an attribute
	 *
	 * @param string $attribute
	 *
	 * @return string
	 */
	protected function getModelFromAttributeName($attribute)
	{
		return ucfirst(str_replace('_id', '', $attribute));
	}

	/**
	 * Get the default arguments for a relation method
	 *
	 * @param string $method
	 * @param string $attribute
	 * @param array  $arguments
	 *
	 * @return array
	 */
	protected function getArgumentsFromMethod($method, $attribute, $arguments = array())
	{
		if (!empty($arguments)) {
			return $arguments;
		}

		// Compute default model arguments
		$model = $this->getModelFromAttributeName($attribute);
		if (Str::contains($attribute, '_id')) {
			$arguments = [$model];
		} elseif ($method === 'randomModels') {
			$arguments = [Str::singular($model)];
		}

		return $arguments;
	}
}
