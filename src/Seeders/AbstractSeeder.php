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
	 * @type Faker
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
	 */
	public function seed($table)
	{
		$timer = microtime(true);
		$this->command->info('Seeding '.$table);
		$this->call($table.'TableSeeder');

		// Log results
		$results = Str::singular($table);
		$results = $this->container['arrounded']->qualifyModel($results);
		if (class_exists($results)) {
			$timer = round(microtime(true) - $timer, 2);
			$this->command->comment(sprintf('-- %s entries created (%ss)', $results::count(), $timer));
		}
	}

	/**
	 * Insert items by chunks
	 *
	 * @param string       $table
	 * @param array        $items
	 * @param integer|null $chunks
	 *
	 * @return boolean
	 */
	public function insertChunked($table, $items, $chunks = null)
	{
		// Insert directly if less than chunks
		if (count($items) < $chunks) {
			return DB::table($table)->insert($items);
		}

		// Enforce SQLite limitations
		if (DB::getDriverName() == 'sqlite') {
			$chunks = count(head($items)) ?: 10;
			$chunks = floor(999 / $chunks);
		}

		// Chunk entries
		$results = [];
		$slices  = $chunks ? array_chunk($items, $chunks) : array($items);
		$this->progressIterator($slices, function ($items) use ($table, $results) {
			$results[] = DB::table($table)->insert($items);
		});

		return count(array_filter($results)) == count($slices);
	}

	/**
	 * Print progress on an iterator
	 *
	 * @param array $items
	 */
	public function progressIterator($items, Closure $closure)
	{
		$output     = $this->command->getOutput();
		$iterations = count($items);

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
}
