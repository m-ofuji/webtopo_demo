<?
class Logger 
{
    // ユーザーID取得
    private static function get_userid($isUseSessionId)
    {
        //print_r(Auth::get_user_id());
        if($isUseSessionId){
          return Session::key('session_id');
        }else{
          list(, $userid) = Auth::get_user_id();
          return $userid;
        }
    }

    // ユーザーグループ取得
    private static function get_groupid()
    {
        list($driver, $groupid) = Auth::get_groups()[0];
        return $groupid;
    }     
    
    public static function Write($process = '', $param = '', $action = '', $isUseSessionId = false)
    {
        \Config::load('file', true);

        try {
            $path     = \Config::get('log_path', APPPATH.'logs'.DS);

            $rootpath = $path.date('Y').DS;
            $filepath = $path.date('Y/m').DS;
            $filename = $filepath.date('d').'.log';

            $permission = \Config::get('file.chmod.folders', 0777);

            if (!is_dir($rootpath))
            {
                mkdir($rootpath, 0777, true);
                chmod($rootpath, $permission);
            }
            
            if (! is_dir($filepath))
            {
                mkdir($filepath, 0777, true);
                chmod($filepath, $permission);
            }

            $handle = fopen($filename, 'a');
            list(, $userid) = Auth::get_user_id();
            // $disaster = Session::get('disaster_id');
            
            fwrite($handle, '['.date('Y-m-d H:i:s').']'
                            .', '
                            .'user_id: '
                            .SELF::get_userid($isUseSessionId)
                            .', '
                            .'user_name: '
                            .Auth::get_screen_name()
                            .', '
                            .'nick_name: '
                            .Session::get('user.nick_name')
                            .', '
                            .$process
                            .', '
                            .$param
                            .', '
                            .$action
                            .PHP_EOL);
                            
            chmod($filename, Config::get('file.chmod.files', 0666));
            fclose($handle);

        } catch (\Exception $e) {
            throw new FuelException('Unable to create or write to the log file');
        }
    }
}