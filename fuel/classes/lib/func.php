<?php
class Func
{
    public static function decodeJson($json = null)
    {
        // if (! $json) {
        //     return null;
        // }

        return json_decode($json, true);
    }
}
// namespace Customize\Twig\Extension;

// use Twig\Extension\AbstractExtension;
// class jsonDecodeTwigExtension extends AbstractExtension
// {
//     public function getFilters()
//     {
//         return [
//             new \Twig_Filter('json_decode', [$this, 'jsonDecodeFilter']),
//         ];
//     }

//     /**
//      * @param  null  $json
//      *
//      * @return mixed|null
//      */
//     public function jsonDecodeFilter($json = null)
//     {
//         if (! $json) {
//             return null;
//         }

//         return json_decode($json, true);
//     }
// }