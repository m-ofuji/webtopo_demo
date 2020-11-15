<?php
class Controller_Abstract extends Controller_Hybrid
{
    protected $body;
    protected $_controller;
    protected $_header = null;
    protected $_footer = null;
    public $template = 'template.twig';

    function __construct($request) 
    {
        parent::__construct($request);

        $this->_controller = mb_strtolower(preg_replace('/Controller_/', '', Request::main()->controller));
    }

    public function after($response)
    {
        if (Input::is_ajax()) return $response;

        $response = parent::after($response);

        $this->template->set(array(
            'controller'    => $this->_controller,
            'footer'        => $this->_footer,
            'header'        => $this->_header
        ));

        $this->template->menu = View_Twig::forge('menu');

        $this->template->hamburger = View_Twig::forge('hamburger');

        return $response;
    }
}