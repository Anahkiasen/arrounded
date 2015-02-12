<?php
namespace Arrounded\Traits\Reflection;

use Arrounded\Facades\Arrounded;
use Illuminate\Support\Facades\HTML;
use Illuminate\Support\Facades\URL;

trait RoutableModel
{
    /**
     * Get the controller matching the model
     *
     * @return string
     */
    public function getController()
    {
        return Arrounded::getController($this);
    }

    /**
     * Get an action from the model's controller
     *
     * @param string  $action
     * @param boolean $api
     *
     * @return string
     */
    public function getAction($action, $api = false)
    {
        $prefix = $this->getController().'@';
        if ($api) {
            $prefix = str_replace('Controllers', 'Controllers\Api', $prefix);
        }

        return $prefix.$action;
    }

    /**
     * Get the path to an action
     *
     * @param string  $action
     * @param boolean $api
     *
     * @return string
     */
    public function getPath($action, $api = false)
    {
        return URL::action($this->getAction($action, $api), $this->getIdentifier());
    }

    /**
     * Get the link to an action
     *
     * @param string      $action
     * @param string|null $title
     * @param array       $attributes
     *
     * @return string
     */
    public function getLink($action, $title = null, array $attributes = array())
    {
        $title = $title ?: $this->name;

        return HTML::linkAction($this->getAction($action), $title, $this->getIdentifier(), $attributes);
    }
}
