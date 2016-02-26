<?php

namespace Koara\Ast;

use Koara\Renderer;

class Paragraph extends BlockElement
{
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitParagraph($this);
    }
    
}
