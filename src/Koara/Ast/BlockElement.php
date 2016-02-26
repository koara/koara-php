<?php

namespace Koara\Ast;

use Koara\Renderer;

class BlockElement extends Node
{
	
	/**
	 * @return bool
	 */
    public function isNested()
    {
        return !($this->getParent() instanceof Document);
    }

    /**
     * @return bool
     */
    public function isSingleChild()
    {
        return count($this->getParent()->getChildren()) === 1;
    }

    /**
     * @param Renderer $renderer
     */
    public function accept(Renderer $renderer)
    {
        $renderer->visitBlockElement($this);
    }
    
}
