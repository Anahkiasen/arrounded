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
		return $this->morphMany('Upload', 'illustrable')->orderBy('created_at', 'DESC');
	}

	/**
	 * Get all the images, filtered
	 *
	 * @return Collection
	 */
	public function filteredImages()
	{
		return $this->images->filter(function ($value) {
			return (bool) $value->path;
		});
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

		return $image && $image->path ? $image : $this->placeholderImage();
	}

	/**
	 * Return a placeholder image
	 *
	 * @return Image
	 */
	public function placeholderImage()
	{
		return new Upload;
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
	public function attachImage($name, $attributes = array())
	{
		// Create attributes array
		$attributes = array_merge(array(
			'name'  => $name,
		), $attributes);

		// Attach image to model
		$this->images()->create($attributes);
		unset($this->images);

		return $this->image;
	}
}
