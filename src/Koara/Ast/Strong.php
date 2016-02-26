<?php

namespace Koara\Ast;

use Koara\Renderer;

class Strong extends Node
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitStrong($this);
    }
}
