<?php
class Code
{
    public static function getCodes(string $codeType)
    {
        $result = Model_Codes::getCodes($codeType);  
        if (!$result) return null;
        return $result;
    }

    public static function getGrades()
    {
        $result = Model_Codes::getGrades();
        if (!$result) return null;
        return $result;
    }

    public static function getYearMonth()
    {
        $result = Admin\Model_Monthly::forge()->getRecords('year_month', 'desc', array());
        if (!$result) return null;
        return $result;
    }
}