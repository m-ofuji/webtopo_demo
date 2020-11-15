<?php

class Controller_Session extends Controller_ProblemBase
{
    public function get_problems() 
    {
        $input = \Input::get();
        return \Response::forge($this->problems(Model_Session::forge(), $input), 200);
    }
}
