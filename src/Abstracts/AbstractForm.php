<?php
namespace Arrounded\Abstracts;

use Arrounded\Abstracts\Models\AbstractModel;
use Arrounded\Validation\ValidationException;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;

/**
 * A class representation of a form
 */
abstract class AbstractForm
{
    /**
     * The validator factory
     *
     * @type Factory
     */
    protected $validator;

    /**
     * The validation rules
     *
     * @type array
     */
    protected $rules = [];

    /**
     * A model to fine-tune rules to
     *
     * @type AbstractModel
     */
    protected $model;

    /**
     * Build a new form
     *
     * @param Factory $validator
     */
    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validate an array of attributes
     *
     * @param array         $attributes
     * @param callable|null $callback
     *
     * @throws ValidationException
     * @return mixed
     */
    public function validate(array $attributes = array(), callable $callback = null)
    {
        // Get attributes and create Validator
        $validation = $this->validator->make($attributes, $this->getRules($attributes));

        // Alter rules and stuff
        $validation = $this->alterValidation($validation);

        if ($validation->fails()) {
            throw new ValidationException('Validation failed', $validation->getMessageBag());
        } elseif ($callback) {
            return $callback($attributes, $this->model);
        }

        return true;
    }

    /**
     * Validate an array of attributes for a particular model
     *
     * @param AbstractModel $model
     * @param array         $attributes
     * @param callable|null $callback
     *
     * @throws ValidationException
     * @return void
     */
    public function validateFor(AbstractModel $model, array $attributes = array(), callable $callback = null)
    {
        $this->model = $model;

        return $this->validate($attributes, $callback);
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////////// RULES ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Alter the rules on the Validator, etc
     *
     * @param Validator $validation
     *
     * @return Validator
     */
    protected function alterValidation(Validator $validation)
    {
        return $validation;
    }

    /**
     * Get the rules in use
     *
     * @param array $attributes
     *
     * @return array
     */
    public function getRules(array $attributes = array())
    {
        $rules = $this->rules;
        if (!$this->model) {
            return $rules;
        }

        // Replace placeholders in rules
        foreach ($rules as $key => $rule) {
            preg_match_all('/\{([a-z_]+)\}/', $rule, $matches);
            foreach ($matches[1] as $attribute) {
                $rule = str_replace('{'.$attribute.'}', $this->model->$attribute, $rule);
            }

            $rules[$key] = $rule;
        }

        return $rules;
    }
}
