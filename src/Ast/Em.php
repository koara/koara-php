<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Em extends Node
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitEm($this);
    }
    
}
