<?php

namespace Koara\Ast;

use Koara\Renderer;

class Heading extends BlockElement
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitHeading($this);
    }
    
    public function getLevel()
    {
    	return $this->getValue();
    }
    
}
