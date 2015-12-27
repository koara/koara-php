<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class BlockElement extends Node
{
    public function isNested()
    {
        return !($this->getParent() instanceof Document);
    }

    public function isSingleChild()
    {
        return $this->getParent().getChildren().length == 1;
    }

    public function accept(Renderer $renderer)
    {
        $renderer->visitBlockElement($this);
    }
}
