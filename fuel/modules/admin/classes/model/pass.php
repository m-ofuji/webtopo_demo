<?php
namespace Admin;

class Model_Pass extends \Model_Abstract
{
    protected static $_tabel_name = 'users';
    protected static $_primary_key = 'id';
    protected static $_rules = array(
        'pass' => 'required|trim|min_length[6]|max_length[20]',
        'pass_confirmation' => 'required|trim|min_length[6]|max_length[20]'
    );

    protected static $_labels = array(
        'pass' => 'パスワード',
        'pass_confirmation' => 'パスワード（確認）'
    );

    public function validate($data)
    {
        if (array_key_exists('pass', $data)) {
            $rule = static::$_rules;
            $rule['pass_confirmation'] .= '|match_password['.$data['pass'].']';
            static::$_rules = $rule;
        }
        return parent::validate($data);
    }

    public function register(array $input): bool
    {
        try{
            if (!$input['pass']) return false;

            $hashPass = \Auth::hash_password($input['pass']);
            $sql = $this->update;
            $updDate = \Date::forge()->get_timestamp();

            $query = \DB::query($sql);
            $query->param('password'     , $hashPass);
            $query->param('updated_at'   , $updDate);
            $query->param('id'           , $input['id']);
            
            $query->execute();

            \Logger::Write('update', implode('/', \Uri::segments()), 'reset: id ='.$input['id']);
            return true;
        } catch (Exception $e) {
            \Log::warning($e->getMessage());
            return false;
        }
    }

    private $update =
    <<<SQL
UPDATE users
SET
    password = :password
    ,updated_at = :updated_at
WHERE
    id = :id
SQL;

}