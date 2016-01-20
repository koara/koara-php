<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class ListItem extends Node
{
    /**
     * @var int
     */
    private $number;

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @param Renderer $renderer
     */
    public function accept(Renderer $renderer)
    {
        $renderer->visitListItem($this);
    }
    
}
