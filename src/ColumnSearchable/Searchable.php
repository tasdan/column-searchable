<?php

namespace Tasdan\ColumnSearchable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Searchable Trait
 * @package Tasdan\ColumnSearchable
 */
trait Searchable
{
    /**
     * Store the current model searchable parameters
     * @var array
     */
    private $searchableParameters = [];

    /**
     * Searchable Scope base
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     * @throws \Tasdan\ColumnSearchable\Exceptions\ColumnSearchableException
     */
    public function scopeSearchable($query)
    {
        if ($this->validateSearchableArray()) {
            $this->parseSearchableArray();
        } else {
            $this->createSearchableArrayFromRequest();
        }

        return $this->querySearchableBuilder($query);
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     *
     * @throws ColumnSearchableException
     */
    private function querySearchableBuilder($query)
    {

        foreach($this->searchableParameters as $searchableParameter) {
            if ( request()->input($searchableParameter['parameter_name'])) {

                $parmaterValue = request()->input($searchableParameter['parameter_name']);

                if ($searchableParameter['relation_name'] == 'self') {
                    $query = $this->addSimpleConditionToQuery($query,
                        $parmaterValue,
                        $searchableParameter['column_names'],
                        $searchableParameter['property_type'],
                    );
                } else {
                    $query = $this->addRelatedConditionToQuery($query,
                        $parmaterValue,
                        $searchableParameter['relation_name'],
                        $searchableParameter['column_names'],
                        $searchableParameter['property_type'],
                    );
                }
            }
        }

        return $query;
    }

    /**
     * Validate the model's private searchable array
     *
     * @return boolean
     */
    private function validateSearchableArray()
    {
        if (isset($this->searchable) &&
            is_array($this->searchable) &&
            !empty($this->searchable)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Parse model's searchable parameter array, fill the omitted values, with default
     *
     */
    private function parseSearchableArray()
    {
        foreach ($this->searchable as $searchableParameterName => $searchableParameterValues) {
            if (is_numeric($searchableParameterName)) {
                //only column names are set
                $parameterName = $searchableParameterValues;
                $relationName = 'self';
                $propertyType = 'string';
                $columnNames = $searchableParameterValues;
            } else {
                $parameterName = $searchableParameterName;
                $relationName = isset($searchableParameterValues['relation']) &&
                !empty($searchableParameterValues['relation'])
                    ? $searchableParameterValues['relation'] :
                    'self';
                $propertyType = isset($searchableParameterValues['type']) &&
                !empty($searchableParameterValues['type'])
                    ? $searchableParameterValues['type'] :
                    'string';
                $columnNames =  isset($searchableParameterValues['columns']) &&
                !empty($searchableParameterValues['columns'])
                    ? $searchableParameterValues['columns'] :
                    $searchableParameterValues;
            }

            $this->searchableParameters[] = [
                'parameter_name' => $parameterName,
                'relation_name' => $relationName,
                'property_type' => $propertyType,
                'column_names' => $columnNames
            ];
        }
    }

    /**
     * Create searchable parameter array, when model hasn't this array
     *
     */
    private function createSearchableArrayFromRequest()
    {
        $requestParameters = request()->all();
        foreach ($requestParameters as $columnName => $requestParameterValue) {
            if ($this->chekcColumnExists($columnName)) {
                $this->searchableParameters[] = [
                    'parameter_name' => $columnName,
                    'relation_name' => 'self',
                    'property_type' => 'string',
                    'column_names' => $columnName
                ];
            }
        }
    }

    /**
     * Check if column name are exists in current model
     *
     * @param string $column
     * @return bool
     */
    private function chekcColumnExists($column)
    {
        return Schema::connection($this->getConnectionName())->hasColumn($this->getTable(), $column);
    }

    /**
     * @param \Illuminate\Database\Query\Builder    $query
     * @param string|int                            $parmaterValue
     * @param string|array                          $columnNames
     * @param string                                $propertyType
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function addSimpleConditionToQuery($query, $parmaterValue, $columnNames, $propertyType)
    {
        if (is_array($columnNames)) {
            //multiple search
            $query->where(function ($subQuery) use ($columnNames, $propertyType, $parmaterValue) {
                foreach ($columnNames as $columnIndex => $columnName) {
                    if ($propertyType == 'int') {
                        if ($columnIndex === array_key_first($columnNames)) {
                            $subQuery->where($columnName, $parmaterValue);
                        } else {
                            $subQuery->orWhere($columnName, $parmaterValue);
                        }
                    } else {
                        if ($columnIndex === array_key_first($columnNames)) {
                            $subQuery->where($columnName, 'LIKE', '%' . $parmaterValue . '%');
                        } else {
                            $subQuery->orWhere($columnName, 'LIKE', '%' . $parmaterValue . '%');
                        }
                    }
                }
            });
        } else {
            //single search
            if ($propertyType == 'int') {
                $query->where($columnNames, $parmaterValue);
            } else {
                $query->where($columnNames, 'LIKE', '%' . $parmaterValue . '%');
            }
        }

        return $query;
    }

    /**
     * @param \Illuminate\Database\Query\Builder    $query
     * @param string|int                            $parmaterValue
     * @param string                                $relationName
     * @param string|array                          $columnNames
     * @param string                                $propertyType
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function addRelatedConditionToQuery($query, $parmaterValue, $relationName, $columnNames, $propertyType)
    {
        //todo $relation = $query->getRelation($relationName);
        //todo check $relation -> instanceof HasOne, BelongsTo, etc... {

        //search in joined table
        if (is_array($columnNames)) {
            //multiple search in a joined table
            $query->whereHas(
                $relationName,
                function ($subQuery) use ($columnNames, $parmaterValue, $propertyType) {
                    foreach ($columnNames as $columnIndex => $columnName) {
                        if ($propertyType == 'int') {
                            if ($columnIndex === array_key_first($columnNames)) {
                                $subQuery->where($columnName, $parmaterValue);
                            } else {
                                $subQuery->orWhere($columnName, $parmaterValue);
                            }
                        } else {
                            if ($columnIndex === array_key_first($columnNames)) {
                                $subQuery->where($columnName, 'LIKE', '%' . $parmaterValue . '%');
                            } else {
                                $subQuery->orWhere($columnName, 'LIKE', '%' . $parmaterValue . '%');
                            }
                        }
                    }
                }
            );
        } else {
            //single search in joined table
            $query->whereHas(
                $relationName,
                function ($subQuery) use ($columnNames, $parmaterValue, $propertyType) {
                    if ($propertyType == 'int') {
                        $subQuery->where($columnNames, $parmaterValue);
                    } else {
                        $subQuery->where($columnNames, 'LIKE', '%' . $parmaterValue . '%');
                    }
                }
            );
        }

        return $query;
    }
}
