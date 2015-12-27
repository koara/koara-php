<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Paragraph extends BlockElement
{
    public function accept(Renderer $renderer)
    {
        $renderer->visitParagraph($this);
    }
}
