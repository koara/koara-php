<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Document extends Node
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitDocument($this);
    }
    
}
