<?php

class Controller_Top extends Controller_Abstract
{
    public function get_index()
    {
        $this->template->content = View_Twig::forge('index');
    }
}
