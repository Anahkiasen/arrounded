<?php
namespace Fakable\Relations;

use Fakable\Abstracts\AbstractRelationSeeder;

class BelongsTo extends AbstractRelationSeeder
{
	/**
	 * Affect a model's attributes
	 *
	 * @param array $attributes
	 * @param array $models
	 *
	 * @return array
	 */
	public function affectAttributes(array $attributes, array $models = array())
	{
		if ($this->getKind() == 'morphTo') {
			$pivot = str_replace('_id', '', $this->foreignKey());
			$model = $this->fakable->getFaker()->randomElement($models);

			$attributes[$pivot.'_type'] = $model;
			$attributes[$pivot.'_id']   = $this->fakable->randomModel($model);
		} else {
			$attributes[$this->getForeignKey()] = $this->fakable->randomModel($this->getParent());
		}

		return $attributes;
	}
}