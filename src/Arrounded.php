<?php
namespace Arrounded;

use Arrounded\Abstracts\AbstractRepository;
use Arrounded\Abstracts\Models\AbstractModel;
use Arrounded\Traits\UsesContainer;
use Illuminate\Support\Str;

class Arrounded
{
    use UsesContainer;

    /**
     * The application's namespace
     *
     * @type string
     */
    protected $namespace;

    /**
     * Where to find the various namespaces
     *
     * @type array
     */
    protected $namespaces = [];

    /**
     * A cache of found instances
     *
     * @type array
     */
    protected $cached = [];

    //////////////////////////////////////////////////////////////////////
    ///////////////////////// GETTERS AND SETTERS ////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $namespace
     */
    public function setRootNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        // Add default structure
        $this->setRootNamespace($namespace);
        $this->setModelsNamespace('Entities');

        $this->addNamespace('Composers', 'Http');
        $this->addNamespace('Controllers', 'Http');
        $this->addNamespace('Forms', 'Http');
    }

    /**
     * @param string $modelsNamespace
     */
    public function setModelsNamespace($modelsNamespace)
    {
        $this->addNamespace('Models', $modelsNamespace);
        $this->addNamespace('Observers', $modelsNamespace);
        $this->addNamespace('Presenters', $modelsNamespace);
        $this->addNamespace('Repositories', $modelsNamespace);
        $this->addNamespace('Traits', $modelsNamespace);
        $this->addNamespace('Transformers', $modelsNamespace);
    }

    /**
     * @param string $namespace
     * @param string $position
     */
    public function addNamespace($namespace, $position)
    {
        $this->namespaces[$namespace] = $position;
    }

    /**
     * @param array $namespaces
     */
    public function setNamespaces(array $namespaces)
    {
        $this->namespaces = $namespaces;
    }

    /**
     * @param string|null $namespace
     *
     * @return string
     */
    public function getNamespace($namespace = null)
    {
        if (!$namespace) {
            return $this->namespace;
        }

        $namespace = array_get($this->namespaces, $namespace);
        $namespace = $this->namespace.'\\'.$namespace;

        return trim($namespace, '\\');
    }

    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// REFLECTION /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get a model service
     *
     * @param string            $model
     * @param string            $type
     * @param string|array|null $defaults
     *
     * @return string
     */
    public function getModelService($model, $type, $defaults = null)
    {
        // Check for cached instance
        $model = is_object($model) ? $model->getClassBasename() : $model;
        $hash  = $model.$type;
        if (array_key_exists($hash, $this->cached)) {
            return $this->cached[$hash];
        }

        // Look into possible namespaces
        $singularType = Str::singular($type);
        $pluralType   = Str::plural($type);
        $pluralModel  = Str::plural($model);

        $service = $this->getFirstExistingClass(array(
            $this->qualifyClass($model, $type),
            $this->qualifyClass($pluralModel, $type),
            $this->qualifyClass($model.$pluralType, $type),
            $this->qualifyClass($pluralModel.$pluralType, $type),
            $this->qualifyClass($model.$singularType, $type),
            $this->qualifyClass($pluralModel.$singularType, $type),
            $this->qualifyClass($model.$singularType, $pluralType),
            $this->qualifyClass($pluralModel.$singularType, $pluralType),
        ));

        // Switch to default if not found
        if (!class_exists($service) && $defaults) {
            $service = $this->getFirstExistingClass($defaults);
        }

        // Cancel if the class doesn't exist
        $this->cached[$hash] = $service;
        if (!class_exists($service)) {
            return;
        }

        return $service;
    }

    /**
     * Build a model service
     *
     * @param string            $model
     * @param string            $type
     * @param string|array|null $defaults
     *
     * @return object
     */
    public function buildModelService($model, $type, $defaults = null)
    {
        $service = $this->getModelService($model, $type, $defaults);
        if (!$service) {
            return;
        }

        return $this->app->make($service);
    }

    /**
     * @param AbstractModel|string $model
     *
     * @return AbstractRepository
     */
    public function getRepository($model)
    {
        $model = str_replace('Repository', null, $model);
        $model = Str::plural($model);

        return $this->buildModelService($model, 'Repository');
    }

    /**
     * @param AbstractModel|string $model
     *
     * @return string
     */
    public function getController($model)
    {
        return $this->getModelService($model, 'Controller');
    }

    /**
     * Find the fully qualified name of a model by its short name
     *
     * @param string $name
     *
     * @return string|null
     */
    public function qualifyModel($name)
    {
        $name = trim($name, '\\');
        $name = ucfirst($name);

        // Look into default path
        $default = $this->qualifyClass($name, 'Models');
        if (class_exists($default)) {
            return $default;
        }

        $repository = $this->getRepository($name);

        return $repository ? $repository->getModel() : null;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// FOLDERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string|null $folder
     *
     * @return string
     */
    public function getFolder($folder = null)
    {
        return $this->getNamespaceFolder($this->getNamespace($folder), $folder);
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Qualify a class by its type
     *
     * @param string      $class
     * @param string|null $type
     *
     * @return string
     */
    public function qualifyClass($class, $type = null)
    {
        $path = [$this->namespace];

        // Compute type
        $parent = array_get($this->namespaces, $type);
        $path[] = is_string($parent) ? $parent : null;
        $path[] = $type ?: null;

        // Compose FQN
        $path[] = $class;
        $path   = array_filter($path);

        return implode('\\', $path);
    }

    /**
     * Get the folder matching a namespace
     *
     * @param string|string[] $namespaces
     * @param string|null     $folder
     *
     * @return string
     */
    public function getNamespaceFolder($namespaces, $folder = null)
    {
        $namespaces = (array) $namespaces;
        $folders    = [];
        foreach ($namespaces as $key => $namespace) {
            $folder    = $folder ? $namespace.'\\'.$folder : $namespace;
            $folder    = str_replace('\\', DIRECTORY_SEPARATOR, $folder);
            $folders[] = app_path($folder);
        }

        $folders = array_filter($folders, [$this->files, 'isDirectory']);

        return head($folders);
    }

    /**
     * Get the first existing class in an array
     *
     * @param string[] $classes
     *
     * @return string
     */
    public function getFirstExistingClass($classes)
    {
        $classes = (array) $classes;
        $classes = array_filter($classes, 'class_exists');

        return head($classes);
    }
}
