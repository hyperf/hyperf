<?php
namespace Hyperf\Apidog\Validation;

use Hyperf\Di\Annotation\Inject;

class Validation implements ValidationInterface
{

    /**
     * @Inject()
     * @var \Hyperf\Logger\LoggerFactory
     */
    public $logger;
    public $data = [];
    public $errors = [];

    public function check(array $rules, array $data, $obj = null, $key_tree = null)
    {
        $this->data = $data;
        $this->errors = [];
        $final_data = [];
        foreach ($rules as $field => $rule) {
            $field_name_label = explode('|', $field);
            $field_name = $field_name_label[0];
            $tree = $key_tree ? $key_tree . '.' . $field_name : $field_name;
            if (is_array($rule)) {
                //todo 索引数组的验证
                $ret = $this->check($rule, array_get_node($field_name, $data, []), $obj, $tree);
                if ($ret === false) {
                    return false;
                }
                $final_data[$field_name] = $ret;
                continue;
            }
            $field_label = $field_name_label[1] ?? '';
            $field_value = array_get_node($field_name, $data);
            $constraints = explode('|', $rule);
            $is_required = in_array('required', $constraints);
            if (!$is_required && is_null($field_value)) {
                continue;
            }
            foreach ($constraints as $constraint) {
                preg_match('/\[(.*)\]/', $constraint, $m);
                $func = preg_replace('/\[.*\]/', '', $constraint);
                $option = $m[1] ?? null;
                $func_rule = 'rule_' . $func;
                if (method_exists($this, $func_rule)) {
                    $check = call_user_func_array([
                        $this,
                        $func_rule,
                    ], [$field_value, $option]);
                    if ($check && !isset($final_data[$field_name])) {
                        $final_data[$field_name] = $field_value;
                    }
                    $this->log()->info(sprintf('validation key:%s rule:%s result:%s', $field_name, $func_rule, $check ? 'true' : 'false'));
                }
                $func_filter = 'filter_' . $func;
                if (method_exists($this, $func_filter)) {
                    $filter_value = call_user_func_array([
                        $this,
                        $func_filter,
                    ], [$field_value, $option]);
                    $this->log()->info(sprintf('validation key:%s filter:%s result:%s', $field_name, $func_filter, $filter_value));
                    $final_data[$field_name] = $filter_value;
                }
                $customMethod = str_replace('cb_', '', $func);
                if (strpos($func, 'cb_') !== false && method_exists($obj, $customMethod)) {
                    $check = $obj->$customMethod($field_value, $option);
                    if ($check === true) {
                        $final_data[$field_name] = $field_value;
                    } else {
                        $this->errors[] = $check;
                    }
                    $this->log()->info(sprintf('validation key:%s cb:%s result:%s', $field_name, $customMethod, $check === true ? 'true' : 'false'));
                }
                if ($this->errors) {
                    $label = $field_label ? $field_label . '(' . $tree . ')' : $tree;
                    foreach ($this->errors as $index => $each) {
                        $this->errors[$index] = sprintf($each, $label);
                    }

                    return false;
                }
            }
        }

        return $final_data;
    }

    public function filter_bool($val)
    {
        if (empty($val)
            || in_array(strtolower($val), [
                'false',
                'null',
                'nil',
                'none',
            ])) {

            return false;
        }

        return true;
    }

    public function filter_int($val)
    {
        return (int)$val;
    }

    public function rule_any($val)
    {
        return true;
    }

    public function rule_required($val)
    {
        if ($val === '' || is_null($val)) {
            $this->errors[] = '%s为必填项';

            return false;
        }

        return true;
    }

    public function rule_uri($val)
    {
        if ($val === '') {
            return true;
        }
        // 构造url格式
        if (!preg_match('@^http@i', $val)) {
            $val = 'http://xxx.com/' . $val;
        }

        return $this->rule_url($val);
    }

    public function rule_url($val)
    {
        if ($val === '') {
            return true;
        }
        $pattern = "/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";
        if (!preg_match($pattern, $val)) {
            $this->errors[] = '%s不是合法的URL';

            return false;
        }

        return true;
    }

    public function rule_email($val)
    {
        if ($val === '') {
            return true;
        }
        if (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $val)) {
            $this->errors[] = '%s不是合法的Email地址';

            return false;
        }

