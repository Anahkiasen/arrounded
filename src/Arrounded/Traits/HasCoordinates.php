<?php
namespace Arrounded\Traits;

use Cache;
use DB;
use Illuminate\Support\Str;

/**
 * A model with coordinates
 */
trait HasCoordinates
{
	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Update the model's coordinates
	 *
	 * @param  string $attribute
	 * @param  mixed $value
	 *
	 * @return void
	 */
	protected function setAttributeWithCoordinates($attribute, $value)
	{
		// Set attributes
		$this->attributes[$attribute] = $value;

		// Update coordinates
		$this->updateCoordinates($this->address, $this->city, $this->state, $this->country);
	}

	/**
	 * Update the Model's coordinates from a list of components
	 *
	 * @return void
	 */
	protected function updateCoordinates()
	{
		$components  = func_get_args();
		$coordinates = $this->getCoordinates($components);

		$this->attributes['lat'] = $coordinates['lat'];
		$this->attributes['lng'] = $coordinates['lng'];
	}

	/**
	 * Get coordinates from an address
	 *
	 * @param  array $components
	 *
	 * @return array [lat, long]
	 */
	protected function getCoordinates($components)
	{
		$address = null;
		foreach ($components as $component) {

			// If the attribute is empty, skip to next one
			if (!$component) {
				continue;
			}

			// Add new component to address slug
			$address += trim(', '.$component, ', ');
			$slug     = Str::slug($address);

			// Try to get coordinates
			$coordinates = Cache::rememberForever($slug, function() use ($slug) {
				return $this->geocode('?sensor=false&address='.str_replace('-', '+', $slug));
			});

			// Return coordinates if address found
			// Else we add the next component
			if ($coordinates['lat'] !== 0) {
				return $coordinates;
			}
		}
	}

	/**
	 * Geocode the User
	 *
	 * @return array
	 */
	protected function geolocate()
	{
		return $this->geocode();
	}

	/**
	 * Call the Maps API
	 *
	 * @param  string $url
	 *
	 * @return array
	 */
	protected function geocode($url = null)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'http://maps.googleapis.com/maps/api/geocode/json'.$url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);

		$json = json_decode($response, true);
		return array_get($json, 'results.0.geometry.location', array(
			'lat' => 0,
			'lng' => 0,
		));
	}

	////////////////////////////////////////////////////////////////////
	///////////////////////////// QUERY SCOPES /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get results in a radius
	 *
	 * @param  Query   $query
	 * @param  integer $distance
	 *
	 * @return Query
	 */
	public function scopeRadius($query, $distance)
	{
		return $query
			->having('distance', '>', 0)
			->having('distance', '<=', $distance);
	}

	/**
	 * Add distance to results
	 *
	 * @param  Query $query
	 * @param  float $lat
	 * @param  float $lng
	 *
	 * @return Query
	 */
	public function scopeDistance($query, $lat, $lng = null)
	{
		// If no coordinates specified, fetch them
		if (!$lat) {
			$lat = $this->geolocate();
		}

		// If they're combined, extract them
		if (!$lng) {
			list($lat, $lng) = $lat;
		}

		return $query->select($this->getTable().'.*', DB::raw('(3959 * ACOS(COS(RADIANS(' .$lat. ')) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS(' .$lng. ')) + SIN(RADIANS(' .$lat. ')) * SIN(RADIANS(lat)))) AS `distance`'));
	}
}