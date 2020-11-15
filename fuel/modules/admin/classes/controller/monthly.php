<?php
namespace Admin;

class Controller_Monthly extends Controller_Abstract
{
    public function get_index()
    {
        $this->_header = \View_Twig::forge('monthly/header');;
        $this->_footer = \View_Twig::forge('monthly/footer');;
        $this->template->content = \View_Twig::forge('monthly/index');
    }

    public function get_detail($yearMonth = null)
    {
        $this->template->content = \View_Twig::forge('monthly/detail');
        $problemNum = 15;
        if (is_numeric($yearMonth)) {
            $model = Model_Monthly::forge();
            $record = $model->getDetail($yearMonth);
            if (count($record) > 0) {
                $this->template->content->set($record[0]);
                $problemNum = $record[0]['problem_num'];
                $ps = $model->getProblem($yearMonth);
            }
        }

        for ($i = 0; $i < $problemNum; $i++) {
            $problem = null;
            if ($yearMonth && count($record) && count($ps) > 0) {
                foreach ($ps as $key => $value) {
                    if ($value['monthly_order'] == $i + 1) {
                        $problem = $value;
                        break;
                    }
                }
            }
            if ($problem) {
                $problem['img'] = \Func::decodeJson($problem['images'])['img'][0];
                $problem['imgs'] = \Func::decodeJson($problem['images'])['img'];
                $problems[] = $problem;
            } else {
                $problems[] = array('id' => '0');
            }
        }
        $this->template->content->set('problems', $problems);
        $this->template->content->set('comment', \View_Twig::forge('component/comment'));
    }

    public function get_records() 
    {
        $input = \Input::get();

        $sort = array_key_exists('sort', $input) ? $input['sort'] : 'year_month';
        $order = array_key_exists('order', $input) ? $input['order'] : 'desc';
        $where = array_key_exists('search_condition', $input) ? $input['search_condition'] : array();

        $records = Model_Monthly::forge()->getRecords($sort, $order, $where);
        $view = null;

        if (count($records) > 0) {
            $view = \View_Twig::forge('monthly/records');
            $view->set(array(
                'months' => $records,
            ));
        } else {
            $view = \View_Twig::forge('notfound');
            $view->set(array('title' => 'マンスリー'));
        }
        return \Response::forge($view, 200);
    }

    public function get_stocks()
    {
        $stocks = Model_Monthly::forge()->getStocks();
        $view = \View_Twig::forge('monthly/stocks')->set(array('stocks' => $stocks));
        return \Response::forge(json_encode(array('stocks' => $stocks)), 200);
    }

    public function post_register()
    {
        $input = \Input::post();

        $model = Model_Monthly::forge();
        $res = $model->register($input);

        $this->template->content = \View_twig::forge('msg')->set(array(
            'redirect_to' => 'index.php/'.$this->_controller,
            'result'  => $res,
            'message' => ($res ? '登録に成功しました' : '登録に失敗しました')
        ));
    }

    public function post_validation()
    {
        $res = $this->validate(Model_Monthly::forge(), \Input::post());
        return $this->response($res, 200);
    }
}
