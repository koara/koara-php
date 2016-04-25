<?php

namespace Koara\Ast;

use Koara\Renderer;

class ListItem extends BlockElement
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
