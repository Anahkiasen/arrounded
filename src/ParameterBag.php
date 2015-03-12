<?php
namespace Arrounded;

class ParameterBag extends \Symfony\Component\HttpFoundation\ParameterBag
{
    /**
     * Get only certain attributes.
     *
     * @param string[]|string $keys,...
     *
     * @return array
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return array_only($this->parameters, $keys);
    }
}
