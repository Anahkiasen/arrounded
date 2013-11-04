<?php
namespace Arrounded\Traits;

use Upload;

/**
 * A model with uploads
 */
trait HasImages
{
	/**
	 * Get the model's images
	 *
	 * @return Collection
	 */
	public function images()
	{
		return $this->morphMany('Upload', 'illustrable');
	}

	/**
	 * Whether the model has images
	 *
	 * @return boolean
	 */
	public function getHasImagesAttribute()
	{
		return !$this->images->isEmpty();
	}

	/**
	 * Get the first image in the stack
	 *
	 * @return string
	 */
	public function getImageAttribute()
	{
		$image = $this->images->first();

		return $image ? $image : null;
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// SERVICES ///////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Attach an image to the model
	 *
	 * @param  string $name
	 * @param boolean $thumb
	 *
	 * @return Upload
	 */
	public function attachImage($name, $thumb = false)
	{
		$upload = new Upload(array(
			'name'  => basename($name),
			'thumb' => $thumb,
		));

		// Attach image to model
		$this->images()->save($upload);

		return $this->image;
	}
}
