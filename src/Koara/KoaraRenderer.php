<?php

namespace Koara;

use Koara\Renderer;

class KoaraRenderer implements Renderer
{

 	/**
     * @var string
     */
    private $out;
    
    /**
     * @var string[]
     */
    private $left = [];
    
	public function visitDocument($node)
	{
 		$this->left = [];
		$node->childrenAccept($this);
	}
	
	public function visitHeading($node)
	{
 		if(!$node->isFirstChild()) {
 			$this->indent();
 		}
 		for($i=0; $i<$node->getLevel(); $i++) {
 			$this->out .= "=";
 		}
 		if($node->hasChildren()) {
 		  $this->out .= " ";
 		  $node->childrenAccept($this);
 		}
 		$this->out .= "\n";
 		if(!$node->isLastChild()) {
 			$this->indent();
 			$this->out .= "\n";
 		}
 	}

 	public function visitBlockQuote($node)
	{
// 		if(!node.isFirstChild()) {
// 			indent();
// 		}
		
// 		if(node.hasChildren()) {
// 			out.append("> ");
// 			left.push("> ");
// 			node.childrenAccept(this);
// 			left.pop();
// 		} else {
// 			out.append(">\n");
// 		}
// 		if(!node.isNested()) {
// 			out.append("\n");
// 		}
 	}

	public function visitListBlock($node)
	{
		$node->childrenAccept($this);
 		if(!$node->isLastChild()) {
 			$this->indent();
 			$this->out .= "\n";
 			$next = $node->next();
 			if(get_class($next) === 'Koara\Ast\ListBlock' && $next->isOrdered() == $node->isOrdered()) {
 				$this->out .= "\n";
 			}
 		}
 	}

	public function visitListItem($node)
	{
 		if(!$node->getParent()->isNested() || !$node->isFirstChild() || !$node->getParent()->isFirstChild()) {
 			$this->indent();
 		}
 		$this->left[] = "  ";
 		if($node->getNumber() != null) {			
 			$this->out .= $node->getNumber().".";
 		} else {
 			$this->out .= "-";
 		}
 		if($node->hasChildren()) {
 			$this->out .= " ";
 			$node->childrenAccept($this);
 		} else {
 			$this->out .= "\n";
 		}
 		array_pop($this->left);
 	}

 	public function visitCodeBlock($node)
	{
// 		StringBuilder indent = new StringBuilder();
// 		for(String s : left) {
// 			indent.append(s);
// 		}
		
// 		out.append("```");
// 		if(node.getLanguage() != null) {
// 			out.append(node.getLanguage());
// 		}
// 		out.append("\n");
	}
		
		
// 		out.append(node.getValue().toString().replaceAll("(?m)^", indent.toString()));
// 		out.append("\n");
// 		indent();
// 		out.append("```");
		
// 		out.append("\n");
// 		out.append("\n");
// 	}

	public function visitParagraph($node)
	{
 		if(!$node->isFirstChild()) {
 			$this->indent();
 		}
 		$node->childrenAccept($this);
 		$this->out .= "\n";
 		
 		if(!$node->isNested() || (get_class($node->getParent()) === 'Koara\Ast\ListItem' && (get_class($node->next()) === 'Koara\Ast\Paragraph') && !$node->isLastChild())) {
 			$this->out .= "\n";
 		} else if(get_class($node->getParent()) === 'Koara\Ast\BlockQuote' && (get_class($node->next()) === 'Koara\Ast\Paragraph')) {
 			indent();
 			$this->out .= "\n";
 		}
 	}

 	public function visitBlockElement($node)
	{
// 		if(!node.isFirstChild()) {
// 			indent();
// 		}
// 		node.childrenAccept(this);
// 		out.append("\n");
// 		if(!node.isNested() || (node.getParent() instanceof ListItem && (node.next() instanceof Paragraph) && !node.isLastChild())) {
// 			out.append("\n");
// 		} else if(node.getParent() instanceof BlockQuote && (node.next() instanceof Paragraph)) {
// 			indent();
// 			out.append("\n");
// 		}
 	}

 	public function visitImage($node)
	{
 		$this->out .= "[image: ";
 		$node->childrenAccept($this);
 		$this->out .= "]";
 		if($node->getValue() != null && strlen(trim($node->getValue())) > 0) {
 			$this->out .= "(";
 			$this->out .= $this->escapeUrl($node->getValue());
 			$this->out .= ")";
 		}
 	}

 	public function visitLink($node)
	{
 		$this->out .= "[";
 		$node->childrenAccept($this);
 		$this->out .= "]";
 		if($node->getValue() != null && strlen(trim($node->getValue())) > 0) {
 			$this->out .= "(";
 			$this->out .= $this->escapeUrl($node->getValue());
 			$this->out .= ")";
 		}
 	}

 	public function visitText($node)
	{
// 		if(node.getParent() instanceof Code) {
// 			out.append(node.getValue().toString());
// 		} else {
 			$this->out .= $this->escape($node->getValue());
// 		}
 	}

 	public function visitStrong($node)
	{
// 		out.append("*");
// 		node.childrenAccept(this);
// 		out.append("*");
 	}

 	public function visitEm($node)
	{
// 		out.append("_");
// 		node.childrenAccept(this);
// 		out.append("_");
 	}

 	public function visitCode($node)
	{
// 		out.append("`");
// 		node.childrenAccept(this);
// 		out.append("`");
 	}

 	public function visitLineBreak($node)
	{
 		$this->out .= "\n";
 		$this->indent();
 	}
	
 	public function escapeUrl($text) {
 		return str_replace(
 				array("(", ")"),
 				array("\(", "\)"),
 				$text
 		);
 	}
	
 	public function escape($text) {
 		$text = str_replace(
 			array("[", "]", "*", "_"),
 			array("\[", "\]", "\*", "\_"),
 			$text
 		);
 		$text = preg_replace("/`/", "\`", $text, 1);
 		$text = preg_replace("/=/", "\=", $text, 1);
 		$text = preg_replace("/>/", "\>", $text, 1);
 		$text = preg_replace("/-/", "\-", $text, 1);
 		$text = preg_replace("/(\d)\./", "\\\\$1.", $text, 1); //backlash
 		return $text;
 	}
	
 	private function indent() {
 		for($i = 0; $i < sizeof($this->left); $i++) {
 			$this->out .= $this->left[$i];
 		}
 	}

 	public function getOutput() {
 		return trim($this->out);
 	}
	
	
}
