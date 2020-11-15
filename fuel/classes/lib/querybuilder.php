<?
class Querybuilder
{
    public static function where($conditions): array
    {
        $where = array();
        if (!isset($conditions)) return $where;

        foreach($conditions as $con) {
            switch ($con['operator']) {
                case 'like':
                    $search_text = str_replace(array('\\', '%', '_'), array('\\\\', '\%', '\_'), strtolower($con['val']));
                    $where[] = array(DB::expr('LOWER('.$con['col'].')'),'like', '%'.$search_text.'%');
                    break;
                case 'is_null':
                    $where[] = array($con['col'], 'is', DB::expr('NULL'));
                    break;
                case 'equal':
                    $where[] = array($con['col'], '=', strtolower($con['val']));
                    break;
                case 'greater':
                    $where[] = array($con['col'], '>', strtolower($con['val']));
                    break;
                case 'less':
                    $where[] = array($con['col'], '<', strtolower($con['val']));
                    break;
                case 'egreater':
                    $where[] = array($con['col'], '>=', strtolower($con['val']));
                    break;
                case 'eless':
                    $where[] = array($con['col'], '<=', strtolower($con['val']));
                    break;
                case 'in':
                    // $where[] = array($con['col'], 'in', '('.strtolower($con['val']).')');
                    $where[] = array($con['col'], 'in', $con['val']);
                    break;
            }
        }

        return $where;
    }
}