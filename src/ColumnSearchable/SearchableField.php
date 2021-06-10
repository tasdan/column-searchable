<?php

namespace Tasdan\ColumnSearchable;

use Tasdan\ColumnSearchable\Exceptions\ColumnSearchableFieldException;

class SearchableField
{

    public static function render(array $parameters)
    {
        //todo az exception-ök nem jók
        //check first parameter and save parameter data
        if (isset($parameters[0]) && $parameters[0] == 'text') {
            $type = $parameters[0];
            $otherParameters = isset($parameters[3]) ? $parameters[3] : null;
        } elseif (isset($parameters[0]) && $parameters[0] == 'select') {
            $type = $parameters[0];

            if (isset($parameters[3]) && !empty($parameters[3])) {
                $selectOptions = [];
                foreach ($parameters[3] as $value => $text) {
                    $selectOptions[$value]['text'] = $text;
                }
            } else {
                throw new ColumnSearchableFieldException('', 0);
            }

            $otherParameters = isset($parameters[4]) ? $parameters[4] : null;
        } else {
            throw new ColumnSearchableFieldException('', 0);
        }

        //check second parameter (column name)
        if (isset($parameters[1])) {
            $name = $parameters[1];
        } else {
            throw new ColumnSearchableFieldException('', 0);
        }

        //check placeholder
        $placeholder = isset($parameters[2]) ? $parameters[2] : $name;

        //create other parameter array, and add searchable-input class
        $otherParams = [];
        if (isset($otherParameters['class'])) {
            $otherParameters['class'] .= ' searchable-input';
        } else {
            $otherParameters['class'] = 'searchable-input';
        }
        foreach ($otherParameters as $otherKey => $otherValue) {
            $otherParams[] = $otherKey . '="' . $otherValue . '"';
        }



        //get value to input, if exists
        if (isset($_REQUEST[$name]) && $_REQUEST[$name] != '') {
            switch ($type) {
                case 'text':
                    $value = $_REQUEST[$name];
                    break;
                case 'select':
                    $value = $_REQUEST[$name];
                    $selectOptions[$value]['selected'] = true;
                    break;
            }
        } else {
            $value = '';
        }

        //if select, create select options array, with selected value
        if ($type == 'select') {
            $select = [];
            $select[] = '<option value="">'.trans(config('columnsearchable.translations.show_all')).'</option>';
            foreach ($selectOptions as $value => $optionArray) {
                if (isset($optionArray['selected'])) {
                    $selectedText = 'selected';
                } else {
                    $selectedText = '';
                }
                $select[] = '<option 
                    value="' . $value . '" 
                    '. $selectedText .' 
                    >' . $optionArray['text'] . '</option>';
            }
        }

        switch ($type) {
            case 'text':
                return '<input 
                    type="text" 
                    name="'.$name.'" 
                    id="'.$name.'" 
                    '. implode(' ', $otherParams) . ' 
                    value="'.$value.'" 
                    placeholder="'.$placeholder.'">';
            case 'select':
                return '<select 
                    name="'.$name.'" 
                    id="'.$name.'" 
                    '. implode(' ', $otherParams) . ' 
                    >
                        '.implode("\n", $select).'
                    </select>';
        }
    }
}
