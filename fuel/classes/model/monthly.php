<?php
class Model_Monthly extends Model_Abstract
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

    public function getMonth(): array
    {
        $sql = 
<<<SQL
(
    SELECT `year_month` ,SUBSTR(cast(`year_month` as char), 1, 4) year
        ,CAST(SUBSTR(cast(`year_month` as char), 5, 2) as signed) month
    FROM months WHERE publishing = 1 ORDER BY `year_month` DESC
) t
SQL;
        $result = \DB::select()->from(DB::expr($sql))->execute()->as_array();
        return $result;
    }

public $select = 
<<<SQL
(
    SELECT 
        id
        ,name
        ,setter
        ,grade
        ,int2codename('grade', grade) grade_name
        ,int2codename('grade_color', grade) grade_color
        ,wall
        ,`year_month`
        ,monthly_order
        ,int2codename('wall', wall) wall_name
        ,publishing
        ,int2codename('publishing', publishing) publishing_name
        ,images
        ,DATE_FORMAT(created_at, '%Y/%m/%d') created_at
        ,created_at c_at
        ,created_by
    FROM monthly_stock
) t
SQL;
}