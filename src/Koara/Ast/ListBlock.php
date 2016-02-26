<?php

namespace Koara\Ast;

use Koara\Renderer;

class ListBlock extends BlockElement
{
    /**
     * @var bool
     */
    private $ordered;

    /**
     * Constructor
     */
    public function __construct($ordered)
    {
        $this->ordered = $ordered;
    }

    /**
     * @return bool
     */
    public function isOrdered()
    {
        return $this->ordered;
    }
 
    /**
     * @param Renderer $renderer
     */
    public function accept(Renderer $renderer)
    {
        $renderer->visitListblock($this);
    }
    
}
