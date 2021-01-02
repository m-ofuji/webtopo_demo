<?php

class Controller_Session extends Controller_ProblemBase
{
    public function get_problems() 
    {
        $input = \Input::get();
        return \Response::forge($this->problems(Model_Session::forge(), $input), 200);
    }

    public function post_evaluation()
    {
        $input = \Input::post();

        if (!array_key_exists('val', $input) 
            || ($input['val'] !== 'g' && $input['val'] !== 'b')
            || !array_key_exists('id', $input)
            || !is_numeric($input['id'])
        )
            return \Response::forge('Bad Request', 400);

        $model = Model_Session::forge();
        $res =  $model->evaluate($input['id'], $input['val'] === 'g');
        return \Response::forge($res, 200);
    }
}
