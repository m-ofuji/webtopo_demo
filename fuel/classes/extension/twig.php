<?php

class Twig_Fuel_Extension extends \Parser\Twig_Fuel_Extension
{
    public function getFunctions()
    {
        return array_merge(parent::getFunctions(), array(
            'asset_js'        => new Twig_Function_Function('Asset::js'),
            'asset_img'       => new Twig_Function_Function('Asset::img'),
            'asset_css'       => new Twig_Function_Function('Asset::css'),
            'asset_get_file'  => new Twig_Function_Function('Asset::get_file'),
            'get_codes'       => new Twig_Function_Function('Code::getCodes'),
            'get_grades'      => new Twig_Function_Function('Code::getGrades'),
            'get_year_month'  => new Twig_Function_Function('Code::getYearMonth'),
            'decode_json'     => new Twig_Function_Function('Func::decodeJson')
        ));
    }
}