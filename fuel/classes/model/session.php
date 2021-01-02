<?php
class Model_Session extends Model_Abstract
{
    protected static $_tabel_name = 'problem_stock';
    protected static $_primary_key = 'id';

    public function getRecords(string $sort, string $order, array $where, int $offset, int $limit, bool $isMonthly): array
    {
        $result = \DB::select()
                    ->from(DB::expr($this->select))
                    ->where(\Querybuilder::where($where))
                    ->offset($offset)
                    ->limit($limit)
                    ->order_by($sort, $order)
                    ->execute()
                    ->as_array();
        return $result;
    }

    public function evaluate($id, bool $val)
    {
        try{
            $sql = 
<<<SQL
INSERT INTO evaluation ( problem_id, :evaluation ) VALUES( :id, 1 )
ON DUPLICATE KEY UPDATE :evaluation = :evaluation + 1;
SQL;
            $evaluation = $val ? 'good' : 'bad';
            $query = \DB::query($sql);
            $query->param('evaluation', DB::expr($evaluation));
            $query->param('id', $id);
            $res = $query->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

public $select = 
<<<SQL
(
    SELECT 
    p.id
        ,p.name
        ,p.setter
        ,p.grade
        ,int2codename('grade', p.grade) grade_name
        ,int2codename('grade_color', p.grade) grade_color
        ,p.wall
        ,int2codename('wall', p.wall) wall_name
        ,p.publishing
        ,int2codename('publishing', p.publishing) publishing_name
        ,p.images
        ,DATE_FORMAT(p.created_at, '%Y/%m/%d') created_at
        ,coalesce(e.good, 0) good
        ,coalesce(e.bad, 0) bad
        ,p.created_at c_at
        ,p.created_by
    FROM problem_stock p
    LEFT JOIN (
        SELECT problem_id, good, bad FROM evaluation
    ) e ON p.id = e.problem_id
    WHERE publishing = 1
) t
SQL;
}