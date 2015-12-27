<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Link extends Node
{
    public function accept(Renderer $renderer)
    {
        $renderer.visitLink($this);
    }
}
