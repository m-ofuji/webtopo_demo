<?php
namespace Admin;

define('SAVE_TO', '/home/ojitopo/www/public/assets/image/problem');

class Controller_ProblemBase extends Controller_Abstract
{
    protected $isMonthly = false;

    function __construct($request) 
    {
        parent::__construct($request);

        $this->isMonthly = preg_replace('/admin\\\/', '', $this->_controller) !== 'problem';
    }

    public function get_index()
    {
        $param = array(
            'controller' => $this->_controller,
            'is_monthly' => $this->isMonthly
        );

        $this->_header = \View_Twig::forge('problem/header', $param);
        $this->_footer = \View_Twig::forge('problem/footer', $param);
        $this->template->content = \View_Twig::forge('problem/index', $param);
        $this->template->content->set('comment', \View_Twig::forge('component/comment'));
        $this->template->content->set('search_form', \View_Twig::forge('component/search_problem', $param));
    }

    public function get_detail($id = null)
    {
        $this->template->content = \View_Twig::forge('problem/detail');
        $this->template->content->set(array(
            'controller' => $this->_controller,
            'is_monthly' => $this->isMonthly,
        ));
        if (is_numeric($id)) {
            $record = Model_Problems::forge()->getDetail($id, $this->isMonthly);
            $record['img'] = \Func::decodeJson($record['images'])['img'];
            $this->template->content->set($record);
        }
    }

    public function get_problems() 
    {
        $input = \Input::get();

        $sort = array_key_exists('sort', $input) ? $input['sort'] : 'created_at';
        $order = array_key_exists('order', $input) ? $input['order'] : 'desc';
        $offset = array_key_exists('offset', $input) ? $input['offset'] : 0 ;
        $limit = 10;
        $where = array_key_exists('search_condition', $input) ? $input['search_condition'] : array();

        $records = Model_Problems::forge()->getRecords($sort, $order, $where, $offset, $limit, $this->isMonthly);
        $view = null;

        if (count($records) > 0) {
            foreach($records as $key => $value) {
                $records[$key]['img'] = \Func::decodeJson($records[$key]['images'])['img'][0];
                $records[$key]['imgs'] = \Func::decodeJson($records[$key]['images'])['img'];
            }
    
            $view = \View_Twig::forge('problem/problems');
            $view->set(array(
                'problems' => $records,
                'is_monthly' => $this->isMonthly,
                'controller' => $this->_controller
            ));
        } else {
            $view = \View_Twig::forge('notfound');
            $view->set(array('title' => '課題'));
        }
        return \Response::forge($view, 200);
    }

    public function post_upload()
    {
        // $img = \Uploader::uploadImage(SAVE_TO);
        // $img = \Uploader::uploadImage(SAVE_TO);
        $img = \Uploader::uploadImage(\Config::get('IMAGE_PATH'));
        $res = array(
            'res' => false,
            'img' => array()
        );
        if (count($img) > 0) {
            $fileNames = array();
            foreach ($img as $key => $val) {
                $fileNames[] = $val['saved_as'];
                $res['res'][] = true;
                $res['img'][] = $val['saved_as'];
            }
        }
        return $this->response($res, 200);
    }

    public function post_register()
    {
        $input = \Input::post();

        if (!array_key_exists('csrf_token', $input) || !\Csrf::check($input['csrf_token'])) {
            return $this->response('Bad Request', 400);
        }

        $res = false;
        if (array_key_exists('images', $input)) {
            $image = array(
                'img' => $input['images']
            );
            $input['images'] = json_encode($image);
            
            $model = Model_Problems::forge();
            $res = $model->register($input, $this->isMonthly);
        }

        $this->template->content = \View_twig::forge('msg')->set(array(
            'redirect_to' => 'index.php/'.$this->_controller,
            'result'  => $res,
            'message' => ($res ? '投稿に成功しました' : '投稿に失敗しました')
        ));
    }

    public function post_validation()
    {
        $res = $this->validate(Model_Problems::forge(), \Input::post());
        return $this->response($res, 200);
    }

    public function post_delete($id)
    {
        $result = false;

        if ($id) {
            $result = Model_Problems::forge()->deleteRecord($id, $this->isMonthly);
        }

        $this->template->content = \View_twig::forge('msg')->set(array(
            'redirect_to' => 'index.php/admin/problem',
            'result'  => $result,
            'message' => ($result ? '削除しました' : '削除に失敗しました')
        ));
    }

    public function post_move()
    {
        // if (!array_key_exists('csrf_token', $input) || !\Csrf::check($input['csrf_token'])) {
        //     return $this->response('Bad Request', 400);
        // }
        $res = array(
            'res' => false
        );
        $id = \Input::post('id');
        if (!$id) return \Response::forge(false, 400);
        $res['res'] = Model_Problems::forge()->moveProblem($id, $this->isMonthly);
        return \Response::forge(json_encode($res), 200);
    }
}
