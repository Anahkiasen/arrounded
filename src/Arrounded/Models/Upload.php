<?php
namespace Arrounded\Models;

use App;
use HTML;
use Illuminage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use URL;

class Upload extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'name',
		'illustrable_id',
		'illustrable_type',
	);

	/**
	 * Get the parent
	 *
	 * @return Model
	 */
	public function illustrable()
	{
		return $this->morphTo();
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// ATTRIBUTES ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the full path to the image
	 *
	 * @return string
	 */
	public function getPathAttribute()
	{
		$path = App::make('path.public').'/'.$this->getPath();
		if (!file_exists($path)) {
			return;
		}

		return $path;
	}

	/**
	 * Get the full URL to the file
	 *
	 * @return string
	 */
	public function getUrlAttribute()
	{
		return URL::asset($this->getPath());
	}

	/**
	 * Get a thumbnail of the picture
	 *
	 * @param  integer $width
	 * @param  integer $height
	 *
	 * @return Illuminage\Image
	 */
	public function thumb($width, $height = null)
	{
		if (!$this->path) {
			return null;
		}

		$height = $height ?: $width;
		$path   = URL::asset('');
		$path   = str_replace($path, null, $this->url);

		return Illuminage::thumb($path, $width, $height);
	}

	/**
	 * Outputs the Upload as an image tag
	 *
	 * @return string
	 */
	public function __toString()
	{
		return HTML::image($this->getPath());
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// QUERY SCOPES /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get only images by a specific owner
	 *
	 * @param  Query   $query
	 * @param  string  $model
	 * @param  integer $key
	 *
	 * @return Query
	 */
	public function scopeOwner($query, $model, $key)
	{
		return $query
			->where('illustrable_type', strtolower($model))
			->where('illustrable_id', $key);
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get the folder to the image
	 *
	 * @return string
	 */
	public function getPath()
	{
		if (!$this->illustrable_id) {
			return 'app/img/'.$this->name;
		}

		$folder = Str::plural($this->illustrable_type);

		return 'uploads/'.strtolower($folder).'/'.$this->name;
	}
}
