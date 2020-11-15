<?php
namespace Admin;

class Controller_Login extends \Controller
{
    public function get_index()
    {
        $view = \View_Twig::forge('login');

        return \Response::forge($view);
    }

    public function post_login()
    {
        $error = null;
        $view = \View_Twig::forge('login');
        $username = \Input::post('username');
        $pass = \Input::post('password');
        $auth = \Auth::instance();

        if (empty($username)) {
            $error = 'ユーザーIDを入力してください';
        } else if (empty($pass)) {
            $error = 'パスワードを入力してください';
        } else if (!$auth->login($username, $pass)) {
            $error = 'ログインに失敗しました';
        } else {
            //ユーザ情報取得
            $userid = \Auth::get_user_id();
            $user = Model_Users::find_by_pk($userid[1]);
            \Session::set('user', array(
                'id'        => $user->id,
                'group'     => $user->group,
                'username'  => $user->username,
                'email'     => $user->email,
                'nick_name' => $user->nick_name
            ));

            \Response::redirect('index.php/admin/problem');
        }

        $view->set('error', $error);
        return $view;
    }

    public function get_logout()
    {
        \Auth::logout();
        $view = \View_Twig::forge('login');
        $view -> set('error', null);
        return $view;
    }
}