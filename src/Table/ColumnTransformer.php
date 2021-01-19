<?php
/**
 * Created by PhpStorm.
 * User: rashed
 * Date: 03-Jul-17
 * Time: 3:01 PM
 */

namespace Rashidul\Hailstorm\Table;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ColumnTransformer
{

    protected $helper;

    /**
     * ColumnTransformer constructor.
     */
    public function __construct()
    {

    }

    public function string($model, $field)
    {
        $row_data = '';
        if ($model->{$field}){
            $row_data = ucwords( $model->{$field} );
        }
        return $row_data;
    }

    public function detailsLink($model, $field, $value)
    {
        $row_data = '';
        if ($model->{$field}){
            $field_value = $model->{$field};
            $row_data = "<a href='{$model->getShowUrl()}'>{$field_value}</a>";
        }
        return $row_data;
    }

    public function exact($model, $field, $value)
    {
        if ($model->{$field}){
            return $model->{$field};
        }
        return '';
    }

    public function enum($model, $field, $value)
    {
        $enumOptionsArray = $value['options'];

        if ($model->{$field}){
            $option = $model->getOriginal($field);

            $html = $enumOptionsArray[$option];
            if (isset($value['labels']))
            {
                $configLabels = config('raindrops.crud.labels');
                $labelName = $value['labels'][$model->{$field}];
                $labelHtml = (new \Rashidul\Hailstorm\Html\Helper())->elementFromSyntax($configLabels[$labelName]);
                $html = $labelHtml->text($html)->render();
            }

            return $html;
        }



        return '';
    }

    public function url($model, $field, $value)
    {
        if ($model->{$field}){
            $field_value = $model->{$field};
            return "<a href='{$field_value}' target='_blank'>{$field_value}</a>";
        }

        return '';
    }

    public function phoneNumber($model, $field, $value)
    {
        if ($model->{$field}){
            $field_value = $model->{$field};
            return "<a href='tel://{$field_value}'>{$field_value}</a>";
        }

        return '';
    }

    public function email($model, $field, $value)
    {
        if ($model->{$field}){
            $field_value = $model->{$field};
            return "<a href='mailto:{$field_value}'>{$field_value}</a>";
        }

        return '';
    }

    public function image($model, $field, $value)
    {
        if (!$model->{$field}) return '';

        $path = isset($value['path']) ? $value['path'] : $field;
        $disk = isset($value['disk'])
            ? $value['disk']
            : config('raindrops.crud.disk');

        $disk = config('filesystems.disks.' . $disk);

        $classes = isset($value['classes']) ? $value['classes'] : '';

        $filename = $model->{$field};
        $url = url( $disk['url'] . '/' . $path .  '/' . $filename);

        return sprintf('<img class="%s" src="%s" alt="%s">', $classes, $url, $value['label']);

    }

    public function checkbox($model, $field, $value)
    {
        $pos = 'Yes';
        $neg = 'No';
        if (isset($value['options']))
        {
            $pos = $value['options'][0];
            $neg = $value['options'][1];
        }

        $html = $model->{$field} ? $pos : $neg;

        if (isset($value['labels']))
        {
            $configLabels = config('raindrops.crud.labels');
            $labelName = $value['labels'][$model->{$field}];
            $labelHtml = (new \Rashidul\Hailstorm\Html\Helper())->elementFromSyntax($configLabels[$labelName]);
            $html = $labelHtml->text($html)->render();
        }

        return $html;
    }

