<?php
namespace Arrounded\Seeders;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ForeignKeysMigration extends Migration
{
	/**
	 * A list of foreign keys to add and remove
	 *
	 * @var array
	 */
	protected $foreignKeys = array();

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$this->setForeigns($this->foreignKeys);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$this->dropForeigns($this->foreignKeys);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////// SET FOREIGN KEYS ///////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Set multiple foreign keys on a table
	 *
	 * @param array|string $tables
	 * @param array        $keys
	 */
	public function setForeigns($tables, $keys = array())
	{
		// Recursive call
		if (is_array($tables)) {
			foreach ($tables as $table => $keys) {
				$this->setForeigns($table, $keys);
			}
			return;
		}

		$keys = (array) $keys;
		if (!app()->environment('testing')) {
			echo '-- Adding foreign keys for [' .implode(',', $keys). '] to '.$tables.PHP_EOL;
		}

		Schema::table($tables, function (Blueprint $table) use ($keys) {
			foreach ($keys as $key) {
				$this->setForeign($table, $key);
			}
		});
	}

	/**
	 * Set foreign key on a table
	 *
	 * @param Blueprint $table
	 * @param string    $otherTable
	 *
	 * @return void
	 */
	public function setForeign(&$table, $otherTable)
	{
		$plural = Str::plural($otherTable);

		$table
			->foreign($otherTable.'_id')
			->references('id')->on($plural)
			->onDelete('cascade');
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////// REMOVE FOREIGN KEYS //////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Drop foreign keys on multiple tables
	 *
	 * @param array|string $tables
	 * @param array        $keys
	 *
	 * @return void
	 */
	public function dropForeigns($tables, $keys = array())
	{
		// Recursive call
		if (is_array($tables)) {
			foreach ($tables as $table => $keys) {
				$this->dropForeigns($table, $keys);
			}
			return;
		}

		$keys = (array) $keys;
		Schema::table($tables, function (Blueprint $table) use ($keys) {
			foreach ($keys as $key) {
				$this->dropForeign($table, $key);
			}
		});
	}

	/**
	 * Drop a foreign key on a table
	 *
	 * @param Blueprint $table
	 * @param string    $otherTable
	 *
	 * @return void
	 */
	public function dropForeign(&$table, $otherTable)
	{
		$table->dropForeign($table->getTable().'_'.$otherTable.'_id_foreign');
	}
}
