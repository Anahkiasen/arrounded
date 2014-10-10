<?php
namespace Arrounded\Interfaces;

use Arrounded\Traits\Upload;

/**
 * A model with uploads
 */
interface IllustrableInterface
{
	/**
	 * Get the model's images
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function images();

	/**
	 * Get one of the model's files
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphOne
	 */
	public function file();

	/**
	 * Get the model's files
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function files();

	/**
	 * Get the model's thumbnail
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphOne
	 */
	public function thumb();

	/**
	 * Get the model's thumb or its parent
	 *
	 * @param string $parent
	 *
	 * @return Upload
	 */
	public function parentableThumb($parent);

	/**
	 * Renders the thumbnail of the model
	 *
	 * @param string|null $size
	 *
	 * @return string
	 */
	public function thumbnail($size = null);
}
