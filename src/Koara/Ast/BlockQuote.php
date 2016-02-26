<?php

namespace Koara\Ast;

use Koara\Renderer;

class BlockQuote extends BlockElement
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitBlockQuote($this);
    }
    
}
