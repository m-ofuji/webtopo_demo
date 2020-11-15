<?php
class Model_Codes extends Model_Abstract
{
    protected static $_tabel_name = 'codes';
    protected Static $_primary_key = '';

    public static function getCodes($codeType)
    {
        $result = DB::select()
                    ->from(DB::expr(Model_Codes::$codesQuery))
                    ->where(array(array('type', '=', $codeType)))
                    ->execute()
                    ->as_array();
        return $result;
    }

    public static function getGrades()
    {
        $result = DB::select()
                    ->from(DB::expr(Model_Codes::$gradesQuery))
                    ->execute()
                    ->as_array();
        return $result;
    }

    public static $codesQuery = 
<<<SQL
(
    SELECT 
        type
        ,code
        ,name
    FROM codes
    WHERE delete_flg = 0
    -- AND type = :type
    ORDER BY code
) t
SQL;

    public static $gradesQuery = 
<<<SQL
(
    SELECT 
        g.type
        ,g.code code
        ,coalesce(g.name, '未指定') name
        ,coalesce(c.name, 'grey') color
    FROM 
        (SELECT * FROM codes WHERE type = 'grade' and delete_flg = 0) g
        LEFT JOIN 
        (SELECT * FROM codes WHERE type = 'grade_color') c
        ON g.code = c.code
    ORDER BY code
) t
SQL;
}