<?php
class Uploader
{
    public static function uploadImage(string $saveTo): array
    {
        $res = array();
        try {
            $config = array(
                'path' => $saveTo,
                'randomize' => true,
                'ext_whitelist' => array('img', 'jpg', 'jpeg', 'gif', 'png'),
            );
    
            // $_FILES 内のアップロードされたファイルを処理する
            \Upload::process($config);
    
            // 有効なファイルがある場合
            if (\Upload::is_valid())
            {
                // 設定にしたがって保存する
                \Upload::save();
    
                $res = \Upload::get_files();
    
                foreach ($res as $key => $val) {
                    $fileName = $val['saved_as'];
                    
                    // $image = \Image::load($saveTo.'/'.$fileName)
                    //             ->resize(750,750)
                    //             ->save($saveTo.'/'.$fileName);
                    $thumb = \Image::load($saveTo.'/'.$fileName)
                                ->resize(250,250)
                                ->save($saveTo.'/thumb_'.$fileName);
                    // $res['thumb'] = $saveTo.'/thumb_'.$res;
                    
                    // $fileNames[] = $val['saved_as'];
                    // $res['res'][] = true;
                    // $res['img'][] = $val['saved_as'];
                }
            }

            return $res;

        } catch(Exception $e) {
            \Log::warning($e->getMessage());
            return $res;
        }
    }
}