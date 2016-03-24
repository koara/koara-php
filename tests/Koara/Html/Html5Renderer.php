<?php

namespace Koara\Html;

use Koara\Ast\BlockElement;
use Koara\Ast\ListItem;
use Koara\Ast\Paragraph;

use Koara\Renderer;

class Html5Renderer implements Renderer
{
    /**
     * @var string
     */
    private $out;

    /**
     * @var int
     */
    private $level = 0;

    /**
     * @var int[]
     */
    private $listSequence = [];

    public function visitDocument($node)
    {
        $node->childrenAccept($this);
    }

    public function visitHeading($node)
    {
        $this->out .= $this->indent().'<h'.$node->getValue().'>';
        $node->childrenAccept($this);
        $this->out .= '</h'.$node->getValue().">\n";
        if (!$node->isNested()) {
            $this->out .= "\n";
        }
    }

    public function visitBlockQuote($node)
    {
        $this->out .= $this->indent().'<blockquote>';
        if ($node->getChildren() != null && sizeof($node->getChildren()) > 0) {
            $this->out .= "\n";
        }
        ++$this->level;
        $node->childrenAccept($this);
        --$this->level;
        $this->out .= $this->indent()."</blockquote>\n";
        if (!$node->isNested()) {
            $this->out .= "\n";
        }
    }

    public function visitListBlock($node)
    {
        $this->listSequence[] = 0;
        $tag = $node->isOrdered() ? 'ol' : 'ul';
        $this->out .= $this->indent().'<'.$tag.">\n";
        ++$this->level;
        $node->childrenAccept($this);
        --$this->level;
        $this->out .= $this->indent().'</'.$tag.">\n";
        if (!$node->isNested()) {
            $this->out .= "\n";
        }
        array_pop($this->listSequence);
    }

    public function visitListItem($node)
    {
        $seq = end($this->listSequence) + 1;
        $this->listSequence[count($this->listSequence) - 1] = $seq;
        $this->out .= $this->indent().'<li';
        if ($node->getNumber() != null && ($seq != $node->getNumber())) {
            $this->out .= ' value="'.$node->getNumber().'"';
            $this->listSequence[] = $node->getNumber();
        }
        $this->out .= '>';
        if ($node->getChildren() != null) {
            $block = $node->getChildren()[0] instanceof Paragraph || get_class($node->getChildren()[0]) === 'Koara\Ast\BlockElement';
            if (sizeof($node->getChildren()) > 1 || !$block) {
                $this->out .= "\n";
            }
            ++$this->level;
            $node->childrenAccept($this);
            --$this->level;
            if (sizeof($node->getChildren()) > 1 || !$block) {
                $this->out .= $this->indent();
            }
        }
        $this->out .= "</li>\n";
    }

    public function visitCodeBlock($node)
    {
        $this->out .= $this->indent().'<pre><code';
        if ($node->getLanguage() != null) {
            $this->out .= ' class="language-'.$this->escape($node->getLanguage()).'"';
        }
        $this->out .= '>';
        $this->out .= $this->escape($node->getValue())."</code></pre>\n";
        if (!$node->isNested()) {
            $this->out .= "\n";
        }
    }

    public function visitParagraph($node)
    {
        if ($node->isNested() && ($node->getParent() instanceof ListItem) && $node->isSingleChild()) {
            $node->childrenAccept($this);
        } else {
            $this->out .= $this->indent().'<p>';
            $node->childrenAccept($this);
            $this->out .= "</p>\n";
            if (!$node->isNested()) {
                $this->out .= "\n";
            }
        }
    }

    public function visitBlockElement($node)
    {
        if ($node->isNested() && ($node->getParent() instanceof ListItem) && $node->isSingleChild()) {
            $node->childrenAccept($this);
        } else {
            $this->out .= $this->indent();
            $node->childrenAccept($this);
            if (!$node->isNested()) {
                $this->out .= "\n";
            }
        }
    }

    public function visitImage($node)
    {
        $this->out .= '<img src="'.$this->escapeUrl($node->getValue()).'" alt="';
        $node->childrenAccept($this);
        $this->out .= '" />';
    }

    public function visitLink($node)
    {
        $this->out .= '<a href="'.$this->escapeUrl($node->getValue()).'">';
        $node->childrenAccept($this);
        $this->out .= '</a>';
    }

    public function visitStrong($node)
    {
        $this->out .= '<strong>';
        $node->childrenAccept($this);
        $this->out .= '</strong>';
    }

    public function visitEm($node)
    {
        $this->out .= '<em>';
        $node->childrenAccept($this);
        $this->out .= '</em>';
    }

    public function visitCode($node)
    {
        $this->out .= '<code>';
        $node->childrenAccept($this);
        $this->out .= '</code>';
    }

    public function visitText($node)
    {
        $this->out .= $this->escape($node->getValue());
    }

    public function escape($text)
    {
        return str_replace(
          array('&', '<', '>', '"'),
          array('&amp;', '&lt;', '&gt;', '&quot;'),
          $text
        );
    }

    public function visitLineBreak($node)
    {
        $this->out .= "<br>\n".$this->indent();
        $node->childrenAccept($this);
    }

    public function escapeUrl($text)
    {
        return str_replace(
          array(' ', '"', '`', '<', '>', '[', ']', '\\'),
          array('%20', '%22', '%60', '%3C', '%3E', '%5B', '%5D', '%5C'),
          $text
        );
    }

    public function indent()
    {
        $buf = '';
        $repeat = $this->level * 2;
        for ($i = $repeat - 1; $i >= 0; --$i) {
            $buf .= ' ';
        }

        return $buf;
    }

    public function getOutput()
    {
        return trim($this->out);
    }
    
}
