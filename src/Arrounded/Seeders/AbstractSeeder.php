<?php
namespace Arrounded\Seeders;

use Closure;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * An enhanced core seeder class
 */
abstract class AbstractSeeder extends Seeder
{
	/**
	 * The Faker instance
	 *
	 * @var Faker
	 */
	protected $faker;

	/**
	 * Build a new Seed
	 */
	public function __construct()
	{
		// Bind Faker instance if available
		if (class_exists('Faker\Factory')) {
			$this->faker = Faker::create();
		}
	}

	/**
	 * Run a seeder
	 *
	 * @param  string $table
	 *
	 * @return void
	 */
	public function seed($table)
	{
		$timer = microtime(true);
		$this->command->info('Seeding '.$table);
		$this->call($table.'TableSeeder');

		// Log results
		$results = Str::singular($table);
		if (class_exists($results)) {
			$timer   = round(microtime(true) - $timer, 2);
			$this->command->comment(sprintf('-- %s entries created (%ss)', $results::count(), $timer));
		}
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Generate X entries
	 *
	 * @param  Closure $closure
	 * @param integer  $min Minimum entries
	 * @param integer  $max Maximum entries
	 *
	 * @return void
	 */
	protected function generateEntries(Closure $closure, $min = 5, $max = null)
	{
		if (!$max) {
			$max = $min + 5;
		}

		// Execute the Closure n times
		$entries = array();
		$number  = $this->faker->randomNumber($min, $max);
		for ($i = 0; $i <= $number; $i++) {
			if ($entry = $closure($i)) {
				$entry = $entry->getAttributes();
				$entries[] = $entry;
			}
		}

		if (!empty($entries)) {
			$model = get_called_class();
			$model = str_replace('TableSeeder', null, $model);
			$model = Str::singular($model);

			$model::insert($entries);
		}
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
		if (!$max) {
			$max = $min + 5;
		}

		// Get a random number of elements
		$available = $model::lists('id');
		$number = $this->faker->randomNumber($min, $max);
		for ($i = 0; $i <= $number; $i++) {
			$entries[] = $this->faker->randomElement($available);
		}

		return $entries;
	}

	/**
	 * Get a random model from the database
	 *
	 * @param  string $model
	 *
	 * @return Eloquent
	 */
	protected function randomModel($model, $notIn = array())
	{
		$models = $model::query();
		if ($notIn) {
			$models = $models->whereNotIn('id', $notIn);
		}

		return $this->faker->randomElement($models->lists('id'));
	}
}
