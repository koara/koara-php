<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class BlockQuote extends BlockElement
{
    public function accept(Renderer $renderer)
    {
        $renderer->visitBlockQuote($this);
    }
}
