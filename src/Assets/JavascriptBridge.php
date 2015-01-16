<?php
namespace Arrounded\Assets;

use Illuminate\Support\Contracts\JsonableInterface;

class JavascriptBridge
{
    /**
     * And array of data to pass to Javascript
     *
     * @type array
     */
    protected static $data = array();

    /**
     * Add data to pass
     *
     * @param array $data
     */
    public static function add(array $data)
    {
        // Filter and merge data
        $data = array_filter($data, function ($value) {
            return !is_null($value);
        });
        $data = array_merge(static::$data, $data);

        static::$data = $data;
    }

    /**
     * Get the data
     *
     * @return array
     */
    public static function getData()
    {
        return static::$data;
    }

    /**
     * Render to JS
     *
     * @return string
     */
    public static function render()
    {
        $rendered = '';
        foreach (static::$data as $key => $value) {
            $encoded = $value instanceof JsonableInterface ? $value->toJson() : json_encode($value);
            $rendered .= sprintf("\tvar %s = %s;".PHP_EOL, $key, $encoded);
        }

        return $rendered;
    }
}
