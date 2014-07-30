<?php
namespace Arrounded\Commands;

use Illuminate\Console\Command;
use Schema;

class RemigrateCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'arrounded:remigrate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Remigrates the database.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		if (!class_exists('Schickling\Backup\BackupServiceProvider')) {
			return $this->remigrate();
		}

		$hash = $this->computeHash();
		$dump = storage_path('dumps/'.$hash);

		// If we already have a dump, use it
		if (file_exists($dump)) {
			return $this->call('db:restore', ['dump' => $hash]);
		}

		// Else remigrate database and back it up
		$this->remigrate();
		$this->call('db:backup', ['filename' => $dump]);
	}

	/**
	 * Migrate some third party packages
	 */
	protected function migrateThirdParty()
	{
		// ...
	}

	/**
	 * @param $migrations
	 *
	 * @return array|string
	 */
	protected function hashFolder($migrations)
	{
		$migrations = app_path($migrations.'/*');
		$migrations = $this->laravel['files']->glob($migrations);
		$migrations = array_map('filemtime', $migrations);
		$migrations = md5(implode($migrations));

		return $migrations;
	}

	/**
	 * Remigrate the database and back it up
	 */
	protected function remigrate()
	{
		// Create migrations table if necessary
		if (!Schema::hasTable('migrations')) {
			$this->call('migrate:install');
		}

		// Migrate database
		$this->call('migrate:reset');
		$this->call('migrate');
		$this->migrateThirdParty();

		// Call seeders
		$this->call('db:seed', ['-vvv' => null]);
	}

	/**
	 * @return string
	 */
	protected function computeHash()
	{
		$migrations = $this->hashFolder('database/migrations');
		$seeds      = $this->hashFolder('database/seeds');
		$hash       = $migrations.$seeds.'.sql';

		return $hash;
	}
}

