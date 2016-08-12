<?php

namespace Koara\Ast;

use Koara\Renderer;

class LineBreak extends Node
{

    /**
     * @var bool
     */
    private $explicit;
	
	/**
	 * @param Renderer $renderer
	 */
    public function accept(Renderer $renderer)
    {
        $renderer->visitLineBreak($this);
    }

    /**
     * @return bool
     */
    public function isExplicit()
    {
        return $this->explicit;
    }

    /**
     * @param bool $explicit
     */
    public function setExplicit($explicit)
    {
        $this->explicit = $explicit;
    }
    
}
