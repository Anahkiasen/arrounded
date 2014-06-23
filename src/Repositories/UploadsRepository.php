<?php
namespace Arrounded\Repositories;

use Arrounded\Abstracts\AbstractRepository;
use Arrounded\Models\Upload;

class UploadsRepository extends AbstractRepository
{
	/**
	 * Build a new UploadsRepository
	 *
	 * @param Container $app
	 * @param Image     $items
	 */
	public function __construct(Upload $items)
	{
		$this->items = $items;
	}

	/**
	 * Gather uploads for a model
	 *
	 * @param AbstractModel $model
	 * @param array         $images
	 * @param array         $attributes
	 *
	 * @return AbstractModel
	 */
	public function bindUploads($model, $images, array $attributes = array())
	{
		// Get the name of the folder to send the image to
		$images = is_array($images) ? $images : array($images);
		$folder = $this->getModelFolder($model);

		$images = array_filter($images);
		foreach ($images as $image) {

			// Create database entry
			$path     = $this->getImageFilepath($image, $folder);
			$basename = basename($path);

			$model->attachImage($basename, $attributes);

			// Upload the file
			$image->move(dirname($path), $basename);
		}

		return $model;
	}

	/**
	 * Get the final path to use for an image
	 *
	 * @param  SplFileInfo $image
	 *
	 * @return string
	 */
	public function getImageFilepath(SplFileInfo $image, $folder)
	{
		$path = public_path('uploads/'.$folder);

		// Get the original extension
		if ($pathinfo = $image->getExtension()) {
			$extension = $pathinfo;
		} elseif (method_exists($image, 'getClientOriginalExtension')) {
			$extension = $image->getClientOriginalExtension();
		} else {
			$extension = 'jpg';
		}

		// Concat name
		$name = md5($image->getBasename()).'.'.$extension;
		$name = $path.'/'.$name;

		return $name;
	}

	/**
	 * Get the folder to use for a model
	 *
	 * @param AbstractModel $model
	 *
	 * @return string
	 */
	public function getModelFolder($model)
	{
		return $model->getTable();
	}
}
