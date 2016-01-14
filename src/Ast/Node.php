<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

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

    public function add($node, $i)
    {
        if ($this->children == null) {
            $this->children = [];
        }
        $this->children[$i] = $node;
    }

    public function childrenAccept(Renderer $renderer)
    {
        if ($this->children != null) {
        	$size = sizeof($this->children);
            for ($i = 0; $i < $size; ++$i) {
                $this->children[$i]->accept($renderer);
            }
        }
    }

    abstract public function accept(Renderer $renderer);

    public function getChildren()
    {
        return $this->children;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
