<?php


class ArrayValidator
{
	// ************** system method
    /**
     * @param $attribute
     * @param $options
     * @param $default
     *
     * @return mixed
     * todo add other params as placeholders
     */
	public static function handleMessage($attribute, array $options, $default) {
		if(!isset($options['message'])) {
			$options['message'] = $default;
		}

		return str_replace('%attribute%', $attribute, $options['message']);
	}
	// ************** system method

    /**
     * @param       $data
     * @param       $attribute
     * @param array $options
     *
     * @return bool|mixed
     */
	public static function required($data, $attribute,array $options = array()) {
		if(
			isset($data[$attribute]) && strlen(trim($data[$attribute]))>0
		) {
			return true;
		}

		return self::handleMessage($attribute, $options, 'Field %attribute% is required');
	}

    /**
     * @param       $data
     * @param       $attribute
     * @param array $options
     *
     * @return bool|mixed
     * @throws Exception
     */
	public static function regexp($data, $attribute,array $options = array()) {
		$value = $data[$attribute];

        if(!isset($options['pattern'])) {
            throw new Exception('You must specify pattern to check with!');
        }

		if(preg_match($options['pattern'], $value) === 1) {
			return true;
		}

		return self::handleMessage($attribute, $options, 'Field %attribute% not match regexp '.$options['pattern']);
	}

    /**
     * @param       $data
     * @param       $attribute
     * @param array $options
     *
     * @return bool|mixed
     * @throws Exception
     */
	public static function in($data, $attribute, array $options = array()) {
		$value = $data[$attribute];

        if(!isset($options['range'])) {
            throw new Exception('You must specify data range to check in!');
        }

		if(in_array($value, $options['range'])) {
			return true;
		}

		return self::handleMessage($attribute, $options, 'Field %attribute% not in range '.implode(',', $options['range']));
	}

    /**
     * @param       $data
     * @param       $attribute
     * @param array $options
     *
     * @return bool|mixed
     */
	public static function integer($data, $attribute, array $options = array()) {
		$value = $data[$attribute];

        // allow here only 2 keys
        $options = array_intersect_key($options, array('min_range' => '', 'max_range' => ''));

		if(filter_var($value, FILTER_VALIDATE_INT, array('options' => $options)) !== false) {
			return true;
		}

		return self::handleMessage($attribute, $options, 'Field %attribute% not a valid integer');
	}

    /**
     * @param       $data
     * @param       $attribute
     * @param array $options
     *
     * @return bool|mixed
     */
	public static function bool($data, $attribute, array $options = array()) {
		$value = $data[$attribute];

		if(filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null) {
			return true;
		}

		return self::handleMessage($attribute, $options, 'Field %attribute% not a valid boolean');
	}

    /**
     * @param       $data
     * @param       $attribute
     * @param array $options
     *
     * @return bool|mixed
     */
    public static function email($data, $attribute,array $options = array()) {
    	$value = $data[$attribute];

    	if(filter_var($value, FILTER_VALIDATE_EMAIL) !== false) {
			return true;
		}

		return self::handleMessage($attribute, $options, 'Field %attribute% not a valid email');
    }

    /**
     * @param       $data
     * @param       $attribute
     * @param array $options
     *
     * @return bool|mixed
     */
	public static function length($data, $attribute,array $options = array()) {
    	$value = $data[$attribute];
    	$fn = 'strlen';
    	if(is_array($value)) {
    		$fn = 'count';
    	}

    	if(!isset($options['min_length'])) {
			$options['min_length'] = 0;
    	}

		if(!isset($options['max_length'])) {
			$options['max_length'] = PHP_INT_MAX;
    	}

		$toReturn = true;
    	$toReturn = $toReturn && call_user_func($fn, $value) > $options['min_length'];
    	$toReturn = $toReturn && call_user_func($fn, $value) < $options['max_length'];

		if($toReturn) {
			return true;
		}

		return self::handleMessage($attribute, $options, 'Field %attribute% has invalid length');
    }

    /**
     * @param       $data
     * @param       $attribute
     * @param array $options
     * @param       $scenario
     *
     * @return array|bool
     * @throws Exception
     */
    public static function nested($data, $attribute, array $options = array(), $scenario = '') {
    	// array of nested data
    	$value = $data[$attribute];
    	$errors = array();
    	foreach ($value as $i => $singleData) {
    		$res = self::validate($options['model'], $singleData, $scenario, $data);

    		if($res === true) {
    			continue;
    		}

    		$errors[$i] = $res;
    	}

    	if(count($errors) > 0) {
    		return $errors;
    	}

    	return true;
    }

    /**
     * @param array  $definedModel
     * @param array  $inputDataArray
     * @param string $scenario
     * @param array  $parentDataArr
     *
     * @return array|bool
     * @throws Exception
     */
    public static function validate(array $definedModel, array $inputDataArray, $scenario = '', $parentDataArr = array()) {
        if(!isset($definedModel['fields'])) {
            throw new Exception('Model must contain field `key` with fields array!');
        }

        if(!isset($definedModel['rules'])) {
            throw new Exception('Model must contain field `rules` with list of rules!');
        }

        $errors = array();

        // here we have only model fields or safe attributes only
        $inputDataArray = array_intersect_key($inputDataArray, array_flip($definedModel['fields']));

        if(count($inputDataArray) < 1) {
            throw new Exception('No fields to validate. Please check model and input data fields!');
        }

        // here we handle each rule in list
        foreach ($definedModel['rules'] as $rule) {
            // we don't handle any other scenarios
            if(isset($rule['on']) && $rule['on'] !== $scenario) {
                continue;
            }

            if(isset($rule['when'])) {
                if(is_string($rule['when'])) {
                    // we use $data and $parent for string expressions
                    // after some refactor we can lost names
                    $data = $inputDataArray;
                    $parent = $parentDataArr;
                    if(eval("return ".$rule['when'].';') !== true) {
                        continue;
                    }
                } elseif (is_callable($rule['when'])) {
                    if(call_user_func_array($rule['when'],array($inputDataArray, $parentDataArr)) !== true) {
                        continue;
                    }
                }
            }

            if(!isset($rule['options'])) {
                $rule['options'] = array();
            }

            // make fields arrays
            if(!is_array($rule[0])) {
                $rule[0] = array($rule[0]);
            }

            $fields = $rule[0];

            $validatorCb = $rule[1];
            foreach ($fields as $field) {
                $res = call_user_func_array($validatorCb, array(
                        $inputDataArray,
                        $field,
                        $rule['options'],
                        $scenario
                    )
                );

                if($res !== true)  {
                    $errors[$field][] = $res;
                }
            }
        }

        if(count($errors) > 0) {
            return $errors;
        }

        return true;
    }
}