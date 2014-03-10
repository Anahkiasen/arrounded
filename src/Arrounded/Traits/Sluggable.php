<?php
namespace Arrounded\Traits;

use Illuminate\Support\Str;

/**
 * A model with synced slug
 */
trait Sluggable
{
	/**
	 * Sync the name attribute with the slug
	 *
	 * @param string $name
	 */
	public function setNameAttribute($name)
	{
		$this->attributes['name'] = $name;
		$this->attributes['slug'] = $this->generateSlug($name);
	}

	/**
	 * Get a prestored slug or generate one
	 *
	 * @return string
	 */
	public function getSlugAttribute()
	{
		return $this->getOriginal('slug') ?: $this->id;
	}

	/**
	 * Generate a slug from various components
	 *
	 * @param array|string $components
	 *
	 * @return string
	 */
	public function generateSlug($components)
	{
		// If we already generated a slug in the past
		// then keep that one
		if ($this->slug and !is_int($this->slug)) {
			return $this->slug;
		}

		// Join components
		$components = (array) $components;
		$components = implode('-', $components);

		// Sluggify the... well, slug
		$slug = Str::slug($components);
		$slug = Str::words($slug, 4);

		$slugs = static::lists('slug');

		// Add trailing number to slug if existing
		do {
			if ($exists = in_array($slug, $slugs)) {
				$slug .= '-'.Str::quickRandom(2);
			}
		} while ($exists);

		return $slug;
	}
}
