<?php
namespace Arrounded\Repositories;

use Arrounded\Abstracts\AbstractRepository;
use Arrounded\Abstracts\Models\AbstractUploadModel;
use Arrounded\Interfaces\IllustrableInterface;

/**
 * Repository for the AbstractUploadModel resource.
 */
class UploadsRepository extends AbstractRepository
{
    /**
     * Build a new UploadsRepository.
     *
     * @param \Arrounded\Abstracts\Models\AbstractUploadModel $items
     */
    public function __construct(AbstractUploadModel $items)
    {
        $this->items = $items;
    }

    /**
     * Bind an unique image type to a model.
     *
     * @param \Arrounded\Abstracts\Models\AbstractUploadModel[]|\Arrounded\Abstracts\Models\AbstractUploadModel $uploads
     * @param IllustrableInterface                                                                              $model
     * @param array                                                                                             $attributes
     *
     * @return \Arrounded\Abstracts\Models\AbstractUploadModel
     */
    public function bindUniqueTo($uploads, IllustrableInterface $model, $attributes = [])
    {
        $model->files()->where($attributes)->delete();

        return $this->bindTo($uploads, $model, $attributes);
    }

    /**
     * Bind an AbstractUploadModel to a model.
     *
     * @param \Arrounded\Abstracts\Models\AbstractUploadModel[]|AbstractUploadModel $uploads
     * @param IllustrableInterface                                                  $model
     * @param array                                                                 $attributes
     *
     * @return \Arrounded\Abstracts\Models\AbstractUploadModel|\Arrounded\Abstracts\Models\AbstractUploadModel[]
     */
    public function bindTo($uploads, IllustrableInterface $model, $attributes = [])
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
        if (!$uploads instanceof AbstractUploadModel) {
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
     * Bind temporary images to a model.
     *
     * @param IllustrableInterface $model
     * @param int                  $hash
     * @param string|null          $type
     *
     * @return array
     */
    public function bindTemporaryTo(IllustrableInterface $model, $hash, $type = null)
    {
        $query  = $this->getTemporaryQuery($hash, $type);
        $images = $query->update([
            'illustrable_type' => $model->getClass(),
            'illustrable_id'   => $model->id,
        ]);

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
        $query = $this->items()->where([
            'illustrable_type' => $this->getModelInstance()->getNamespace().'\Models\Temporary',
            'illustrable_id'   => $hash,
        ]);

        if ($type) {
            $query->where('type', $type);
        }

        return $query;
    }
}
