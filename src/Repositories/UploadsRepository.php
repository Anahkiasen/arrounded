<?php
namespace Arrounded\Repositories;

use Arrounded\Abstracts\AbstractModel;
use Arrounded\Abstracts\AbstractRepository;
use Arrounded\Models\Upload;

/**
 * Repository for the Upload resource
 */
class UploadsRepository extends AbstractRepository
{
	/**
	 * Build a new UploadsRepository
	 *
	 * @param Upload $items
	 */
	public function __construct(Upload $items)
	{
		$this->items = $items;
	}

	/**
	 * Bind an unique image type to a model
	 *
	 * @param Upload[]|Upload $uploads
	 * @param AbstractModel   $model
	 * @param array           $attributes
	 *
	 * @return Upload
	 */
	public function bindUniqueTo($uploads, AbstractModel $model, $attributes = array())
	{
		$model->files()->where($attributes)->delete();

		return $this->bindTo($uploads, $model, $attributes);
	}

	/**
	 * Bind an Upload to a model
	 *
	 * @param Upload[]|Upload $uploads
	 * @param AbstractModel   $model
	 * @param array           $attributes
	 *
	 * @return Upload|Upload[]
	 */
	public function bindTo($uploads, AbstractModel $model, $attributes = array())
	{
		// Recursive call
		if (is_array($uploads)) {
			$uploads = array_filter($uploads);
			$results = [];
			foreach ($uploads as $upload) {
				$results[] = $this->bindTo($upload, $model, $attributes);
			}

			return $results;
		}

		// If we passed a string or UploadedFile, etc.
		if (!$uploads instanceof Upload) {
			$attributes = array_merge($attributes, ['file' => $uploads]);
			$uploads    = $this->instance($attributes);
		}

		// Bind to model and save
		$uploads->illustrable_type = $model->getClass();
		$uploads->illustrable_id   = $model->getKey();
		$uploads->type             = array_get($attributes, 'type', null);

		$uploads->save();

		// Recompile thumbnails
		$uploads->reprocessStyles();

		return $uploads;
	}

	/**
	 * Bind temporary images to a model
	 *
	 * @param AbstractModel $model
	 * @param integer       $hash
	 * @param  string|null  $type
	 *
	 * @return array
	 */
	public function bindTemporaryTo(AbstractModel $model, $hash, $type = null)
	{
		$query = $this->getTemporaryQuery($hash, $type);

		$images = $query->update(array(
			'illustrable_type' => $model->getClass(),
			'illustrable_id'   => $model->id,
		));

		return $images;
	}

	/**
	 * Find all uploads for a temporary hash.
	 *
	 * @param string      $hash
	 * @param string|null $type
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function findForTemporary($hash, $type = null)
	{
		return $this->getTemporaryQuery($hash, $type)->get();
	}

	//////////////////////////////////////////////////////////////////////
	////////////////////////////// HELPERS ///////////////////////////////
	//////////////////////////////////////////////////////////////////////

	/**
	 * @param string $hash
	 * @param string $type
	 *
	 * @return mixed
	 */
	protected function getTemporaryQuery($hash, $type)
	{
		$query = $this->items()->where(array(
			'illustrable_type' => $this->getModelInstance()->getNamespace().'\Models\Temporary',
			'illustrable_id'   => $hash,
		));

		if ($type) {
			$query->where('type', $type);
		}

		return $query;
	}
}
