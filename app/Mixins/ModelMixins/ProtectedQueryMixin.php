<?php

namespace App\Mixins\ModelMixins;

use App\Utility\Algorithms\StringAlgorithm;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait ProtectedQueryMixin
{
    private $paramFilters = '';
    private $uniqueTableList = [];

    /**
     * String separator
     * @param string $string
     * @param array
     */

    public function filterSeparator($string): array
    {
        $parts = explode(',', $string);
        $filters = [];
        foreach ($parts as $part) {
            $second_part = explode(':', $part);
            $filters[] = [
                'key' =>  $second_part[0],
                'value' => $second_part[1]
            ];
        }
        return $filters;
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    /**
     * @param string $string string
     * @param string $string startString
     * @return boolean
     */
    public function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    /**
     * @param string $string string
     * @param string $string startString
     * @return boolean
     */
    public function endsWith($string, $endString)
    {
        $len = strlen($endString);
        if ($len == 0) {
            return true;
        }
        return (substr($string, -$len) === $endString);
    }

    public function validatorChecker($_request, $v_fields)
    {
        $validator = Validator::make($_request, $v_fields);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()]);
        }
        return false;
    }

    public function getOne2ManyFieldIndex($relativeField)
    {
        if (property_exists($this, 'one2manyFields')) {
            foreach ($this->one2manyFields as $index => $field) {
                if ($field['associate_with'] == $relativeField) {
                    return $index;
                }
            }
        }
        return -1;
    }

    public function getOne2ManyFieldWithKey()
    {
        $with_fields = [];
        if (property_exists($this, 'one2manyFields')) {
            foreach ($this->one2manyFields as $index => $field) {
                if (isset($field['associate_with'])) {
                    $with_fields[] = $field['associate_with'];
                }
            }
        }
        return $with_fields;
    }


    /**
     * @param query $querySet
     * @return query
     */
    public function joinQuery($field, $model)
    {
        if (method_exists($model, 'relations')) {
            if (StringAlgorithm::firstPatternMatching($field, '__')) {
                $field_keys = explode('__', $field);
                $key = $field_keys[0];
                $field = $field_keys[1];
                foreach ($model::relations() as $relation) {
                    if ($key == $relation['key']) {
                        $field = (new $relation['model'])->getTable().'.'.$field;
                        return $field;
                    }
                }
            } else {
                return $this->getTableName().'.'.$field;
            }
        }
        return $this->getTableName().'.'.$field;
    }


    /**
     * @param query $querySet
     * @return query
     */
    public function joinTable($querySet)
    {
        if (method_exists($this, 'relations')) {
            foreach ($this::relations() as $relation) {
                $f_table_name = $relation['model']::getTableName();
                $b_table_name = $relation['base_model']::getTableName();
                $querySet->join($f_table_name, $f_table_name.'.'.$relation['related'], '=', $b_table_name.'.'.$relation['base_reation']);
            }
        }
        return $querySet;
    }

    protected function getTableNameFromRelationKey($key)
    {
        $tableData = explode('.', $key);
        if (method_exists($this, 'relations')) {
            foreach ($this::relations() as $relation) {
                if ($relation['key'] == $tableData[0]) {
                    $f_table_name = $relation['model']::getTableName();
                    return $f_table_name.'.'.$tableData[1];
                }
            }
        }
        return $key;
    }

    public function selectQueryField()
    {
        $_selectFields = [];
        $selectFields = $this->selectFields();
        foreach ($selectFields as $field) {
            if (StringAlgorithm::firstPatternMatching($field, '.')) {
                $_selectFields[] = $field;
            } else {
                $_selectFields[] = $this->tableName.'.'.$field;
            }
        }
        return $_selectFields;
    }

    public function appliedMullipleFilter($querySet, $filter)
    {
        $this->paramFilters = $filter;
        $queryFilters = $this->filterSeparator($this->paramFilters);
        if (method_exists($this, 'relations')) {
            foreach ($this::relations() as $relation) {
                $f_table_name = $relation['model']::getTableName();
                $b_table_name = $relation['base_model']::getTableName();

                if (!Str::contains($filter, $relation['key'])) {
                    continue;
                }

                $querySet->join(
                    $f_table_name,
                    $f_table_name.'.'.$relation['related'],
                    '=',
                    $b_table_name.'.'.$relation['base_reation']
                );
            }
        }

        foreach ($queryFilters as $qFilter) {
            if (strpos($qFilter['key'], '__b2n') !== false) {
                $keyMap = explode('__b2n', $qFilter['key']);
                $keyMap2 = explode('__date', $keyMap[0]);
                $key = $keyMap2[0];
                $valueMap = explode('~', $qFilter['value']);
                $key = $this->joinQuery($key, $this);
                $start = $valueMap[0];
                $end = $valueMap[1];

                if (strpos($keyMap[0], '__date') !== false) {
                    $start = date('Y-m-d', strtotime($start));
                    $end = date('Y-m-d', strtotime($end.'+1 days'));
                }
                $querySet->whereBetween($key, [$start, $end]);
            } elseif (StringAlgorithm::firstPatternMatching($qFilter['key'], '__gte')) {
                $keyMap = explode('__gte', $qFilter['key']);
                $key = $keyMap[0];
                $key = $this->joinQuery($key, $this);
                $querySet->where($key, '>=', $qFilter['value']);
            } elseif (StringAlgorithm::firstPatternMatching($qFilter['key'], '__lte')) {
                $keyMap = explode('__lte', $qFilter['key']);
                $key = $keyMap[0];
                $key = $this->joinQuery($key, $this);
                $querySet->where($key, '<=', $qFilter['value']);
            } elseif ($this->startsWith($qFilter['key'], 'like~')) {
                $len = strlen('like~');
                $key = substr($qFilter['key'], $len);
                $key = $this->joinQuery($key, $this);
                $searchData = strtolower($qFilter['value']);
                $querySet->whereRaw('lower('.$key.') like (?)', ["%{$searchData}%"]);
            } elseif ($this->startsWith($qFilter['key'], 'in~')) {
                $len = strlen('in~');
                $key = substr($qFilter['key'], $len);

                if (!Schema::hasColumn($this->getTable(), $key) && !preg_match('/__/', $key)) {
                    continue;
                }

                $key = $this->joinQuery($key, $this);
                $searchData = explode('--', $qFilter['value']);
                $querySet->whereIn($key, $searchData);
            } elseif ($this->startsWith($qFilter['key'], 'notin~')) {
                $len = strlen('notin~');
                $key = substr($qFilter['key'], $len);
                $key = $this->joinQuery($key, $this);
                $searchData = explode('--', $qFilter['value']);
                $querySet->whereNotIn($key, $searchData);
            } elseif ($this->startsWith($qFilter['key'], 'or~')) {
                $len = strlen('or~');
                $key = substr($qFilter['key'], $len);
                $key = $this->joinQuery($key, $this);
                $searchData = explode('--', $qFilter['value']);
                $querySet->orWhere($key, $searchData);
            } elseif ($this->startsWith($qFilter['key'], 'dte~')) {
                $len = strlen('dte~');
                $key = substr($qFilter['key'], $len);
                $key = $this->joinQuery($key, $this);
                $querySet->whereDate($key, $qFilter['value']);
            } elseif ($this->startsWith($qFilter['key'], '&|')) {
                $keyValue = explode('|', $qFilter['value']);
                $parentQuery = explode('=', $keyValue[0]);
                $subQuery = explode('=', $keyValue[1]);

                if (StringAlgorithm::firstPatternMatching($parentQuery[0], '__')) {
                    $p_t_key = explode('__', $parentQuery[0]);
                    $p_table = $p_t_key[0];
                    $p_key = $p_t_key[1];
                    $querySet->where(function ($query) use ($p_table, $p_key, $parentQuery, $subQuery) {
                        $query->where($p_table.'.'.$p_key, $parentQuery[1])
                            ->orWhere($subQuery[0], '=', $subQuery[1]);
                    });
                }
            } else {
                $key = $qFilter['key'];
                $key = $this->joinQuery($key, $this);

                if (!StringAlgorithm::firstPatternMatching($qFilter['key'], '__')) {
                    if (Schema::hasColumn($this->getTable(), $qFilter['key'])) {
                        $querySet->where($this->getTable().'.'.$qFilter['key'], $qFilter['value']);
                    }
                } else {
                    $querySet->where($key, $qFilter['value']);
                }
            }
        }

        return $querySet;
    }
}
