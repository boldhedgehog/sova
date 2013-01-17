<?php
/**
 *
 */

/**
 *
 * @author ISM-UKRAINE\a.yegorov
 *
 */
class choiceDatatype extends stringDatatype {
    protected $choices = array();
    
    public function getChoiceValue($key) {
        return isset($this->choices[$key]) ? $this->choices[$key] : NULL;
    }
}