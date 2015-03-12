<?php
namespace Arrounded\Traits;

use Illuminate\Support\Facades\HTML;

/**
 * A model with uploads.
 */
trait Illustrable
{
    //////////////////////////////////////////////////////////////////////
    //////////////////////////// RELATIONSHIPS ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the model's images.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->files()->whereImages();
    }

    /**
     * Get one of the model's files.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function file()
    {
        return $this->morphOne($this->getUploadClass(), 'illustrable')->orderBy('file_file_name', 'ASC');
    }

    /**
     * Get the model's files.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function files()
    {
        return $this->morphMany($this->getUploadClass(), 'illustrable')->orderBy('file_file_name', 'ASC');
    }

    /**
     * Get the model's thumbnail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function thumb()
    {
        return $this->morphOne($this->getUploadClass(), 'illustrable')->whereImages();
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// THUMBNAILS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the model's thumb or its parent.
     *
     * @param string $parent
     *
     * @return \Arrounded\Abstracts\Models\AbstractUploadModel
     */
    public function parentableThumb($parent)
    {
        if (!$this->thumb) {
            // Use parent thumb if it exists.
            return ($this->$parent && $this->$parent->thumb) ? $this->$parent->thumb : null;
        }

        return $this->thumb;
    }

    /**
     * Renders the thumbnail of the model.
     *
     * @param string|null $size
     * @param array       $attributes
     *
     * @return string
     */
    public function thumbnail($size = null, $attributes = [])
    {
        if (!$this->thumb) {
            $upload = $this->getUploadClass();
            $upload = $upload::getPlaceholder($this->getClassBasename());

            return HTML::image($upload);
        }

        return $this->thumb->render($size, $attributes);
    }

    /**
     * Get the correct upload class.
     *
     * @return string
     */
    public function getUploadClass()
    {
        return $this->getNamespace().'\Models\Upload';
    }
}
