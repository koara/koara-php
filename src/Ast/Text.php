<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Text extends Node
{
    public function accept(Renderer $renderer)
    {
        $renderer->visitText($this);
    }
}
