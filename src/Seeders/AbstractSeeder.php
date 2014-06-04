<?php
namespace Arrounded\Seeders;

use Closure;
use DB;
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
	 * The namespace where the models are
	 *
	 * @var string
	 */
	protected $models;

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

	/**
	 * Insert items by chunks
	 *
	 * @param string  $table
	 * @param array   $items
	 * @param integer $chunks
	 *
	 * @return void
	 */
	public function insertChunked($table, $items, $chunks = 2500)
	{
		// Insert directly if less than chunks
		if (sizeof($items) < $chunks) {
			return DB::table($table)->insert($items);
		}

		// Enforce SQLite limitations
		if (DB::getDriverName() == 'sqlite') {
			$chunks = sizeof(head($items)) ?: 10;
			$chunks = floor(999 / $chunks);
		}

		// Chunk entries
		$slices = $chunks ? array_chunk($items, $chunks) : array($items);
		$this->progressIterator($slices, function($items) use ($table) {
			DB::table($table)->insert($items);
		});
	}
	/**
	 * Print progress on an iterator
	 *
	 * @param array $items
	 *
	 * @return void
	 */
	public function progressIterator($items, Closure $closure)
	{
		$output     = $this->command->getOutput();
		$iterations = sizeof($items);

		if (class_exists('Symfony\Component\Console\Helper\ProgressBar')) {
			$progress = new \Symfony\Component\Console\Helper\ProgressBar($output, $iterations);
			$progress->start();
		} else {
			$progress = $this->command->getHelper('progress');
			$progress->start($output, $iterations);
		}

		foreach ($items as $value) {
			$progress->advance();
			$closure($value, $progress);
		}
		$progress->finish();
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// SEEDERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Loop over the specified models
	 *
	 * @param string|array  $models
	 * @param Closure       $closure
	 * @param integer       $min
	 * @param integer       $max
	 *
	 * @return void
	 */
	protected function forModels($models, Closure $closure, $min = 1, $max = null)
	{
		$max = $max ?: $min;

		$models = (array) $models;
		foreach ($models as $model) {
			$model   = $this->models.$model;
			$entries = $model::lists('id');
			foreach ($entries as $entry) {
				$this->times(function () use ($closure, $entry, $model) {
					$closure($entry, $model);
				}, $min, $max);
			}
		}
	}

	/**
	 * Generate X entries
	 *
	 * @param  Closure $closure
	 * @param integer  $min
	 * @param integer  $max
	 *
	 * @return void
	 */
	protected function generateEntries(Closure $closure, $min = 5, $max = null)
	{
		$isTesting = app()->environment('testing');

		// Execute the Closure n times
		$table   = null;
		$entries = array();
		$this->times(function ($i) use ($closure, &$entries, $isTesting, &$table) {
			if (!$isTesting) print '.';
			if ($entry = $closure($i)) {
				if (!$table) {
					$table = $entry->getTable();
				}

				$entry = $entry->getAttributes();
				$entries[] = $entry;
			}
		}, $min, $max);

		// If we're not testing, print progress
		if (!$isTesting) {
			print PHP_EOL;
		}

		// Get the table to insert into and insert aaaall the things
		if (!empty($entries)) {
			$slices = DB::getDriverName() === 'sqlite' ? floor(999 / sizeof($entries[0])) : null;
			$this->insertChunked($table, $entries, $slices);
		}
	}

	/**
	 * Generate pivot relationships
	 *
	 * @return void
	 */
	protected function generatePivotRelations($model, $modelTwo)
	{
		$foreign    = snake_case($model).'_id';
		$foreignTwo = snake_case($modelTwo).'_id';
		$table      = snake_case($model).'_'.snake_case($modelTwo);

		$number = $this->models.$modelTwo;
		$number = $number::count() * 5;

		for ($i = 0; $i <= $number; $i++) {
			$attributes = array(
				$foreign    => $this->randomModel($model),
				$foreignTwo => $this->randomModel($modelTwo),
			);

			DB::table($table)->insert($attributes);
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
		// Get a random number of elements
		$model     = $this->models.$model;
		$available = $model::lists('id');

		$this->times(function () use ($available, &$entries) {
			$entries[] = $this->faker->randomElement($available);
		}, $min, $max);

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
		$model  = $this->models.$model;
		$models = $model::query();
		if ($notIn) {
			$models = $models->whereNotIn('id', $notIn);
		}

		return $this->faker->randomElement($models->lists('id'));
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Execute an action from $min to $max times
	 *
	 * @param integer  $min
	 * @param integer  $max
	 * @param Closure  $closure
	 *
	 * @return void
	 */
	protected function times(Closure $closure, $min, $max = null)
	{
		// Define the number of times to loop over
		$max   = $max ?: $min + 5;
		$times = $this->faker->numberBetween($min, $max);

		for ($i = 0; $i <= $times; $i++) {
			$closure($i);
		}
	}
}
