<?php
/**
 * Created by PhpStorm.
 * User: rashed
 * Date: 19-Jul-17
 * Time: 12:05 PM
 */

namespace Rashidul\Hailstorm\Form;

class Helper
{


    /**
     * build html <option> tags from a given collection of models
     * @param $collection
     * @param array $indices
     * @return string
     */
    public static function collectionToOptions($collection, $indices = [], $selected = null)
    {
        $values = $collection->toArray();

        $option_key = $indices[0];
        $options = '';
        foreach ($values as $value) {

            if (is_array($selected)){
                $isSelected = in_array($value[$option_key], $selected) ? 'selected' : '';
            } else {
                $isSelected = $value[$option_key] === $selected ? 'selected' : '';
            }
            $option_value = count($indices) > 2 ? $value[$indices[1]] . ' ' . $value[$indices[2]] : $value[$indices[1]];
            $options .= sprintf('<option value="%s" %s>%s</option>', $value[$option_key], $isSelected, $option_value);
        }

        return $options;
    }

}
