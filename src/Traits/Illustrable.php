<?php
namespace Arrounded\Traits;

/**
 * A model with uploads
 */
trait Illustrable
{
	//////////////////////////////////////////////////////////////////////
	//////////////////////////// RELATIONSHIPS ///////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Get the model's images
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function images()
	{
		return $this->files()->whereImages();
	}

	/**
	 * Get one of the model's files
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function file()
	{
		return $this->morphOne($this->getUploadClass(), 'illustrable')->orderBy('file_file_name', 'ASC');
	}

	/**
	 * Get the model's files
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function files()
	{
		return $this->morphMany($this->getUploadClass(), 'illustrable')->orderBy('file_file_name', 'ASC');
	}

	/**
	 * Get the model's thumbnail
	 *
	 * @return mixed|null
	 */
	public function thumb()
	{
		return $this->morphOne($this->getUploadClass(), 'illustrable')->whereImages();
	}

	//////////////////////////////////////////////////////////////////////
	///////////////////////////// THUMBNAILS /////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * Renders the thumbnail of the model
	 *
	 * @param string|null $size
	 *
	 * @return string
	 */
	public function thumbnail($size = null)
	{
		if (!$this->thumb) {
			$upload = $this->getUploadClass();

			return $upload::getPlaceholder($this->getClassBasename());
		}

		return $this->thumb->render($size);
	}

	/**
	 * Get the correct upload class
	 *
	 * @return string
	 */
	protected function getUploadClass()
	{
		return $this->getNamespace().'\Models\Upload';
	}
}
