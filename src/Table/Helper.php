<?php
/**
 * Created by PhpStorm.
 * User: rashed
 * Date: 03-Jul-17
 * Time: 3:06 PM
 */

namespace Rashidul\Hailstorm\Table;

use Rashidul\Hailstorm\Table\ColumnTransformer;


class Helper
{
    protected $columnTransformer;

    /**
     * Helper constructor.
     * @internal param ColumnTransformer $columnTransformer
     * @internal param $columnGenerator
     */
    public function __construct()
    {
        $this->columnTransformer = new ColumnTransformer();
    }

    /**
     * @param $model
     * @param $field
     * @param $value
     * @param $type
     * @return string
     */
    public function get($model, $field, $value, $type)
    {
        $row_data = '';

        switch ($type){

            case 'string':
                $row_data = $this->columnTransformer->string($model, $field);
                break;

            case 'details_link':
                $row_data = $this->columnTransformer->detailsLink($model, $field, $value);
                break;

            case 'url':
                $row_data = $this->columnTransformer->url($model, $field, $value);
                break;

            case 'tel':
                $row_data = $this->columnTransformer->phoneNumber($model, $field, $value);
                break;

            case 'mailto':
                $row_data = $this->columnTransformer->email($model, $field, $value);
                break;

            case 'exact':
                $row_data = $this->columnTransformer->exact($model, $field, $value);
                break;

            case 'enum':
                $row_data = $this->columnTransformer->enum($model, $field, $value);
                break;

            case 'img':
                $row_data = $this->columnTransformer->image($model, $field, $value);
                break;

            case 'image':
                $row_data = $this->columnTransformer->image($model, $field, $value);
                break;

            case 'checkbox':
                $row_data = $this->columnTransformer->checkbox($model, $field, $value);
                break;

            case 'method':
                $row_data = $model->{$value['method']}();
                break;

            case 'currency':
                $row_data = $this->columnTransformer->currency($model, $field, $value);
                break;

            case 'time':
                $row_data = $this->columnTransformer->time($model, $field, $value);
                break;

            case 'datetime':
                $row_data = $this->columnTransformer->datetime($model, $field, $value);
                break;

            case 'timestamp':
                $row_data = $this->columnTransformer->timestamp($model, $field, $value);
                break;

            case 'date':
                $row_data = $this->columnTransformer->date($model, $field, $value);
                break;

            case 'email':
                $row_data = $this->columnTransformer->email($model, $field, $value);
                break;

            case 'relation':
                $row_data = $this->columnTransformer->relation($model, $field, $value, $this);
                break;

            case 'relation_many':
                $row_data = $this->columnTransformer->relationMany($model, $field, $value);
                break;


        }

        return $row_data;

    }

    public function getDataType($value)
    {
        if ( isset($value['show'] ) )
        {
            // return false if show set to false
            if ( !$value['show'] )
            {
                return false;
            }

            if (is_array($value['show'])){
                $type = count($value['show']) === 3 && $value['show'][2] === true ? 'relation-details' : 'relation';
            } else {
                $type = $value['show'];
            }

        } else {
            $type = $this->defaultDataTypeForField($value);
        }

        return $type;
    }

    /**
     * Predict default table row type if there's no `show`
     * attribute specified explicitly
     * @param $value
     * @return string
     */
    private function defaultDataTypeForField($value)
    {
        if (isset($value['type'])){

            switch($value['type']){
                case 'select' :
                    return 'enum';
                    break;

                case 'checkbox' :
                    return 'checkbox';
                    break;

                case 'email' :
                    return 'email';
                    break;

                case 'text' :
                case 'textarea' :
                case 'number' :
                    return 'exact';
                    break;

                default:
                    return $value['type'];
            }
        }

        return 'exact';
    }
}
