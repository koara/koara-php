<?php

namespace Koara\Ast;

use Koara\Renderer;

class LineBreak extends Node
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitLineBreak($this);
    }
    
}
