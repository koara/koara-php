<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class Heading extends BlockElement
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitHeading($this);
    }
    
}
