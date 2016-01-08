<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class ListItem extends Node
{
    /**
     * @var int
     */
    private $number;

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function accept(Renderer $renderer)
    {
        $renderer->visitLink($this);
    }
}
