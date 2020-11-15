<?php
namespace Admin;

class Controller_Comment extends Controller_Abstract
{
    public function get_comments($problemId)
    {
        $res = array(
            'res' => false
        );
        if (!$problemId) return \Response::forge(json_encode($res), 400);
        $view = $this->getCommentsView($problemId);
        $res['res'] = true;
        $res['comments'] = $view;
        return \Response::forge(json_encode($res, 200));
    }

    public function post_register()
    {
        $input = \Input::post();
        $model = Model_Comments::forge();
        $validation = $this->validate($model, $input);

        $res = array(
            'res' => $validation
        );

        if (!$res) return \Response::forge(json_encode($res), 400);

        $result = $model->register($input);
        $res['res'] = $result;
        if ($result) {
            $view = $this->getCommentsView($input['problem_id']);
            $res['comments'] = $view;
        }

        return \Response::forge(json_encode($res), 200);
    }

    private function getCommentsView(string $problemId): string
    {
        $comments = Model_Comments::forge()->getComments($problemId);
        $view = \View_Twig::forge('component/comment_content')->set(array('comments' => $comments));
        return $view;
    }
}