<?php
namespace Admin;

class Controller_Template extends Controller_Abstract
{
    public function get_index()
    {
        $this->_header = \View_Twig::forge('template/header');
        // $this->_footer = \View_Twig::forge('monthly/footer');
        
        $this->template->content = \View_Twig::forge('template/index');
    }

    // public function post_validation()
    // {
    //     $res = $this->validate(Model_Pass::forge(), \Input::post());
    //     return $this->response($res, 200);
    // }

    // public function post_register()
    // {
    //     $input = \Input::post();

    //     $model = Model_Pass::forge();
    //     $res = $model->register($input);

    //     $this->template->content = \View_twig::forge('msg')->set(array(
    //         'redirect_to' => 'index.php/admin/problem',
    //         'result'  => $res,
    //         'message' => ($res ? '登録に成功しました' : '登録に失敗しました')
    //     ));
    // }
}