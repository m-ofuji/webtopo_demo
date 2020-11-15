<?
class Csrf
{
    public static function check($value)
    {
        $csrf_old_token = \Session::get_flash(\Config::get('security.csrf_token_key'));
        return $value === $csrf_old_token;
    }

    // token チェック
    public static function check4next($value)
    {
        $csrf_old_token = \Session::get_flash(\Config::get('security.csrf_token_key'));
        if ($value === $csrf_old_token){
            \Session::set_flash(\Config::get('security.csrf_token_key'), $value);
            return true;
        } else {
            return false;
        }
    }

    // token 発行
    public static function fetch()
    {
        $token = \Security::fetch_token();
        \Session::set_flash(\Config::get('security.csrf_token_key'), $token);
        return $token;
    }
}