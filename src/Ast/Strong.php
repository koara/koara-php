<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Strong extends Node
{
    public function accept(Renderer $renderer)
    {
        $renderer->visitString($this);
    }
}
