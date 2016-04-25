<?php

namespace Koara\Ast;

use Koara\Renderer;

class BlockElement extends Node
{
	
	/**
	 * @return bool
	 */
	public function hasChildren() {
		return $this->getChildren() != null && sizeof($this->getChildren()) > 0;
	}
	
	/**
	 * @return bool
	 */
	public function isFirstChild() {
		return $this->getParent()->getChildren()[0] === $this;
	}
	
	/**
	 * @return bool
	 */
	public function isLastChild() {
		$children = $this->getParent()->getChildren();
		return $children[sizeof($children) - 1] === $this;
	}
	
	/**
	 * @return bool
	 */
    public function isNested()
    {
        return !($this->getParent() instanceof Document);
    }

    /**
     * @return bool
     */
    public function isSingleChild()
    {
        return count($this->getParent()->getChildren()) === 1;
    }

    /**
     * @param Renderer $renderer
     */
    public function accept(Renderer $renderer)
    {
        $renderer->visitBlockElement($this);
    }
    
}
