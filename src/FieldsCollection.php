<?php

namespace Rashidul\Hailstorm;


class FieldsCollection
{

    protected $fields = [];

    /**
     * FieldsCollection constructor.
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $this->expandFields($fields);
    }

    public function getFields()
    {
        return $this->fields;
    }


    // return only those fields which are present in form
    public function getFormFields()
    {
        $formFields = [];
        foreach ($this->fields as $field_name => $options){
            if (array_key_exists('form', $options) && !$options['form']){
                continue;
            }
            $formFields[$field_name] = $options;
        }
        return $formFields;
    }

    public function getValidationRules($item = null)
    {
        $rules = [];
        foreach ($this->fields as $field_name => $options){
            if (array_key_exists('validations', $options) && $options['validations'] != ''){
                $rule = $options['validations'];
                if ( str_contains($options['validations'], '{id}') ){
                    $replacer = $item ? ',' . $item->getKey() : '';
                    $rule = str_replace('{id}', $replacer, $rule);
                }
                $rules[$field_name] = $rule;
            }
        }
        return $rules;
    }

    public function getIndexFields()
    {
        $indexFields = [];
        foreach ($this->fields as $field_name => $options){
            if (array_key_exists('index', $options) && $options['index']){
                $indexFields[$field_name] = $options;
            }
        }
        return $indexFields;
    }

    // take fields array from controller & populate all the required options
    // for example, when putting just the field name, populate proper type & label
    private function expandFields(array $fields)
    {
        $new = [];

        foreach ($fields as $field_name => $options){
            if (is_string($options)) {
                // simple text field, $options is the name of the field
                $op['type'] = Constants::TYPE_TEXT;
                $op['label'] = $this->getLabel($options);
                // will be shown on datatable
                $op['index'] = true;
                $new[$options] = $op;
            }

            if (is_array($options)) {
                if (!array_key_exists('label', $options)) {
                    $options['label'] = $this->getLabel($field_name);
                }
                $new[$field_name] = $options;
            }
        }

        return $new;
    }

    private function getLabel($field, $options = [])
    {
        return $options['label'] ?? ucwords(str_replace("_", " ", $field));
    }


}
