<?php
namespace Arrounded\Services\Transformers;

use Arrounded\Abstracts\AbstractTransformer;

class DefaultTransformer extends AbstractTransformer
{
	/**
	 * Default transformation for an item
	 *
	 * @param $item
	 *
	 * @return array
	 */
	public function transform($item)
	{
		return $this->transformWithDefaults($item);
	}
}
