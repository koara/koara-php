<?php

namespace Koara\Ast;

use Koara\Renderer;

class Text extends Node
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitText($this);
    }
    
}
