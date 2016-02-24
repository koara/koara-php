<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Image extends Node
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitImage($this);
    }
    
}
