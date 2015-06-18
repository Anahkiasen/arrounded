<?php

// Polyfill core framework functions
if (!defined('app_path')) {
    function app_path($path) {
        return __DIR__.'/../src/'.$path;
    }
}