    public function relation($model, $field, $value, $helper)
    {

        $html = '';
        $relatedModel = null;
        $columnName = '';

        /*if ( !$model->{$field} )
        {
            return $html;
        }*/

        if (isset($value['options']))
        {
            $relatedModel = $model->{$value['options'][0]};
            $columnName = $value['options'][1];
        }

        if (isset($value['show']))
        {
            $relatedModel = $model->{$value['show'][0]};
            $columnName = $value['show'][1];
        }

        if ( $relatedModel == null || !($relatedModel instanceof Model))
        {
            return $html;
        }

        // we first check if there's a fields array defined in this related model, if it is
        // then we show it according to the configuration of that fields array,
        if (method_exists($relatedModel, 'getFields') && $relatedModel->getFields() != null)
        {
            $relatedModelFields = $relatedModel->getFields();
            $type = $helper->getDataType($relatedModelFields[$columnName]);
            $html = $helper->get($relatedModel, $columnName, $relatedModelFields[$columnName], $type);
        }
        else
        {
            // otherwise just return the field's value directly
            $html = $relatedModel->{$columnName};
        }

        if (isset($value['linkable']) && $value['linkable'])
        {
            $html = sprintf('<a href="%s">%s</a>', $relatedModel->getShowUrl(), $html);
        }

        return $html;

        /*if ($model->{$field})
        {
            $relatedModel = $model->{$value['options'][0]};
            // TODO.
            // 1. check if returned related model is actually a subclass of eloquent
            // 2. handle relationship more than 2 levels
            if ($relatedModel)
            {

                return $relatedModel->{$value['options'][1]};
                /*array_shift($showArray); // remove the first element of the array
                foreach ($showArray as $item) {
                    $row_data .= $relatedModel->{$item} . ' ';
                }*//*

            }

        }
        elseif (isset($value['show']))
        {
            $relatedModel = $model->{$value['show'][0]};

            if ($relatedModel && $relatedModel instanceof Model)
            {
                // linkable
                if (isset($value['linkable']) && $value['linkable'])
                {
                    return sprintf('<a href="%s">%s</a>', $relatedModel->getShowUrl(), $relatedModel->{$value['show'][1]});
                }
                // we first check if there's a fields array defined in this related model, if it is
                // then we show it according to the configuration of that fields array,
                if (method_exists($relatedModel, 'getFields') && $relatedModel->getFields() != null)
                {
                    $relatedModelFields = $relatedModel->getFields();
                    return $this->helper->get($relatedModel, $value['show'][1], $relatedModelFields[$value['show'][1]]);
                }
                else
                {
                    // otherwise just return the field's value directly
                    return $relatedModel->{$value['show'][1]};
                }

            }
        }
        else
        {
            return '';
        }

        return '';*/

    }

    public function currency($model, $field, $value)
    {
        if ($model->{$field}){
            return $model->{$field};
        }
        return '';
    }

    public function time($model, $field, $options)
    {
        if ($model->{$field}){
            $format = $this->getFormat($options, 'time');
            $time = $this->getCarbonObjFromDateTime($model, $field, $options);
            return $time->format($format);
        }
        return '';
    }

    public function date($model, $field, $options)
    {
        if ($model->{$field}){
            $format = $this->getFormat($options, 'date');
            $time = $this->getCarbonObjFromDateTime($model, $field, $options);
            return $time->format($format);
        }
        return '';
    }

    public function datetime($model, $field, $options)
    {
        if ($model->{$field}){
            $format = $this->getFormat($options, 'datetime');
            $time = $this->getCarbonObjFromDateTime($model, $field, $options);
            return $time->format($format);
        }
        return '';
    }

    public function timestamp($model, $field, $options)
    {
        if ($model->{$field}){
            $format = $this->getFormat($options, 'datetime');
            $time = $this->getCarbonObjFromDateTime($model, $field, $options);
            return $time->format($format);
        }
        return '';
    }


    private function getFormat($value, $field_type)
    {
        return isset($value['format']) ? $value['format'] : config('raindrops.crud.datetime_formats.' . $field_type);
    }


    private function getCarbonObjFromDateTime($model, $field, $options)
    {

        if (isset($options['db_format'])){
            $format = $options['db_format'];
        } else {
            switch ($options['type']){
                case 'time':
                    $format = 'H:i:s';
                    break;

                case 'date':
                    $format = 'Y-m-d';
                    break;

                case 'datetime':
                case 'timestamp':
                    $format = 'Y-m-d H:i:s';
                    break;

                default:
                    $format = 'Y-m-d H:i:s';
            }
        }

        return Carbon::createFromFormat($format, $model->getOriginal($field));
    }

    public function relationMany($model, $field, $value)
    {
        $html = '';

        $relatedModels = $model->{$value['options'][0]};

        if ($relatedModels->count())
        {
            $relatedModels->each(function ($item) use (&$html, $value){
                $html .= ' ' . $item->{$value['options'][1]} . ',';
            });
        }

        return rtrim($html, ',');
    }

}
