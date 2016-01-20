<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Code extends Node
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitCode($this);
    }
    
}
