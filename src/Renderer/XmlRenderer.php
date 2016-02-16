<?php

namespace Koara\Renderer;

use Koara\Ast\BlockElement;
use Koara\Ast\ListItem;
use Koara\Ast\Paragraph;

class XmlRenderer implements Renderer
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
        $this->out = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        if($node->getChildren() != null && sizeof($node->getChildren()) > 0) {
        	$this->out .= "<document>\n";
	        $node->childrenAccept($this);
	        $this->out .= "</document>";
        } else {
        	$this->out .= "<document />";
        }
    }

    public function visitHeading($node)
    {
        $this->level++;
		$this->out .= $this->indent()."<heading level=\"".$node->getValue()."\"";
		if($node->getChildren() != null && sizeof($node->getChildren()) > 0) {
			$this->out .= ">\n";
			$this->level++;
			$node->childrenAccept($this);
			$this->level--;
			$this->out .= $this->indent()."</heading>\n";
		} else {
			$this->out .= " />\n";
		}
		$this->level--;
    }

    public function visitBlockQuote($node)
    {
    	$this->level++;
    	$this->out .= $this->indent()."<blockquote";
    	if($node->getChildren() != null && sizeof($node->getChildren()) > 0) {
    		$this->out .= ">\n";
    		$this->level++;
    		$node->childrenAccept($this);
    		$this->level--;
    		$this->out .= $this->indent()."</blockquote>\n";
    		$this->level--;
    	} else {
    		$this->out .= " />\n";
    	}
    }

    public function visitListBlock($node)
    {
    	$this->level++;
    	$this->out .= $this->indent()."<list ordered=\"".($node->isOrdered() ? "true": "false")."\">\n";
    	$node->childrenAccept($this);
    	$this->out .= $this->indent()."</list>\n";
    	$this->level--;
    }

    public function visitListItem($node)
    {
    	$this->level++;
    	$this->out .= $this->indent()."<listitem";
    	if($node->getNumber() != null) {
    		$this->out .= " number=\"".$node->getNumber()."\"";
    	}
    	if($node->getChildren() != null && sizeof($node->getChildren()) > 0) {
    		$this->out .= ">\n";
    		$node->childrenAccept($this);
    		$this->out .= $this->indent()."</listitem>\n";
    	} else {
    		$this->out .= " />\n";
    	}
    	$this->level--;
    }

    public function visitCodeBlock($node)
    {
    	$this->level++;
    	$this->out .= $this->indent()."<codeblock";
    	if($node->getLanguage() != null) {
    		$this->out .= " language=\"".$node->getLanguage()."\"";
    	}
    	if($node->getValue() != null && strlen($node->getValue()) > 0) {
    		$this->out .= ">\n";
    		$this->level++;
    		$this->out .= $this->escape($node->getValue());
    		$this->level--;
    		$this->out .= $this->indent()."</codeblock>\n";
    		$this->level--;
    	} else {
    		$this->out .= " />\n";
    	}
    }

    public function visitParagraph($node)
    {
    	$this->level++;
    	$this->out .= $this->indent()."<paragraph>\n";
    	$this->level++;
    	$node->childrenAccept($this);
    	$this->level--;
    	$this->out .= $this->indent()."</paragraph>\n";
    	$this->level--;
    }

    public function visitBlockElement($node)
    {
    }

    public function visitImage($node)
    {
      	$this->out .= $this->indent()."<image url=\"".$this->escapeUrl($node->getValue())."\">\n";
		$this->level++;
		$node->childrenAccept($this);
		$this->level--;
		$this->out .= $this->indent()."</image>\n";
    }

    public function visitLink($node)
    {
    	$this->out .= $this->indent()."<link url=\"".$this->escapeUrl($node->getValue())."\">\n";
    	$this->level++;
    	$node->childrenAccept($this);
    	$this->level--;
    	$this->out .= $this->indent()."</link>\n";
    }

    public function visitStrong($node)
    {
    	$this->out .= $this->indent()."<strong>\n";
    	$this->level++;
    	$node->childrenAccept($this);
    	$this->level--;
    	$this->out .= $this->indent()."</strong>\n";
    }

    public function visitEm($node)
    {
        $this->out .= $this->indent()."<em>\n";
		$this->level++;
		$node->childrenAccept($this);
		$this->level--;
		$this->out .= $this->indent()."</em>\n";
    }

    public function visitCode($node)
    {
    	$this->out .= $this->indent()."<code>\n";
    	$this->level++;
    	$node->childrenAccept($this);
    	$this->level--;
    	$this->out .= $this->indent()."</code>\n";
    }

    public function visitText($node)
    {
    	$this->out .= $this->indent()."<text>";
    	$this->out .= $this->escape($node->getValue());
    	$this->out .= "</text>\n";
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
    	$this->out .= $this->indent()."<linebreak />\n";
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