        return true;
    }

    //包含注释语法的JSON
    public function rule_extended_json($val)
    {
        if ($val === '') {

            return true;
        }
        $j = is_json_str($val, true);
        if ($j) {
            $this->errors[] = '%s不是合法的JSON:' . $j;

            return false;
        }

        return true;
    }

    public function rule_json($val)
    {
        if ($val === '') {

            return true;
        }
        $j = is_json_str($val);
        if ($j) {
            $this->errors[] = '%s不是合法的JSON:' . $j;

            return false;
        }

        return true;
    }

    public function rule_date($val)
    {
        if ($val === '') {

            return true;
        }
        $ret = strtotime($val);
        if ($ret <= 0 || $ret === false || !preg_match('@^\d{4}-\d{2}-\d{2}$@', $val)) {
            $this->errors[] = '%s不是有效日期';

            return false;
        }

        return true;
    }

    public function rule_datetime($val)
    {
        if ($val === '') {

            return true;
        }
        $ret = strtotime($val);
        if ($ret <= 0 || $ret === false || !preg_match('@^\d{4}-\d{2}-\d{2}@', $val)) {
            $this->errors[] = '%s不是有效日期时间';

            return false;
        }

        return true;
    }

    public function rule_safe_password($val)
    {
        if ($val === '') {

            return true;
        }
        if (strlen($val) < 8) {
            $this->errors[] = '%s长度最少为8位';

            return false;
        }
        $level = 0;
        if (preg_match('@\d@', $val)) {
            $level++;
        }
        if (preg_match('@[a-z]@', $val)) {
            $level++;
        }
        if (preg_match('@[A-Z]@', $val)) {
            $level++;
        }
        if (preg_match('@[^0-9a-zA-Z]@', $val)) {
            $level++;
        }
        if ($level < 3) {
            $this->errors[] = '您设置的%s太简单，密码必须包含数字、大小写字母、其它符号中的三种及以上';

            return false;
        }

        return true;
    }

    public function rule_in($val, $list)
    {
        if ($val === '') {

            return true;
        }
        $ok = in_array($val, explode("\001", $list));
        if (!$ok) {
            $this->errors[] = '%s不是有效值';
        }

        return $ok;
    }

    function rule_max_width($val, $len)
    {
        if ($val === '') {

            return true;
        }
        $res = (mb_strlen($val) > $len) ? false : true;
        if (!$res) {
            $this->errors[] = "%s最大长度为{$len}";

            return false;
        }

        return $res;
    }

    public function rule_natural($val)
    {
        if ($val === '') {

            return true;
        }
        if (!preg_match('/^[0-9]+$/', $val)) {
            $this->errors[] = '%s不是合法的自然数';

            return false;
        }

        return true;
    }

    public function rule_int($val)
    {
        if ($val === '') {

            return true;
        }
        if (!filter_var($val, FILTER_VALIDATE_INT)) {
            $this->errors[] = '%s不是合法的整数';

            return false;
        }

        return true;
    }

    public function rule_array($val)
    {
        if (!is_array($val)) {
            $this->errors[] = '%s需是数组类型';

            return false;
        }

        return true;
    }

    public function rule_alpha($val)
    {
        if ($val === '') {

            return true;
        }
        if (!preg_match("/^([a-z])+$/i", $val)) {
            $this->errors[] = '%s仅能包含字母';

            return false;
        }

        return true;
    }

    public function rule_alpha_numeric($val)
    {
        if ($val === '') {

            return true;
        }
        if (!preg_match("/^([a-z0-9])+$/i", $val)) {
            $this->errors[] = '%s仅能包含字母和数字';

            return false;
        }

        return true;
    }

    public function rule_alpha_dash($val)
    {
        if ($val === '') {

            return true;
        }
        if (!preg_match("/^([-a-z0-9_-])+$/i", $val)) {
            $this->errors[] = '%s仅能包含字母、数字、_-';

            return false;
        }

        return true;
    }

    public function rule_numeric($val)
    {
        if ($val === '') {

            return true;
        }
        if (!is_numeric($val)) {
            $this->errors[] = '%s不是合法数字';

            return false;
        }

        return true;
    }

    public function rule_match($val, $match_field)
    {
        return $val == array_get_node($match_field, $this->data);
    }

    public function rule_mobile($val)
    {
        if ($val === '') {

            return true;
        }
        if (strlen($val) != 11) {
            $this->errors[] = '%s长度为11位';

            return false;
        }
        if (!preg_match('/^1\d{10}$/', (string)$val)) {
            $this->errors[] = '%s格式不正确';

            return false;
        }

        return true;
    }

    public function rule_gt($val, $n)
    {
        if ($val === '') {

            return true;
        }
        if (!$this->rule_numeric($val)) {

            return false;
        }
        if ($val <= $n) {
            $this->errors[] = '%s必须大于' . $n;

            return false;
        }

        return true;
    }

    public function rule_ge($val, $n)
    {
        if ($val === '') {

            return true;
        }
        if (!$this->rule_numeric($val)) {

            return false;
        }
        if ($val < $n) {
            $this->errors[] = '%s必须大于等于' . $n;

            return false;
        }

        return true;
    }

    public function rule_lt($val, $n)
    {
        if ($val === '') {

            return true;
        }
        if (!$this->rule_numeric($val)) {

            return false;
        }
        if ($val >= $n) {
            $this->errors[] = '%s必须小于' . $n;

            return false;
        }

        return true;
    }

    public function rule_le($val, $n)
    {
        if ($val === '') {

            return true;
        }
        if (!$this->rule_numeric($val)) {

            return false;
        }
        if ($val > $n) {
            $this->errors[] = '%s必须小于等于' . $n;

            return false;
        }

        return true;
    }

    public function rule_enum($val, $n)
    {
        if ($val === '') {

            return true;
        }
        if (!in_array($val, explode(',', $n))) {
            $this->errors[] = '%s必须是 ' . $n . ' 其中之一';

            return false;
        }

        return true;
    }

    public function getError()
    {
        return $this->errors;
    }

    public function log()
    {
        return $this->logger->get('validation');
    }
}