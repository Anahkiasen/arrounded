<?php
namespace Fakable;

/**
 * Implements faking methods to a model
 */
trait FakableModel
{
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

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// FAKABLES ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the fakable attributes
	 *
	 * @return array
	 */
	public function getFakables()
	{
		return array_merge($this->defaultFakables, $this->fakables);
	}

	////////////////////////////////////////////////////////////////////
	//////////////////////////// FAKE INSTANCES ////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a fakable instance
	 *
	 * @return Fakable
	 */
	public static function fakable()
	{
		return new Fakable(new static);
	}

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
		$fakable = static::fakable();
		$fakable->setAttributes($attributes);
		$fakable->setSaved($save);

		return $fakable->fakeModel();
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
	public static function fakeMultiple($attributes = array(), $min = 5, $max = null)
	{
		// Skippable arguments
		if (is_int($attributes)) {
			$min = $attributes;
			$attributes = array();
		}

		$fakable = static::fakable();
		$fakable->setPool($min, $max);
		$fakable->setAttributes($attributes);

		return $fakable->fakeMultiple();
	}
}
