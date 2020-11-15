<?php

class Controller_ProblemBase extends Controller_Abstract
{
    protected $isMonthly = false;

    function __construct($request) 
    {
        parent::__construct($request);

        $this->isMonthly = $this->_controller === 'monthly';
    }

    public function get_index()
    {
        $params = array(
            'is_monthly'  => $this->isMonthly,
            'image_popup' => \View_Twig::forge('component/image_popup')
        );

        if (!$this->isMonthly) {
            $params['search_form'] = \View_Twig::forge('component/search_problem');
        }

        $this->_header = \View_Twig::forge('problem/header', $params);
        $this->_footer = \View_Twig::forge('problem/footer', $params);
        $this->template->content = \View_Twig::forge('problem/index', $params);
    }

    public function problems($model, array $input) 
    {
        $sort = array_key_exists('sort', $input) ? $input['sort'] : 'created_at';
        $order = array_key_exists('order', $input) ? $input['order'] : 'desc';
        $offset = array_key_exists('offset', $input) ? $input['offset'] : 0 ;
        $limit = 10;
        $where = array_key_exists('search_condition', $input) ? $input['search_condition'] : array();

        $records = $model->getRecords($sort, $order, $where, $offset, $limit, $this->isMonthly);
        $view = null;

        if (count($records) > 0) {
            foreach($records as $key => $value) {
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

        return $view;
    }
}
