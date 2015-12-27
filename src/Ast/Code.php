<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Code extends Node
{
    public function accept(Renderer $renderer)
    {
        $renderer->visitCode($this);
    }
}
