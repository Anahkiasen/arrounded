<?php
namespace Arrounded\Commands;

use Illuminate\Console\Command;

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
		if (!$this->hasBackups()) {
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
		if ($this->hasBackups()) {
			$this->call('db:backup', ['filename' => $dump]);
		}
	}

	/**
	 * Migrate some third party packages
	 */
	protected function migrateThirdParty()
	{
		// ...
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
		$this->seedDatabase();
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

	/**
	 * @param $migrations
	 *
	 * @return array|string
	 */
	protected function hashFolder($migrations)
	{
		$migrations = app_path($migrations.'/*');
		$migrations = $this->laravel['files']->glob($migrations);
		$migrations = array_map('file_get_contents', $migrations);
		$migrations = md5(implode($migrations));

		return $migrations;
	}

	/**
	 * Call the various seeders
	 */
	protected function seedDatabase()
	{
		$this->call('db:seed', ['-vvv' => null]);
	}

	/**
	 * @return boolean
	 */
	protected function hasBackups()
	{
		$provider = 'Schickling\Backup\BackupServiceProvider';
		$provided = $this->laravel->getLoadedProviders();

		return class_exists($provider) and array_get($provided, $provider);
	}
}

