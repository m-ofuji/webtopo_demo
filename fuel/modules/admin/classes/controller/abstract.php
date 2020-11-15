<?php
namespace Admin;

class Controller_Abstract extends \Controller_Hybrid
{
    protected $body;
    protected $_header = null;
    protected $_footer = null;
    protected $_controller;
    protected $_content_params = array();
    protected $_has_header = false;
    public $template = 'template.twig';

    function __construct($request) 
    {
        parent::__construct($request);

        $this->_controller = mb_strtolower(preg_replace('/Controller_/', '', \Request::main()->controller));
    }

    public function before()
    {
        if (!(\Auth::check())) {
            return \Response::redirect('index.php/admin/login');
        }
        return parent::before();
    }

    public function after($response)
    {
        if (\Input::is_ajax()) return $response;

        $response = parent::after($response);

        $this->template->set(array(
            'controller'    => $this->_controller,
            'header'        => $this->_header,
            'footer'        => $this->_footer,
            'image_popup'   => \View_Twig::forge('component/image_popup')
        ));

        if (isset($this->template->content)) {
            $this->_content_params = array(
                'csrf_token'    => \Csrf::fetch()
            );
            $this->template->content->set($this->_content_params);
        }
        $this->template->menu = \View_Twig::forge('menu', array('username' => \Session::get('user.username')));

        $this->template->hamburger = \View_Twig::forge('hamburger');

        \Logger::Write('access', implode('/', \Uri::segments()));

        return $response;
    }

    protected function validate($model, array $input): array
    {
        $res = array();
        if (!$model->validate($input)) {
            $res['result'] = false;
            $res['error' ] = \Validation::getErrorMsg($model->getValidation());
        } else {
            $res['result'] = true;
        }

        return $res;
    }
}