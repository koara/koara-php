<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class LineBreak extends Node
{
    public function accept(Renderer $renderer)
    {
        $renderer.visitLineBreak($this);
    }
}
