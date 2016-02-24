<?php

namespace Koara;

use Koara\Ast\Node;

class TreeState
{
    private $nodes;
    private $marks;
    private $nodesOnStack;
    private $currentMark;

    public function __construct()
    {
        $this->nodes = [];
        $this->marks = [];
        $this->nodesOnStack = 0;
        $this->currentMark = 0;
    }

    public function openScope()
    {
        $this->marks[] = $this->currentMark;
        $this->currentMark = $this->nodesOnStack;
    }

    public function closeScope(Node $n)
    {
        $a = $this->nodeArity();
        $this->currentMark = array_pop($this->marks);
        while ($a-- > 0) {
            $c = $this->popNode();
            $c->setParent($n);
            $n->add($c, $a);
        }
        $this->pushNode($n);
    }

    public function addSingleValue(Node $n, Token $t)
    {
        $this->openScope($n);
        $n->setValue($t->image);
        $this->closeScope($n);
    }

    private function nodeArity()
    {
        return $this->nodesOnStack - $this->currentMark;
    }

    private function popNode()
    {
        --$this->nodesOnStack;
       return array_pop($this->nodes);
    }

    private function pushNode(Node $n)
    {    	
        $this->nodes[] = $n;
        ++$this->nodesOnStack;
    }
}
