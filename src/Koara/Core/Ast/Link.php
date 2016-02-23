<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Link extends Node
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitLink($this);
    }
    
}
