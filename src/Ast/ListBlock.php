<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class ListBlock extends BlockElement
{
    /**
     * @var bool
     */
    private $ordered;

    public function __construct($ordered)
    {
        $this->ordered = $ordered;
    }

    public function isOrdered()
    {
        return ordered;
    }

    public function accept(Renderer $renderer)
    {
        $renderer.visitListblock($this);
    }
}
