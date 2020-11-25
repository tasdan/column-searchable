<?php

namespace Tasdan\ColumnSearchable;

trait Searchable
{
    public function scopeSearchable($query)
    {
        //validate searchable array
        if (isset($this->searchable) &&
            is_array($this->searchable) &&
            !empty($this->searchable)
        ) {
            foreach ($this->searchable as $searchableFieldName => $searchableFieldValue) {
                if (is_numeric($searchableFieldName)) {
                    //only column names are set
                    $fieldName = $searchableFieldValue;
                    $relationName = 'self';
                    $propertyType = 'string';
                    $columnNames = $searchableFieldValue;
                } else {
                    $fieldName = $searchableFieldName;
                    $relationName = isset($searchableFieldValue['relation']) &&
                    !empty($searchableFieldValue['relation'])
                        ? $searchableFieldValue['relation'] :
                        'self';
                    $propertyType = isset($searchableFieldValue['type']) &&
                        !empty($searchableFieldValue['type'])
                        ? $searchableFieldValue['type'] :
                        'string';
                    $columnNames =  isset($searchableFieldValue['columns']) &&
                    !empty($searchableFieldValue['columns'])
                        ? $searchableFieldValue['columns'] :
                        $searchableFieldName;
                }

                if (isset($_REQUEST[$fieldName])) {
                    $propertyValue = $_REQUEST[$fieldName];
                    if ($relationName == 'self') {
                        //search in table
                        if (is_array($columnNames)) {
                            //multiple search
                            $query->where(function ($subQuery) use ($columnNames, $propertyType, $propertyValue) {
                                foreach ($columnNames as $columnIndex => $columnName) {
                                    if ($propertyType == 'int') {
                                        if ($columnIndex === array_key_first($columnNames)) {
                                            $subQuery->where($columnName, $propertyValue);
                                        } else {
                                            $subQuery->orWhere($columnName, $propertyValue);
                                        }
                                    } else {
                                        if ($columnIndex === array_key_first($columnNames)) {
                                            $subQuery->where($columnName, 'LIKE', '%' . $propertyValue . '%');
                                        } else {
                                            $subQuery->orWhere($columnName, 'LIKE', '%' . $propertyValue . '%');
                                        }
                                    }
                                }
                            });
                        } else {
                            //single search
                            if ($propertyType == 'int') {
                                $query->where($columnNames, $propertyValue);
                            } else {
                                $query->where($columnNames, 'LIKE', '%' . $propertyValue . '%');
                            }
                        }
                    } else {
                        //search in joined table
                        if (is_array($columnNames)) {
                            //multiple search in a joined table
                            $query->whereHas(
                                $relationName,
                                function ($subQuery) use ($columnNames, $propertyValue, $propertyType) {
                                    foreach ($columnNames as $columnIndex => $columnName) {
                                        if ($propertyType == 'int') {
                                            if ($columnIndex === array_key_first($columnNames)) {
                                                $subQuery->where($columnName, $propertyValue);
                                            } else {
                                                $subQuery->orWhere($columnName, $propertyValue);
                                            }
                                        } else {
                                            if ($columnIndex === array_key_first($columnNames)) {
                                                $subQuery->where($columnName, 'LIKE', '%' . $propertyValue . '%');
                                            } else {
                                                $subQuery->orWhere($columnName, 'LIKE', '%' . $propertyValue . '%');
                                            }
                                        }
                                    }
                                }
                            );
                        } else {
                            //single search in joined table
                            $query->whereHas(
                                $relationName,
                                function ($subQuery) use ($columnNames, $propertyValue, $propertyType) {
                                    if ($propertyType == 'int') {
                                        $subQuery->where($columnNames, $propertyValue);
                                    } else {
                                        $subQuery->where($columnNames, 'LIKE', '%' . $propertyValue . '%');
                                    }
                                }
                            );
                        }
                    }
                }

                //todo add polymorphic many-to-many relation type
                //add range type
            }
        }
    }
}
