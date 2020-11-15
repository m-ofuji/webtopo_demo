<?php
class Validation extends Fuel\core\Validation
{
    // エラーメッセージを作成
    public static function getErrorMsg($val)
    {
        $res = array();
        foreach($val->error() as $key => $e) {
            $res[] = $e->get_message();
        }

        return $res;
    }

    public static function _validation_array($data)
    {
        $valid = true;
        foreach($data as $key => $val) {
            if ($val == '') {
                $valid = false;
                break;
            }
        }
        return $valid;
    }

    public static function _validation_match_password($pass_confirmation, $pass)
    {
        return $pass_confirmation === $pass;
    }
}