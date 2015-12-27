<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Document extends Node
{
    public function accept(Renderer $renderer)
    {
        $renderer->visitDocument($this);
    }
}
