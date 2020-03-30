<?php
/**
 * 数据验证类
 */
namespace Org\Util;
class Validator
{
    private $_vals;
    private $_rules;
    private $_errs;
    private $_error_code;
    public function set_rule($val, $rule, $err_code = -999) {
        $this->_vals[]  = $val;
        $this->_rules[] = $rule;
        $this->_errs[]  = $err_code;
        return $this;
    }
    public function ErrorCode() {
        return $this->_error_code;
    }
    private function reset() {
        $this->_vals       = array();
        $this->_rules      = array();
        $this->_errs       = array();
    }
    public function run() {
        foreach ($this->_rules as $index => $rule) {
            $_errs   = $this->_errs[$index];
            $errs    = explode('|', $_errs);
            $methods = explode('|', $rule);
            if (!in_array('required', $methods) && ($this->_vals[$index] === false || $this->_vals[$index] === '' || $this->_vals[$index] === null))
                continue;
            foreach ($methods as $k => $method) {
                if (preg_match('/(.*?)\[(.*)\]/', $rule, $match)) {
                    $method = 'rule_' . $match[1];
                    $param  = $match[2];
                    if (method_exists($this, $method) && $this->$method($index, $param) === false) {
                        $this->_error_code = isset($errs[$k]) ? $errs[$k] : -999;
                        $this->reset();
                        return false;
                    }
                } else {
                    $method = 'rule_' . $method;
                    if (method_exists($this, $method) && $this->$method($index) === false) {
                        $this->_error_code = isset($errs[$k]) ? $errs[$k] : -999;
                        $this->reset();
                        return false;
                    }
                }
            }
        }
        $this->reset();
        return true;
    }
    private function rule_trim($index) {
        $this->_vals[$index] = trim($this->_vals[$index]);
    }
    private function rule_required($index) {
        return $this->_vals[$index] !== '' && $this->_vals[$index] !== false && $this->_vals[$index] !== null ? true : false;
    }
    private function rule_regex_match($index, $regex) {
        return (bool) preg_match($regex, $this->_vals[$index]);
    }
    private function rule_matches($index, $match_val) {
        return (bool) $this->_vals[$index] === $match_val;
    }
    private function rule_integer($index) {
        return (bool) preg_match('/^[\-+]?[0-9]+$/', $this->_vals[$index]);
    }
    private function rule_mobile($index) {
        return (bool) preg_match('/^1[3|4|5|7|8]\d{9}$/', $this->_vals[$index]);
    }

    private function rule_min_len($index, $val) {
        return strlen($this->_vals[$index]) >= $val;
    }
    private function rule_max_len($index, $val) {
        return strlen($this->_vals[$index]) <= $val;
    }
}