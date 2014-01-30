<?php
namespace Fakable;

use DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Generates a fake model
 */
class Fakable
{
	/**
	 * The model to fake
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * The attributes to set on the fake models
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * The pool of models
	 *
	 * @var integer
	 */
	protected $pool;

	/**
	 * Whether fake models created should be saved or not
	 *
	 * @var integer
	 */
	protected $saved = true;

	/**
	 * Whether to batch insert models or not
	 *
	 * @var boolean
	 */
	protected $batch = true;

	/**
	 * The relations to seed
	 *
	 * @var array
	 */
	protected $relations = array();

	/**
	 * The generated models
	 *
	 * @var Collection
	 */
	protected $generated = array();

	/**
	 * Create a new Fakable instance
	 *
	 * @param Model   $model
	 * @param array   $attributes
	 * @param boolean $saved
	 */
	public function __construct(Model $model)
	{
		$this->faker = Faker::create();
		$this->model = clone $model;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// OPTIONS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Save or not the generated models
	 *
	 * @param boolean $saved
	 *
	 * @return self
	 */
	public function setSaved($saved)
	{
		$this->saved = $saved;

		return $this;
	}

	/**
	 * batch or not the generated models
	 *
	 * @param boolean $batch
	 *
	 * @return self
	 */
	public function setBatch($batch)
	{
		$this->batch = $batch;

		return $this;
	}

	/**
	 * Set the attributes to overwrite on the fake model
	 *
	 * @param array $attributes
	 *
	 * @return self
	 */
	public function setAttributes(array $attributes = array())
	{
		if (!empty($attributes)) {
			$this->attributes = $attributes;
		}

		return $this;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////////// POOL /////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Set the pool of models
	 *
	 * @param integer $min
	 * @param integer $max
	 *
	 * @return self
	 */
	public function setPool($min, $max = null)
	{
		$max = $max ?: $min + 5;
		$this->pool = $this->faker->randomNumber($min, $max);

		return $this;
	}

	/**
	 * Set the pool from the count of another model
	 *
	 * @param string  $model
	 * @param integer $power
	 *
	 * @return self
	 */
	public function setPoolFromModel($model, $power = 2)
	{
		$this->pool = $model::count() * $power;

		return $this;
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// GENERATION ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Fake a single model instance
	 *
	 * @param attributes $array
	 *
	 * @return Model
	 */
	public function fakeModel(array $attributes = array(), $generateRelations = true)
	{
		$this->setAttributes($attributes);

		// Get the fakable attributes
		$fakables = $this->model->getFakables();
		$instance = $this->model->newInstance();

		// Generate dummy attributes
		$relations = array();
		$defaults  = array();
		foreach ($fakables as $attribute => $signature) {
			$signature = (array) $signature;
			$value = $this->callFromSignature($defaults, $attribute, $signature);

			if (!method_exists($this->model, $attribute)) {
				continue;
			}

			if ($signature[0] === 'randomModels') {
				$relations[$attribute] = ['sync', [$value]];
			} elseif ($signature[0] === 'randomPivots') {
				list ($ids, $attributes) = $value;
				foreach ($ids as $id) {
					$relations[$attribute] = ['attach', [$id, $attributes]];
				}
			}
		}

		// Fill attributes and save
		$attributes = array_merge($defaults, $this->attributes);
		$instance->fill($attributes);
		if ($instance->usesTimestamps()) {
			$instance->created_at = $attributes['created_at'];
			$instance->updated_at = $attributes['updated_at'];
		}

		if ($this->saved and !$this->batch) {
			$instance->save();
		}

		// Save instance
		$instance->id = sizeof($this->generated) + 1;
		$this->relations[$instance->id] = $relations;
		$this->generated[$instance->id] = $instance;

		// Generate relations if necessary
		if ($generateRelations) {
			$this->fakeRelations();
		}

		return $instance;
	}

	/**
	 * Fake multiple model instances
	 *
	 * @param attributes $array
	 *
	 * @return void
	 */
	public function fakeMultiple(array $attributes = array())
	{
		$this->setAttributes($attributes);

		// Create models
		for ($i = 0; $i <= $this->pool; $i++) {
			$this->fakeModel([], false);
		}

		// Create relations
		$this->fakeRelations();
	}

	/**
	 * Insert the generated models as one
	 *
	 * @return void
	 */
	protected function insertGeneratedEntries()
	{
		// Cast all to array
		$entries = Collection::make($this->generated)->map(function ($entry) {
			return $entry->getAttributes();
		})->all();
		$slices = array($entries);

		// If the engine is SQLite and we have a lot of seeded entries
		// We'll split the results to not overflow the variable limit
		if (DB::getDriverName() === 'sqlite') {
			$slicer = floor(999 / sizeof($entries[0]));
			$slices = array_chunk($entries, $slicer);
		}

		foreach ($slices as $entries) {
			$this->model->insert($entries);
		}
	}

	/**
	 * Generate fake relations
	 *
	 * @return void
	 */
	public function fakeRelations()
	{
		// Save the created models
		if ($this->batch) {
			$this->insertGeneratedEntries();
		}

		// Generate the relations
		foreach ($this->generated as $instance) {
			$relations = array_get($this->relations, $instance->id, array());

			foreach($relations as $name => $signature) {
				list ($method, $value) = $signature;
				call_user_func_array([$instance->$name(), $method], $value);
			}
		}
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

		return $this->faker->randomElement($models->lists('id'));
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
		$model  = $this->faker->randomElement($models);

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
		$available = empty($available) ? range(1, $this->pool) : $available;
		$number    = $this->faker->randomNumber($min, $max);

		$entries = array();
		for ($i = 0; $i <= $number; $i++) {
			$entries[] = $this->faker->randomElement($available);
		}

		return array_unique($entries);
	}

	/**
	 * Get arguments for a random pivot
	 *
	 * @param string $model
	 * @param array  $attributes
	 *
	 * @return array [id, attributes]
	 */
	protected function randomPivots($model, array $attributes = array(), $min = 5, $max = null)
	{
		return [$this->randomModels($model, $min, $max), $attributes];
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
		$source = method_exists($this, $method) ? $this : $this->faker;
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