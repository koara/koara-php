<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Em extends Node
{
    public function accept(Renderer $renderer)
    {
        $renderer->visitEm($this);
    }
}
