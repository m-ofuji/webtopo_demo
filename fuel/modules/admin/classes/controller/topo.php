<?php
namespace Admin;

class Controller_Topo extends Controller_Abstract
{
    public function get_index()
    {
        $this->template->content = \View_Twig::forge('topo');
    }
}
