<?php

class Controller_Monthly extends Controller_ProblemBase
{
    public function get_index()
    {
        parent::get_index();
        $months = Model_Monthly::forge()->getMonth();
        $this->_footer->set(array(
            'months' => $months
        ));
    }

    public function get_problems() 
    {
        $input = \Input::get();

        if (!array_key_exists('search_condition', $input)) {
            $view = \View_Twig::forge('notfound', array('title' => '課題'));
        } else {
            $view = $this->problems(Model_Monthly::forge(), $input);
        }
        return \Response::forge($view, 200);
    }
}
