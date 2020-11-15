<?php
class Model_Abstract extends \Model_Crud
{
    public static $_user = array();
    protected static $_rules = array();
    protected static $_labels = array();

    public static function forge(array $data = array(), $region = null)
    {
        static::$_user = \Session::get('user');
        return new static($data);
    }

    public function validate($data)
    {
        $vars = $this->pre_validate($data);
        return $this->post_validate($this->run_validation($vars));
    }

    public function getValidation()
    {
        return $this->_validation;
    }

    public function setLabel()
    {
        foreach (static::$_headers as $key => $value) {
            static::$_labels[$key] = $value;
        }
    }

    protected function getNextId(string $table)
    {
        $id = DB::query($this->nextId)->param('table', \DB::expr($table))->execute()->as_array();
        return $id[0];
    }

    protected function getSeq($sequence)
    {
        $sql =
<<<SQL
    SELECT nextval(:sequence) seq;
SQL;
        $id = DB::query($sql)->param('sequence', $sequence)->execute()->as_array();
        return $id[0]['seq'];
    }

private $nextId =
<<<SQL
    SELECT count(*) + 1 FROM :table;
SQL;

    protected function decodeImage(array $problem): array
    {
        $problem['img'] = \Func::decodeJson($problem['images'])['img'][0];
        $problem['imgs'] = \Func::decodeJson($problem['images'])['img'];
        return $problem;
    }
}