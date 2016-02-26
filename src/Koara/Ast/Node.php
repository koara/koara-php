<?php

namespace Koara\Ast;

use Koara\Renderer;

abstract class Node
{
    /**
     * @var Node
     */
    private $parent;

    /**
     * @var Node[]
     */
    private $children;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param Node    $node
     * @param int     $i
     */
    public function add($node, $i)
    {
        if ($this->children === null) {
            $this->children = [];
        }
        $this->children[$i] = $node;
    }

    /**
     * @param Renderer $renderer
     */
    public function childrenAccept(Renderer $renderer)
    {
        if ($this->children != null) {
        	$size = sizeof($this->children);
            for ($i = 0; $i < $size; ++$i) {
                $this->children[$i]->accept($renderer);
            }
        }
    }

    /**
     * @param Renderer $renderer
     */
    abstract public function accept(Renderer $renderer);

    /**
     * @return Node[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return Node
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * @param Node $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    
}
