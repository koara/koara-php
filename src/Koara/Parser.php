<?php 
namespace Koara;

use Koara\Ast\BlockQuote;
use Koara\Ast\BlockElement;
use Koara\Ast\Code;
use Koara\Ast\CodeBlock;
use Koara\Ast\Document;
use Koara\Ast\Em;
use Koara\Ast\Heading;
use Koara\Ast\Image;
use Koara\Ast\ListBlock;
use Koara\Ast\ListItem;
use Koara\Ast\LineBreak;
use Koara\Ast\Link;
use Koara\Ast\Paragraph;
use Koara\Ast\Strong;
use Koara\Ast\Text;
use Koara\Io\FileReader;
use Koara\Io\StringReader;
use Koara\Io\Reader;
use Koara\LookaheadSuccess;

class Parser {

 	private $cs;
 	private $token;
 	private $nextToken;
 	private $scanPosition;
 	private $lastPosition;
 	private $tm;
 	private $tree;
 	private $currentBlockLevel;
 	private $currentQuoteLevel;
 	private $lookAhead;
 	private $nextTokenKind;
 	private $lookingAhead;
 	private $semanticLookAhead;	
 	private $lookAheadSuccess;
 	private $modules;

 	public function __construct() {
 		$this->lookAheadSuccess = new LookaheadSuccess();
 		$this->modules = array("paragraphs", "headings", "lists", "links", "images", "formatting", "blockquotes", "code");
 	}
 	
 	/**
 	 * @return Document
 	 */
	public function parse($text) {
		return $this->parserReader(new StringReader($text));
	}

	/**
	 * @return Document
	 */
	public function parseFile($fileName) {
		if(strtolower(substr($fileName, strlen($fileName) - 3)) != '.kd') {
		throw new \InvalidArgumentException("Can only parse files with extension .kd");
		}
		
		return $this->parserReader(new FileReader($fileName));
	}
	
 	private function parserReader(Reader $reader) {
  		$this->cs = new CharStream($reader);
  		$this->tm = new TokenManager($this->cs);
  		$this->token = new Token();
 		$this->tree = new TreeState();
  		$this->nextTokenKind = -1;

  		$document = new Document(); 		
  		$this->tree->openScope();	 		
 			
  		while ($this->getNextTokenKind() == TokenManager::EOL) {
  			$this->consumeToken(TokenManager::EOL);
  		}
  		$this->whiteSpace();
  		if ($this->hasAnyBlockElementsAhead()) {
 			$this->blockElement(); 			
 			while ($this->blockAhead(0)) {
 				while ($this->getNextTokenKind() == TokenManager::EOL) {
 					$this->consumeToken(TokenManager::EOL);
 					$this->whiteSpace();			
 				}
 				$this->blockElement();
 			}
			while($this->getNextTokenKind() == TokenManager::EOL) {
 				$this->consumeToken(TokenManager::EOL);
 			}
 			$this->whiteSpace();
 		} 
 		$this->consumeToken(TokenManager::EOF);
 		$this->tree->closeScope($document);
 		return $document;
 	}
	
 	private function blockElement() {
 		$this->currentBlockLevel++;
 		if (in_array("headings", $this->modules) && $this->headingAhead(1)) {
 			$this->heading();
 		} else if(in_array("blockquotes", $this->modules) && $this->getNextTokenKind() == TokenManager::GT) {
 			$this->blockquote();
 		} else if(in_array("lists", $this->modules) && $this->getNextTokenKind() == TokenManager::DASH) {
 			$this->unorderedList();
 		} else if(in_array("lists", $this->modules) && $this->hasOrderedListAhead()) {
 			$this->orderedList();
 		} else if(in_array("code", $this->modules) && $this->hasFencedCodeBlockAhead()) {
 			$this->fencedCodeBlock();
 		} else {
 			$this->paragraph();
 		}
 		$this->currentBlockLevel--;
 	}
	
 	private function heading() {
 		$heading = new Heading();
 		$this->tree->openScope();
 		$headingLevel = 0;

 		while($this->getNextTokenKind() == TokenManager::EQ) {
 			$this->consumeToken(TokenManager::EQ);
 			$headingLevel++;
 		}
 		$this->whiteSpace();
 	    while ($this->headingHasInlineElementsAhead()) {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array("images", $this->modules) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array("links", $this->modules) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array("formatting", $this->modules) && $this->hasStrongAhead()) {
 				$this->strong();
 			} else if (in_array("formatting", $this->modules) && $this->hasEmAhead()) {
 				$this->em();
 			} else if (in_array("code", $this->modules) && $this->hasCodeAhead()) {
 				$this->code();
 			} else {
				$this->looseChar();
 			}
 		}
 		$heading->setValue($headingLevel);
 		$this->tree->closeScope($heading);
 	}
	
	private function blockquote() {
 		$blockquote = new BlockQuote();
 		$this->tree->openScope();
 		$this->currentQuoteLevel++;
 		$this->consumeToken(TokenManager::GT);
 		while ($this->blockquoteHasEmptyLineAhead()) {
 			$this->blockquoteEmptyLine();
 		}
 		$this->whiteSpace(); 
 		if ($this->blockquoteHasAnyBlockElementseAhead()) {
 			$this->blockElement();
 			while ($this->blockAhead(0)) {
 				while ($this->getNextTokenKind() == TokenManager::EOL) {
 					$this->consumeToken(TokenManager::EOL);
 					$this->whiteSpace();
 					$this->blockquotePrefix();
 				}
 				$this->blockElement();
 			}
 		}
 		while ($this->hasBlockQuoteEmptyLinesAhead()) {
 			$this->blockquoteEmptyLine();
 		}
 		$this->currentQuoteLevel--;
 		$this->tree->closeScope($blockquote);
 	}
		
 	private function blockquotePrefix() {
 		$i = 0;
 		do {
 			$this->consumeToken(TokenManager::GT);
 			$this->whiteSpace();	
 		} while(++$i < $this->currentQuoteLevel);
 	}

 	private function blockquoteEmptyLine() {
 		$this->consumeToken(TokenManager::EOL);
 		$this->whiteSpace();
 		do {
 			$this->consumeToken(TokenManager::GT);
 			$this->whiteSpace();
 		} while($this->getNextTokenKind() == TokenManager::GT);
 	}
	
 	private function unorderedList() {
 		$list = new ListBlock(false);
 		$this->tree->openScope();
 		$listBeginColumn = $this->unorderedListItem();
 		
 		while ($this->listItemAhead($listBeginColumn, false)) {
 			while ($this->getNextTokenKind() == TokenManager::EOL) {
 				$this->consumeToken(TokenManager::EOL);
 			}
 			$this->whiteSpace();
 			if ($this->currentQuoteLevel > 0) {
 				$this->blockquotePrefix();
			}
 			$this->unorderedListItem();
		}
 		$this->tree->closeScope($list);
 	}

 	private function unorderedListItem() {
 		$listItem = new ListItem();
 		$this->tree->openScope();
 		$t = $this->consumeToken(TokenManager::DASH);
 		
 		$this->whiteSpace();
 		if ($this->listItemHasInlineElements()) { 
 			$this->blockElement();
 			while ($this->blockAhead($t->beginColumn)) {
 				while ($this->getNextTokenKind() == TokenManager::EOL) {
 					$this->consumeToken(TokenManager::EOL);
 					$this->whiteSpace();
 					if ($this->currentQuoteLevel > 0) {
 						$this->blockquotePrefix();
					}
 				}
 				$this->	blockElement();
 			}
 		}
 		$this->tree->closeScope($listItem);
		return $t->beginColumn;
 	}

 	private function orderedList() {
 		$list = new ListBlock(true);
 		$this->tree->openScope();
 		$listBeginColumn = $this->orderedListItem();
 		while ($this->listItemAhead($listBeginColumn, true)) {
 			while ($this->getNextTokenKind() == TokenManager::EOL) {
 				$this->consumeToken(TokenManager::EOL);
 			}
 			$this->whiteSpace();
 			if ($this->currentQuoteLevel > 0) {
 				$this->blockquotePrefix();
 			}
 			$this->orderedListItem();
 		}
 		$this->tree->closeScope($list);
 	}

 	private function orderedListItem() {
		$listItem = new ListItem();
 		$this->tree->openScope($listItem);
 		$t = $this->consumeToken(TokenManager::DIGITS);
 		$this->consumeToken(TokenManager::DOT);
 		$this->whiteSpace();
 		if ($this->listItemHasInlineElements()) { 
 			$this->blockElement();
 			while ($this->blockAhead($t->beginColumn)) {
 				while ($this->getNextTokenKind() == TokenManager::EOL) {
					$this->consumeToken(TokenManager::EOL);
 					$this->whiteSpace();
 					if ($this->currentQuoteLevel > 0) {
 						$this->blockquotePrefix();
 					}
 				}
 				$this->blockElement();
 			}
 		}
 		$listItem->setNumber(intval($t->image));
 		$this->tree->closeScope($listItem);
 		return $t->beginColumn;
 	}

 	private function fencedCodeBlock() {
 		$codeBlock = new CodeBlock();
 		$this->tree->openScope();
 		$s='';
 		$beginColumn = $this->consumeToken(TokenManager::BACKTICK)->beginColumn;
 		do {
 			$this->consumeToken(TokenManager::BACKTICK);
 		} while($this->getNextTokenKind() == TokenManager::BACKTICK);
 		$this->whiteSpace();
 		if ($this->getNextTokenKind() == TokenManager::CHAR_SEQUENCE) {
 			$codeBlock->setLanguage($this->codeLanguage()); 
 		}
 		if ($this->getNextTokenKind() != TokenManager::EOF && !$this->fencesAhead()) {
 			$this->consumeToken(TokenManager::EOL);
 			$this->levelWhiteSpace($beginColumn);
 		}
 		while ($this->fencedCodeBlockHasInlineTokens()) {
 			switch ($this->getNextTokenKind()) {
			case TokenManager::CHAR_SEQUENCE: 	$s .= $this->consumeToken(TokenManager::CHAR_SEQUENCE)->image; break;
 			case TokenManager::ASTERISK: 		$s .= $this->consumeToken(TokenManager::ASTERISK)->image; break;
 			case TokenManager::BACKSLASH: 		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
			case TokenManager::COLON: 			$s .= $this->consumeToken(TokenManager::COLON)->image; break;
			case TokenManager::DASH: 			$s .= $this->consumeToken(TokenManager::DASH)->image; break;
			case TokenManager::DIGITS: 			$s .= $this->consumeToken(TokenManager::DIGITS)->image; break;
			case TokenManager::DOT: 			$s .= $this->consumeToken(TokenManager::DOT)->image; break;
			case TokenManager::EQ: 				$s .= $this->consumeToken(TokenManager::EQ)->image; break;
			case TokenManager::ESCAPED_CHAR: 	$s .= $this->consumeToken(TokenManager::ESCAPED_CHAR)->image; break;
			case TokenManager::IMAGE_LABEL: 	$s .= $this->consumeToken(TokenManager::IMAGE_LABEL)->image; break;
			case TokenManager::LT: 				$s .= $this->consumeToken(TokenManager::LT)->image; break;
			case TokenManager::GT: 				$s .= $this->consumeToken(TokenManager::GT)->image; break;
			case TokenManager::LBRACK:			$s .= $this->consumeToken(TokenManager::LBRACK)->image; break;
			case TokenManager::RBRACK:			$s .= $this->consumeToken(TokenManager::RBRACK)->image; break;
			case TokenManager::LPAREN:			$s .= $this->consumeToken(TokenManager::LPAREN)->image; break;
			case TokenManager::RPAREN:			$s .= $this->consumeToken(TokenManager::RPAREN)->image; break;
			case TokenManager::UNDERSCORE:		$s .= $this->consumeToken(TokenManager::UNDERSCORE)->image; break;
			case TokenManager::BACKTICK:		$s .= $this->consumeToken(TokenManager::BACKTICK)->image; break;
 			default:
 				if (!$this->nextAfterSpace(array(TokenManager::EOL, TokenManager::EOF))) {
 					switch ($this->getNextTokenKind()) {
 					case TokenManager::SPACE: 	$s .= $this->consumeToken(TokenManager::SPACE)->image; break;
 					case TokenManager::TAB: 	$this->consumeToken(TokenManager::TAB); $s .= '    '; break;
 					}
 				} else if (!$this->fencesAhead()) {
 					$this->consumeToken(TokenManager::EOL);
 					$s .= "\n";
 					$this->levelWhiteSpace($beginColumn);
 				}
 			}
 		}
 		if ($this->fencesAhead()) {
 			$this->consumeToken(TokenManager::EOL);
 			$this->blockQuotePrefix();
 			$this->whiteSpace();
 			
 			while ($this->getNextTokenKind() == TokenManager::BACKTICK) {
 				$this->consumeToken(TokenManager::BACKTICK);
 			}
 		}
 		$codeBlock->setValue($s);
 		$this->tree->closeScope($codeBlock);
 	}
	
 	private function paragraph() {
 		$paragraph = null;
 		if(in_array("paragraphs", $this->modules)) {
 			$paragraph = new Paragraph();			
 		} else {
 			$paragraph = new BlockElement();
 		}
		
 		$this->tree->openScope();
 		$this->inline();
 		while ($this->textAhead()) {
 			$this->lineBreak();
 			$this->whiteSpace();
 			if(in_array("blockquotes", $this->modules)) {
 				while ($this->getNextTokenKind() == TokenManager::GT) {
 					$this->consumeToken(TokenManager::GT);
 					$this->whiteSpace();
 					$this->blockquotePrefix();
 				}
 			}
 			$this->inline();
 		}
 		$this->tree->closeScope($paragraph);
 	}
	
 	private function text() {
 		$text = new Text();
 		$this->tree->openScope($text);
		$s = '';
 		while ($this->textHasTokensAhead()) {
 			switch ($this->getNextTokenKind()) {
			case TokenManager::CHAR_SEQUENCE:	$s .= $this->consumeToken(TokenManager::CHAR_SEQUENCE)->image; break;
 			case TokenManager::BACKSLASH: 		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
			case TokenManager::COLON: 			$s .= $this->consumeToken(TokenManager::COLON)->image; break;
			case TokenManager::DASH: 			$s .= $this->consumeToken(TokenManager::DASH)->image; break;
			case TokenManager::DIGITS: 			$s .= $this->consumeToken(TokenManager::DIGITS)->image; break;
			case TokenManager::DOT: 			$s .= $this->consumeToken(TokenManager::DOT)->image; break;
			case TokenManager::EQ: 				$s .= $this->consumeToken(TokenManager::EQ)->image; break;
			case TokenManager::ESCAPED_CHAR:	$s .= mb_substr($this->consumeToken(TokenManager::ESCAPED_CHAR)->image, 1); break;
			case TokenManager::GT: 				$s .= $this->consumeToken(TokenManager::GT)->image; break;
			case TokenManager::IMAGE_LABEL: 	$s .= $this->consumeToken(TokenManager::IMAGE_LABEL)->image; break;
			case TokenManager::LPAREN: 			$s .= $this->consumeToken(TokenManager::LPAREN)->image; break;
			case TokenManager::LT: 				$s .= $this->consumeToken(TokenManager::LT)->image; break;
			case TokenManager::RBRACK: 			$s .= $this->consumeToken(TokenManager::RBRACK)->image; break;
			case TokenManager::RPAREN: 			$s .= $this->consumeToken(TokenManager::RPAREN)->image; break;
 			default:
 				if (!$this->nextAfterSpace(array(TokenManager::EOL, TokenManager::EOF))) {
 					switch ($this->getNextTokenKind()) {
 					case TokenManager::SPACE:	$s .= $this->consumeToken(TokenManager::SPACE)->image; break;
 					case TokenManager::TAB:		$this->consumeToken(TokenManager::TAB); $s .= '    '; break;
 					}
 				}
 			}
 		}

 		$text->setValue($s);
 		$this->tree->closeScope($text);
 	}
	
 	private function image() {
 		$image = new Image();
 		$this->tree->openScope();
 		$ref = '';
 		$this->consumeToken(TokenManager::LBRACK);
 		$this->whiteSpace();
 		$this->consumeToken(TokenManager::IMAGE_LABEL);
 		$this->whiteSpace();
 		while ($this->imageHasAnyElements()) {
 			if ($this->hasTextAhead()) {
 				$this->resourceText();
 			} else {
 				$this->looseChar();
 			}
 		}
 		$this->whiteSpace();
 		$this->consumeToken(TokenManager::RBRACK);
 		if ($this->hasResourceUrlAhead()) {
 			$ref = $this->resourceUrl();
 		}
		$image->setValue($ref);
 		$this->tree->closeScope($image);
 	}
	
 	private function link() {
 		$link = new Link();
 		$this->tree->openScope();
 		$ref = '';
 		$this->consumeToken(TokenManager::LBRACK);
 		$this->whiteSpace();
 		while ($this->linkHasAnyElements()) {
 			if (in_array("images", $this->modules) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array("formatting", $this->modules) && $this->hasStrongAhead()) {
 				$this->strong();
 			} else if (in_array("formatting", $this->modules) && $this->hasEmAhead()) {
 				$this->em();
 			} else if (in_array("code", $this->modules) && $this->hasCodeAhead()) {
 				$this->code();
 			} else if ($this->hasResourceTextAhead()) {
 				$this->resourceText();
 			} else {
 				$this->looseChar();
 			}
 		}
 		$this->whiteSpace();
 		$this->consumeToken(TokenManager::RBRACK);
		if ($this->hasResourceUrlAhead()) {
 			$ref = $this->resourceUrl();
 		}
 		$link->setValue($ref);
 		$this->tree->closeScope($link);
 	}
	
 	private function strong() {
 		$strong = new Strong();
 		$this->tree->openScope();
 		$this->consumeToken(TokenManager::ASTERISK);
 		while ($this->strongHasElements()) {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array("images", $this->modules) && $this->hasImage()) {
 				$this->image();
 			} else if (in_array("links", $this->modules) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array("code", $this->modules) && $this->multilineAhead(TokenManager::BACKTICK)) {
 				$this->codeMultiline();
 			} else if ($this->strongEmWithinStrongAhead()) {
 				$this->emWithinStrong();
 			} else {
 				switch ($this->getNextTokenKind()) {
 				case TokenManager::BACKTICK: 	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::BACKTICK)); break;
 				case TokenManager::LBRACK:		$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::LBRACK)); break;
 				case TokenManager::UNDERSCORE:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::UNDERSCORE)); break;
 				}
 			}
 		}
 		$this->consumeToken(TokenManager::ASTERISK);
 		$this->tree->closeScope($strong);
 	}
	
 	private function em() {
 		$em = new Em();
 		$this->tree->openScope();
 		$this->consumeToken(TokenManager::UNDERSCORE);
 		while ($this->emHasElements()) {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array("images", $this->modules) && $this->hasImage()) {
 				$this->image();
 			} else if (in_array("links", $this->modules) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array("code", $this->modules) && $this->hasCodeAhead()) {
 				$this->code();
 			} else if ($this->emHasStrongWithinEm()) {
 				$this->strongWithinEm();
 			} else {
 				switch ($this->getNextTokenKind()) {
 				case TokenManager::ASTERISK:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::ASTERISK)); break;
 				case TokenManager::BACKTICK:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::BACKTICK)); break;
 				case TokenManager::LBRACK: 		$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::LBRACK));	break;
 				}
 			}
 		}
 		$this->consumeToken(TokenManager::UNDERSCORE);
 		$this->tree->closeScope($em);
 	}

 	private function code() {
 		$code = new Code();
 		$this->tree->openScope();
 		$this->consumeToken(TokenManager::BACKTICK);
 		$this->codeText();
 		$this->consumeToken(TokenManager::BACKTICK);
 		$this->tree->closeScope($code);
 	}

 	private function codeText() {
 		$text = new Text();
 		$this->tree->openScope();
 		$s='';
 		do {
 			switch ($this->getNextTokenKind()) {
 			case TokenManager::CHAR_SEQUENCE: 	$s .= $this->consumeToken(TokenManager::CHAR_SEQUENCE)->image; break;
 			case TokenManager::ASTERISK: 		$s .= $this->consumeToken(TokenManager::ASTERISK)->image; break;
 			case TokenManager::BACKSLASH: 		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
 			case TokenManager::COLON: 			$s .= $this->consumeToken(TokenManager::COLON)->image; break;
 			case TokenManager::DASH: 			$s .= $this->consumeToken(TokenManager::DASH)->image; break;
 			case TokenManager::DIGITS:			$s .= $this->consumeToken(TokenManager::DIGITS)->image; break;
 			case TokenManager::DOT:				$s .= $this->consumeToken(TokenManager::DOT)->image; break;
 			case TokenManager::EQ:				$s .= $this->consumeToken(TokenManager::EQ)->image; break;
 			case TokenManager::ESCAPED_CHAR:	$s .= $this->consumeToken(TokenManager::ESCAPED_CHAR)->image; break;
 			case TokenManager::IMAGE_LABEL:		$s .= $this->consumeToken(TokenManager::IMAGE_LABEL)->image; break;
 			case TokenManager::LT:				$s .= $this->consumeToken(TokenManager::LT)->image; break;
 			case TokenManager::LBRACK:			$s .= $this->consumeToken(TokenManager::LBRACK)->image; break;
 			case TokenManager::RBRACK:			$s .= $this->consumeToken(TokenManager::RBRACK)->image; break;
 			case TokenManager::LPAREN:			$s .= $this->consumeToken(TokenManager::LPAREN)->image; break;
 			case TokenManager::GT:				$s .= $this->consumeToken(TokenManager::GT)->image; break;
 			case TokenManager::RPAREN:			$s .= $this->consumeToken(TokenManager::RPAREN)->image; break;
 			case TokenManager::UNDERSCORE:		$s .= $this->consumeToken(TokenManager::UNDERSCORE)->image; break;
 			default:
 				if (!$this->nextAfterSpace(array(TokenManager::EOL, TokenManager::EOF))) {
 					switch ($this->getNextTokenKind()) {
 					case TokenManager::SPACE:	$s .= $this->consumeToken(TokenManager::SPACE)->image; break;
 					case TokenManager::TAB: 	$this->consumeToken(TokenManager::TAB); $s .= '    '; break;
 					}
 				}
 			}
 		} while($this->codeTextHasAnyTokenAhead());
 		$text->setValue($s);
 		$this->tree->closeScope($text);
 	}

 	private function looseChar() {
 		$text = new Text();
		$this->tree->openScope();
 		switch ($this->getNextTokenKind()) {
 		case TokenManager::ASTERISK:		$text->setValue($this->consumeToken(TokenManager::ASTERISK)->image); break;
 		case TokenManager::BACKTICK:		$text->setValue($this->consumeToken(TokenManager::BACKTICK)->image); break;
 		case TokenManager::LBRACK:			$text->setValue($this->consumeToken(TokenManager::LBRACK)->image); break;
 		case TokenManager::UNDERSCORE:		$text->setValue($this->consumeToken(TokenManager::UNDERSCORE)->image); break;		
 		}
 		$this->tree->closeScope($text);
 	}

 	private function lineBreak() {
 		$linebreak = new LineBreak();
 		$this->tree->openScope();
 		while ($this->getNextTokenKind() == TokenManager::SPACE || $this->getNextTokenKind() == TokenManager::TAB) {
 			$this->consumeToken($this->getNextTokenKind());
 		}
 		$token = $this->consumeToken(TokenManager::EOL);
 		
 		//substr($token->image, 2) == "  "
 		
        $linebreak->setExplicit(true);
 		$this->tree->closeScope($linebreak);
 	}
	
 	private function levelWhiteSpace($threshold) {
 		$currentPos = 1;
 	    while($this->getNextTokenKind() == TokenManager::GT) {
 	    	$this->consumeToken($this->getNextTokenKind());
 	    }
 		while (($this->getNextTokenKind() == TokenManager::SPACE || $this->getNextTokenKind() == TokenManager::TAB) && $currentPos < ($threshold - 1)) {
 			$currentPos = $this->consumeToken($this->getNextTokenKind())->beginColumn;
 		}
 	}

 	private function codeLanguage() {
 		$s='';
 		do {
 			switch ($this->getNextTokenKind()) {
 			case TokenManager::CHAR_SEQUENCE:	$s .= $this->consumeToken(TokenManager::CHAR_SEQUENCE)->image; break;
 			case TokenManager::ASTERISK: 		$s .= $this->consumeToken(TokenManager::ASTERISK)->image; break;
 			case TokenManager::BACKSLASH: 		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
 			case TokenManager::BACKTICK: 		$s .= $this->consumeToken(TokenManager::BACKTICK)->image; break;
 			case TokenManager::COLON:			$s .= $this->consumeToken(TokenManager::COLON)->image; break;
 			case TokenManager::DASH:	 		$s .= $this->consumeToken(TokenManager::DASH)->image; break;
 			case TokenManager::DIGITS: 			$s .= $this->consumeToken(TokenManager::DIGITS)->image; break;
 			case Tokenmanager::DOT: 			$s .= $this->consumeToken(Tokenmanager::DOT)->image; break;
 			case TokenManager::EQ: 				$s .= $this->consumeToken(Tokenmanager::EQ)->image; break;
 			case TokenManager::ESCAPED_CHAR: 	$s .= $this->consumeToken(TokenManager::ESCAPED_CHAR)->image; break;
 			case Tokenmanager::IMAGE_LABEL: 	$s .= $this->consumeToken(TokenManager::IMAGE_LABEL)->image; break;
 			case TokenManager::LT: 				$s .= $this->consumeToken(TokenManager::LT)->image; break;
 			case TokenManager::GT: 				$s .= $this->consumeToken(TokenManager::GT)->image; break;
 			case TokenManager::LBRACK: 			$s .= $this->consumeToken(TokenManager::LBRACK)->image; break;
 			case TokenManager::RBRACK: 			$s .= $this->consumeToken(TokenManager::RBRACK)->image; break;
 			case TokenManager::LPAREN: 			$s .= $this->consumeToken(TokenManager::LPAREN)->image; break;
 			case TokenManager::RPAREN: 			$s .= $this->consumeToken(TokenManager::RPAREN)->image; break;
 			case TokenManager::UNDERSCORE: 		$s .= $this->consumeToken(TokenManager::UNDERSCORE)->image; break;
 			case TokenManager::SPACE: 			$s .= $this->consumeToken(TokenManager::SPACE)->image; break;
 			case TokenManager::TAB: 			$s .= '    '; break;
 			default: break;
 			}
 		} while ($this->getNextTokenKind() != TokenManager::EOL && $this->getNextTokenKind() != TokenManager::EOF);
 		return $s;
 	}

 	private function inline() {
 		do {
 			if ($this->hasInlineTextAhead()) {
 				$this->text();
 			} else if (in_array("images", $this->modules) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array("links", $this->modules) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array("formatting", $this->modules) && $this->multilineAhead(TokenManager::ASTERISK)) {
 				$this->strongMultiline();
 			} else if (in_array("formatting", $this->modules) && $this->multilineAhead(TokenManager::UNDERSCORE)) {
 				$this->emMultiline();
 			} else if (in_array("code", $this->modules) && $this->multilineAhead(TokenManager::BACKTICK)) {
 				$this->codeMultiline();
 			} else {
 				$this->looseChar();
 			}
 		} while ($this->hasInlineElementAhead());
 	}

 	private function resourceText() {
 		$text = new Text();
 		$this->tree->openScope();
		$s='';
 		do {
 			switch ($this->getNextTokenKind()) {
 			case TokenManager::CHAR_SEQUENCE:	$s .= $this->consumeToken(TokenManager::CHAR_SEQUENCE)->image; break;
 			case TokenManager::BACKSLASH:		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
 			case TokenManager::COLON:			$s .= $this->consumeToken(TokenManager::COLON)->image; break;
 			case TokenManager::DASH:			$s .= $this->consumeToken(TokenManager::DASH)->image; break;
 			case TokenManager::DIGITS:			$s .= $this->consumeToken(TokenManager::DIGITS)->image; break;
 			case TokenManager::DOT:				$s .= $this->consumeToken(TokenManager::DOT)->image; break;
 			case TokenManager::EQ:				$s .= $this->consumeToken(TokenManager::EQ)->image; break;
 			case TokenManager::ESCAPED_CHAR:	$s .= mb_substr($this->consumeToken(TokenManager::ESCAPED_CHAR)->image, 1); break;
 			case TokenManager::IMAGE_LABEL:		$s .= $this->consumeToken(TokenManager::IMAGE_LABEL)->image; break;
 			case TokenManager::GT:				$s .= $this->consumeToken(TokenManager::GT)->image; break;
 			case TokenManager::LPAREN:			$s .= $this->consumeToken(TokenManager::LPAREN)->image; break;
 			case TokenManager::LT:				$s .= $this->consumeToken(TokenManager::LT)->image; break;
 			case TokenManager::RPAREN:			$s .= $this->consumeToken(TokenManager::RPAREN)->image; break;
 			default:
 				if (!$this->nextAfterSpace(array(TokenManager::RBRACK))) {
 					switch ($this->getNextTokenKind()) {
 					case TokenManager::SPACE:	$s .= $this->consumeToken(TokenManager::SPACE)->image; break;
 					case TokenManager::TAB:		$this->consumeToken(TokenManager::TAB); $s .= '    '; break;
 					}
 				}
 			}
 		} while($this->resourceHasElementAhead());
 		$text->setValue($s);
 		$this->tree->closeScope($text);
 	}

 	private function resourceUrl() {
 		$this->consumeToken(TokenManager::LPAREN);
 		$this->whiteSpace();
 		$ref = $this->resourceUrlText();
 		$this->whiteSpace();
 		$this->consumeToken(TokenManager::RPAREN);
 		return $ref;
 	}

 	private function resourceUrlText() {
 		$s='';
 		while ($this->resourceTextHasElementsAhead()) {
 			switch ($this->getNextTokenKind()) {
 			case TokenManager::CHAR_SEQUENCE:	$s .= $this->consumeToken(TokenManager::CHAR_SEQUENCE)->image; break;
 			case TokenManager::ASTERISK: 		$s .= $this->consumeToken(TokenManager::ASTERISK)->image; break;
 			case TokenManager::BACKSLASH:		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
 			case TokenManager::BACKTICK:		$s .= $this->consumeToken(TokenManager::BACKTICK)->image; break;
 			case TokenManager::COLON:			$s .= $this->consumeToken(TokenManager::COLON)->image; break;
 			case TokenManager::DASH:			$s .= $this->consumeToken(TokenManager::DASH)->image; break;
 			case TokenManager::DIGITS:			$s .= $this->consumeToken(TokenManager::DIGITS)->image; break;
 			case TokenManager::DOT:				$s .= $this->consumeToken(TokenManager::DOT)->image; break;
 			case TokenManager::EQ:				$s .= $this->consumeToken(TokenManager::EQ)->image; break;
 			case TokenManager::ESCAPED_CHAR:	$s .= mb_substr($this->consumeToken(TokenManager::ESCAPED_CHAR)->image, 1); break;
 			case TokenManager::IMAGE_LABEL:		$s .= $this->consumeToken(TokenManager::IMAGE_LABEL)->image; break;
 			case TokenManager::GT:				$s .= $this->consumeToken(TokenManager::GT)->image; break;
 			case TokenManager::LBRACK:			$s .= $this->consumeToken(TokenManager::LBRACK)->image; break;
 			case TokenManager::LPAREN:			$s .= $this->consumeToken(TokenManager::LPAREN)->image; break;
 			case TokenManager::LT:				$s .= $this->consumeToken(TokenManager::LT)->image; break;
 			case TokenManager::RBRACK:			$s .= $this->consumeToken(TokenManager::RBRACK)->image; break;
 			case TokenManager::UNDERSCORE:		$s .= $this->consumeToken(TokenManager::UNDERSCORE)->image; break;
 			default:
 				if (!$this->nextAfterSpace(array(TokenManager::RPAREN))) {
 					switch ($this->getNextTokenKind()) {
 					case TokenManager::SPACE:		$s .= $this->consumeToken(TokenManager::SPACE)->image; break;
 					case TokenManager::TAB:			$this->consumeToken(TokenManager::TAB); $s .= '    '; break;
					}
 				}
 			}
 		}
 		return $s;
 	}

 	private function strongMultiline() {
 		$strong = new Strong();
 		$this->tree->openScope();
 		$this->consumeToken(TokenManager::ASTERISK);
 		$this->strongMultilineContent();
 		while ($this->textAhead()) {
 			$this->lineBreak();
 			$this->whiteSpace();
 			$this->strongMultilineContent();
 		}
 		$this->consumeToken(TokenManager::ASTERISK);
 		$this->tree->closeScope($strong);
 	}

 	private function strongMultilineContent() {
 		do {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array("images", $this->modules) && $this->hasImageAhead()) {
				$this->image();
 			} else if (in_array("links", $this->modules) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array("code", $this->modules) && $this->hasCodeAhead()) {
 				$this->code();
 			} else if ($this->hasEmWithinStrongMultiline()) {
 				$this->emWithinStrongMultiline();
 			} else {
 				switch ($this->getNextTokenKind()) {
 				case TokenManager::BACKTICK:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::BACKTICK)); break;
 				case TokenManager::LBRACK:		$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::LBRACK)); break;
 				case TokenManager::UNDERSCORE:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::UNDERSCORE)); break;
 				}
 			}
 		} while($this->strongMultilineHasElementsAhead());
 	}

 	private function strongWithinEmMultiline() {
 		$strong = new Strong();
 		$this->tree->openScope();
 		$this->consumeToken(TokenManager::ASTERISK);
 		$this->strongWithinEmMultilineContent();
 		while ($this->textAhead()) {
 			$this->lineBreak();
 			$this->whiteSpace();
 			$this->strongWithinEmMultilineContent();
 		}
 		$this->consumeToken(TokenManager::ASTERISK);
 		$this->tree->closeScope($strong);
 	}

 	private function strongWithinEmMultilineContent() {
 		do {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array("images", $this->modules) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array("links", $this->modules) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array("code", $this->modules) && $this->hasCodeAhead()) {
 				$this->code();
 			} else {
 				switch ($this->getNextTokenKind()) {
 				case TokenManager::BACKTICK:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::BACKTICK)); break;
 				case TokenManager::LBRACK:		$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::LBRACK)); break;
 				case TokenManager::UNDERSCORE:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::UNDERSCORE)); break;
 				}
 			}
 		} while($this->strongWithinEmMultilineHasElementsAhead());
 	}

 	private function strongWithinEm() {
 		$strong = new Strong();
 		$this->tree->openScope();
 		$this->consumeToken(TokenManager::ASTERISK);
 		do {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array("images", $this->modules) && $this->hasImageAhead()) {
				$this->image();
 			} else if (in_array("links", $this->modules) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array("code", $this->modules) && $this->hasCodeAhead()) {
 				$this->code();
 			} else {
 				switch ($this->getNextTokenKind()) {
 				case TokenManager::BACKTICK:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::BACKTICK)); break;
 				case TokenManager::LBRACK:		$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::LBRACK)); break;
 				case TokenManager::UNDERSCORE:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::UNDERSCORE)); break;
 				}
 			}
 		} while($this->strongWithinEmHasElementsAhead());
 		$this->consumeToken(TokenManager::ASTERISK);
 		$this->tree->closeScope($strong);
 	}

 	private function emMultiline() {
 		$em = new Em();
 		$this->tree->openScope();
		$this->consumeToken(TokenManager::UNDERSCORE);
 		$this->emMultilineContent();
 		while ($this->textAhead()) {
 			$this->lineBreak();
 			$this->whiteSpace();
 			$this->emMultilineContent();
 		}
 		$this->consumeToken(TokenManager::UNDERSCORE);
 		$this->tree->closeScope($em);
 	}

 	private function emMultilineContent() {
 		do {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array("images", $this->modules) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array("links", $this->modules) && $this->hasLinkAhead()) {
				$this->link();
 			} else if (in_array("code", $this->modules) && $this->multilineAhead(TokenManager::BACKTICK)) {
 				$this->codeMultiline();
 			} else if ($this->hasStrongWithinEmMultilineAhead()) {
 				$this->strongWithinEmMultiline();
 			} else {
 				switch ($this->getNextTokenKind()) {
 				case TokenManager::ASTERISK:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::ASTERISK)); break;
 				case TokenManager::BACKTICK:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::BACKTICK)); break;
 				case TokenManager::LBRACK:		$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::LBRACK)); break;
 				}
 			}
 		} while($this->emMultilineContentHasElementsAhead());
 	}

 	private function emWithinStrongMultiline() {
 		$em = new Em();
 		$this->tree->openScope();
 		$this->consumeToken(TokenManager::UNDERSCORE);
 		$this->emWithinStrongMultilineContent();
 		while ($this->textAhead()) {
 			$this->lineBreak();
 			$this->whiteSpace();
 			$this->emWithinStrongMultilineContent();
 		}
 		$this->consumeToken(TokenManager::UNDERSCORE);
 		$this->tree->closeScope($em);
 	}

 	private function emWithinStrongMultilineContent() {
 		do {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array("images", $this->modules) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array("links", $this->modules) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array("code", $this->modules) && $this->hasCodeAhead()) {
 				$this->code();
 			} else {
 				switch ($this->getNextTokenKind()) {
 				case TokenManager::ASTERISK: 	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::ASTERISK)); break;
 				case TokenManager::BACKTICK:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::BACKTICK)); break;
 				case TokenManager::LBRACK:		$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::LBRACK)); break;
 				}
 			}
 		} while ($this->emWithinStrongMultilineContentHasElementsAhaed());
 	}

 	private function emWithinStrong() {
 		$em = new Em();
 		$this->tree->openScope();
 		$this->consumeToken(TokenManager::UNDERSCORE);
 		do {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array("images", $this->modules) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array("links", $this->modules) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array("code", $this->modules) && $this->hasCodeAhead()) {
 				$this->code();
 			} else {
 				switch ($this->getNextTokenKind()) {
 				case TokenManager::ASTERISK:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::ASTERISK)); break;
 				case TokenManager::BACKTICK:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::BACKTICK)); break;
 				case TokenManager::LBRACK:		$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::LBRACK)); break;
 				}
 			}
 		} while($this->emWithinStrongHasElementsAhead());
 		$this->consumeToken(TokenManager::UNDERSCORE);
 		$this->tree->closeScope($em);
 	}

 	private function codeMultiline() {
 		$code = new Code();
 		$this->tree->openScope();
 		$this->consumeToken(TokenManager::BACKTICK);
 		$this->codeText();
 		while ($this->textAhead()) {
 			$this->lineBreak();
 			$this->whiteSpace();
 			while ($this->getNextTokenKind() == TokenManager::GT) {
 				$this->consumeToken(TokenManager::GT);
 				$this->whiteSpace();
 			}
 			$this->codeText();
 		}
 		$this->consumeToken(TokenManager::BACKTICK);
 		$this->tree->closeScope($code);
 	}
	
 	private function whiteSpace() {
 		while ($this->getNextTokenKind() == TokenManager::SPACE || $this->getNextTokenKind() == TokenManager::TAB) {
 			$this->consumeToken($this->getNextTokenKind());
 		}
 	}

 	private function hasAnyBlockElementsAhead() {
 		try {
 			$this->lookAhead = 1;
 			$this->lastPosition = $this->scanPosition = $this->token;
 			return !$this->scanMoreBlockElements();
 		} catch (LookaheadSuccess $ls) {
 			return true;
 		} 
 	}
	
 	 private function blockAhead($blockBeginColumn) {
 	 	$quoteLevel=0;
 	 	if($this->getToken(1)->kind == TokenManager::EOL) {
    		$t=null;
			$i = 2;
			$quoteLevel=0;
			do {
				$quoteLevel=0;
				do {
					$t = $this->getToken($i++);
					if($t->kind == TokenManager::GT) {
	            		if($t->beginColumn == 1 && $this->currentBlockLevel > 0 && $this->currentQuoteLevel == 0)  {
	        	 	   		return false;
	            		}
            			$quoteLevel++;
          			}
				} while($t->kind == TokenManager::GT || $t->kind == TokenManager::SPACE || $t->kind == TokenManager::TAB);
				if($quoteLevel > $this->currentQuoteLevel) {
	          		return true;
        		}
	        	if($quoteLevel < $this->currentQuoteLevel) {
	          		return false;
	        	}
    		} while($t->kind == TokenManager::EOL);
    		return $t->kind != TokenManager::EOF && ($this->currentBlockLevel == 0 || $t->beginColumn >= $blockBeginColumn + 2) ;
  		}
		return false;
 	}

	private function multilineAhead($token) {
		if($this->getToken(1)->kind == $token && $this->getToken(2)->kind != $token && $this->getToken(2)->kind != TokenManager::EOL) {
  		for($i=2;;$i++) {
			$t = $this->getToken($i);
			if($t->kind == $token) {
				return true;
			} else if($t->kind == TokenManager::EOL) {
				$i = $this->skip($i+1, array(TokenManager::SPACE, TokenManager::TAB));
				$quoteLevel = $this->newQuoteLevel($i);
				if($quoteLevel == $this->currentQuoteLevel) {
					$i = $this->skip($i, array(TokenManager::SPACE, TokenManager::TAB, TokenManager::GT));
					if($this->getToken($i)->kind == $token
						|| $this->getToken($i)->kind == TokenManager::EOL
						|| $this->getToken($i)->kind == TokenManager::DASH
						|| ($this->getToken($i)->kind == TokenManager::DIGITS && $this->getToken($i+1)->kind == TokenManager::DOT)
						|| ($this->getToken($i)->kind == TokenManager::BACKTICK && $this->getToken($i+1)->kind == TokenManager::BACKTICK && $this->getToken($i+2)->kind == TokenManager::BACKTICK)
						|| $this->headingAhead($i)) {
							return false;
						}
					} else {
						return false;
					}
				} else if($t->kind == TokenManager::EOF) {
					return false;
				}
			}
		}
		return false;
	}

	private function fencesAhead() {
		$i = $this->skip(2, array(TokenManager::SPACE, TokenManager::TAB, TokenManager::GT));
		if($this->getToken($i)->kind == TokenManager::BACKTICK && $this->getToken($i+1)->kind == TokenManager::BACKTICK && $this->getToken($i+2)->kind == TokenManager::BACKTICK) {
			$i = $this->skip($i+3, array(TokenManager::SPACE, TokenManager::TAB));
			$t = $this->getToken($i);
			return $t->kind == TokenManager::EOL || $t->kind == TokenManager::EOF;
		}
		return false;
	}

	private function headingAhead($offset) {
		if ($this->getToken($offset)->kind == TokenManager::EQ) {
			$heading = 1;
			for($i=($offset + 1);;$i++) {
				if($this->getToken($i)->kind != TokenManager::EQ) { return true; }
				if(++$heading > 6) { return false;}
			}
		}
		return false;
	}

	private function listItemAhead($listBeginColumn, $ordered) {
		if($this->getToken(1)->kind == TokenManager::EOL) {
			for($i=2,$eol=1;;$i++) {
				$t = $this->getToken($i);
				if($t->kind == TokenManager::EOL && ++$eol > 2) {
					return false;
				} else if($t->kind != TokenManager::SPACE && $t->kind != TokenManager::TAB && $t->kind != TokenManager::GT && $t->kind != TokenManager::EOL) {
					if($ordered) {
						return ($t->kind == TokenManager::DIGITS && $this->getToken($i+1)->kind == TokenManager::DOT && $t->beginColumn >= $listBeginColumn);
					}
					return $t->kind == TokenManager::DASH && $t->beginColumn >= $listBeginColumn;
				}
			}
		}
		return false;
	}

	private function textAhead() {
		if($this->getToken(1)->kind == TokenManager::EOL && $this->getToken(2)->kind != TokenManager::EOL) {
			$i = $this->skip(2, array(TokenManager::SPACE, TokenManager::TAB));
			$quoteLevel = $this->newQuoteLevel($i);
			if($quoteLevel == $this->currentQuoteLevel || !in_array("blockquotes", $this->modules)) {
				$i = $this->skip($i, array(TokenManager::SPACE, TokenManager::TAB, TokenManager::GT));
				$t = $this->getToken($i);
				return $this->getToken($i)->kind != TokenManager::EOL
					&& !(in_array("lists", $this->modules) && $t->kind == TokenManager::DASH)
					&& !(in_array("lists", $this->modules) && $t->kind == TokenManager::DIGITS && $this->getToken($i+1)->kind == TokenManager::DOT) && !($this->getToken($i)->kind == TokenManager::BACKTICK && $this->getToken($i+1)->kind == TokenManager::BACKTICK && $this->getToken($i+2)->kind == TokenManager::BACKTICK)
	                && !(in_array("headings", $this->modules) && $this->headingAhead($i));
 	        } 
 		}
		return false;
}

	private function nextAfterSpace($tokens) {
		$i = $this->skip(1, array(TokenManager::SPACE, TokenManager::TAB));
		return in_array($this->getToken($i)->kind, $tokens);
	}

	private function newQuoteLevel($offset) {
		$quoteLevel = 0;
		for($i=$offset;;$i++) {
			$t = $this->getToken($i);
			if($t->kind == TokenManager::GT) {
				$quoteLevel++;
			} else if($t->kind != TokenManager::SPACE && $t->kind != TokenManager::TAB) {
				return $quoteLevel;
			}
		}
	}

	private function skip($offset, $tokens) {
		for($i=$offset;;$i++) {
			$t = $this->getToken($i);
			if(!in_array($t->kind, $tokens) || $t->kind == TokenManager::EOF ) { return $i; }
		}
	}
	
	private function hasOrderedListAhead() {
		$this->lookAhead = 2;
		$this->lastPosition = $this->scanPosition = $this->token;
 		try {
 			return !$this->scanToken(TokenManager::DIGITS) && !$this->scanToken(TokenManager::DOT);
 		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}
	
	private function hasFencedCodeBlockAhead() {
     	$this->lookAhead = 2147483647;
	  	$this->lastPosition = $this->scanPosition = $this->token;
  		try {
    		return !$this->scanFencedCodeBlock();
  		} catch (LookaheadSuccess $ls) {
    		return true;
 		}
		
		return false;
	}
	
	
	private function headingHasInlineElementsAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			$xsp = $this->scanPosition;
			if ($this->scanTextTokens()) {
				$this->scanPosition = $xsp;
				if ($this->scanImage()) {
					$this->scanPosition = $xsp;
					if ($this->scanLink()) {
						$this->scanPosition = $xsp;
						if ($this->scanStrong()) {
							$this->scanPosition = $xsp;
							if ($this->scanEm()) {
								$this->scanPosition = $xsp;
								if ($this->scanCode()) {
									$this->scanPosition = $xsp;
									if ($this->scanLooseChar()) {
										return false;
									}
								}
							}
						}
					}
				}
			}
 			return true;
 		} catch (LookaheadSuccess $ls) {
 			return true;
 		}
 	}
	
	private function hasTextAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanTextTokens();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasImageAhead() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanImage();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}
	
	private function blockquoteHasEmptyLineAhead() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanBlockQuoteEmptyLine();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasStrongAhead() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanStrong();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasEmAhead() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanEm();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasCodeAhead() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanCode();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function blockquoteHasAnyBlockElementseAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanMoreBlockElements();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasBlockQuoteEmptyLinesAhead() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanBlockQuoteEmptyLines();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function listItemHasInlineElements() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanMoreBlockElements();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function fencedCodeBlockHasInlineTokens() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanFencedCodeBlockTokens();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasInlineTextAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanTextTokens();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasInlineElementAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanInlineElement();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function imageHasAnyElements() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanImageElement();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasResourceTextAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanResourceElements();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function linkHasAnyElements() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanLinkElement();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasResourceUrlAhead() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanResourceUrl();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function resourceHasElementAhead() {
		$this->lookAhead = 2;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanResourceElement();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function resourceTextHasElementsAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanResourceTextElement();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasEmWithinStrongMultiline() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanEmWithinStrongMultiline();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function strongMultilineHasElementsAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanStrongMultilineElements();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}
	
	private function strongWithinEmMultilineHasElementsAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanStrongWithinEmMultilineElements();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasImage() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanImage();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasLinkAhead() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanLink();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function strongEmWithinStrongAhead() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanEmWithinStrong();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function strongHasElements() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanStrongElements();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function strongWithinEmHasElementsAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanStrongWithinEmElements();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function hasStrongWithinEmMultilineAhead() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanStrongWithinEmMultiline();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function emMultilineContentHasElementsAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanEmMultilineContentElements();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function emWithinStrongMultilineContentHasElementsAhaed() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanEmWithinStrongMultilineContent();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function emHasStrongWithinEm() {
		$this->lookAhead = 2147483647;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanStrongWithinEm();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function emHasElements() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanEmElements();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function emWithinStrongHasElementsAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanEmWithinStrongElements();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function codeTextHasAnyTokenAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanCodeTextTokens();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function textHasTokensAhead() {
		$this->lookAhead = 1;
		$this->lastPosition = $this->scanPosition = $this->token;
		try {
			return !$this->scanText();
		} catch (LookaheadSuccess $ls) {
			return true;
		}
	}

	private function scanLooseChar() {
		$xsp = $this->scanPosition;
		if ($this->scanToken(TokenManager::ASTERISK)) {
			$this->scanPosition = $xsp;
			if ($this->scanToken(TokenManager::BACKTICK)) {
				$this->scanPosition = $xsp;
				if ($this->scanToken(TokenManager::LBRACK)) {
					$this->scanPosition = $xsp;
					return $this->scanToken(TokenManager::UNDERSCORE);
				}
			}
		}
		return false;
	}

	private function scanText() {
		$xsp = $this->scanPosition;
		if ($this->scanToken(TokenManager::BACKSLASH)) {
			$this->scanPosition = $xsp;
			if ($this->scanToken(TokenManager::CHAR_SEQUENCE)) {
				$this->scanPosition = $xsp;
				if ($this->scanToken(TokenManager::COLON)) {
					$this->scanPosition = $xsp;
					if ($this->scanToken(TokenManager::DASH)) {
						$this->scanPosition = $xsp;
						if ($this->scanToken(TokenManager::DIGITS)) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::DOT)) {
								$this->scanPosition = $xsp;
								if ($this->scanToken(TokenManager::EQ)) {
									$this->scanPosition = $xsp;
									if ($this->scanToken(TokenManager::ESCAPED_CHAR)) {
										$this->scanPosition = $xsp;
										if ($this->scanToken(TokenManager::GT)) {
											$this->scanPosition = $xsp;
											if ($this->scanToken(TokenManager::IMAGE_LABEL)) {
												$this->scanPosition = $xsp;
												if ($this->scanToken(TokenManager::LPAREN)) {
													$this->scanPosition = $xsp;
													if ($this->scanToken(TokenManager::LT)) {
														$this->scanPosition = $xsp;
														if ($this->scanToken(TokenManager::RBRACK)) {
															$this->scanPosition = $xsp;
															if ($this->scanToken(TokenManager::RPAREN)) {
																$this->scanPosition = $xsp;
																$this->lookingAhead = true;
																$this->semanticLookAhead = !$this->nextAfterSpace(array(TokenManager::EOL, TokenManager::EOF));
																$this->lookingAhead = false;
																return (!$this->semanticLookAhead || $this->scanWhitspaceToken());
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanTextTokens() {
		if ($this->scanText()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanText()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return false;
	}
	
	private function scanCodeTextTokens() {
		$xsp = $this->scanPosition;
		if ($this->scanToken(TokenManager::ASTERISK)) {
			$this->scanPosition = $xsp;
			if ($this->scanToken(TokenManager::BACKSLASH)) {
				$this->scanPosition = $xsp;
				if ($this->scanToken(TokenManager::CHAR_SEQUENCE)) {
					$this->scanPosition = $xsp;
					if ($this->scanToken(TokenManager::COLON)) {
						$this->scanPosition = $xsp;
						if ($this->scanToken(TokenManager::DASH)) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::DIGITS)) {
								$this->scanPosition = $xsp;
								if ($this->scanToken(TokenManager::DOT)) {
									$this->scanPosition = $xsp;
									if ($this->scanToken(TokenManager::EQ)) {
										$this->scanPosition = $xsp;
										if ($this->scanToken(TokenManager::ESCAPED_CHAR)) {
											$this->scanPosition = $xsp;
											if ($this->scanToken(TokenManager::IMAGE_LABEL)) {
												$this->scanPosition = $xsp;
												if ($this->scanToken(TokenManager::LT)) {
													$this->scanPosition = $xsp;
													if ($this->scanToken(TokenManager::LBRACK)) {
														$this->scanPosition = $xsp;
														if ($this->scanToken(TokenManager::RBRACK)) {
															$this->scanPosition = $xsp;
															if ($this->scanToken(TokenManager::LPAREN)) {
																$this->scanPosition = $xsp;
																if ($this->scanToken(TokenManager::GT)) {
																	$this->scanPosition = $xsp;
																	if ($this->scanToken(TokenManager::RPAREN)) {
																		$this->scanPosition = $xsp;
																		if ($this->scanToken(Tokenmanager::UNDERSCORE)) {
																			$this->scanPosition = $xsp;
																			$this->lookingAhead = true;
																			$this->semanticLookAhead = !$this->nextAfterSpace(array(TokenManager::EOL, TokenManager::EOF));
																			$this->lookingAhead = false;
																			return (!$this->semanticLookAhead || $this->scanWhitspaceToken());
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return false;
	}


	private function scanCode() {
		return $this->scanToken(TokenManager::BACKTICK) || $this->scanCodeTextTokensAhead() || $this->scanToken(TokenManager::BACKTICK);
	}

	private function scanCodeMultiline() {
		if ($this->scanToken(TokenManager::BACKTICK) || $this->scanCodeTextTokensAhead()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->hasCodeTextOnNextLineAhead()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return $this->scanToken(TokenManager::BACKTICK);
	}
	
	private function scanCodeTextTokensAhead() {
		if ($this->scanCodeTextTokens()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanCodeTextTokens()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return false;
	}

	private function hasCodeTextOnNextLineAhead() {
		if ($this->scanWhitespaceTokenBeforeEol()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanToken(TokenManager::GT)) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return $this->scanCodeTextTokensAhead();
	}
	
	private function scanWhitspaceTokens() {
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanWhitspaceToken()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return false;
	}

	private function scanWhitespaceTokenBeforeEol() {
		return $this->scanWhitspaceTokens() || $this->scanToken(TokenManager::EOL);
	}

	private function scanEmWithinStrongElements() {
		$xsp = $this->scanPosition;
		if ($this->scanTextTokens()) {
			$this->scanPosition = $xsp;
			if ($this->scanImage()) {
				$this->scanPosition = $xsp;
				if ($this->scanLink()) {
					$this->scanPosition = $xsp;
					if ($this->scanCode()) {
						$this->scanPosition = $xsp;
						if ($this->scanToken(TokenManager::ASTERISK)) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::BACKTICK)) {
								$this->scanPosition = $xsp;
								return $this->scanToken(TokenManager::LBRACK);
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanEmWithinStrong() {
		if ($this->scanToken(TokenManager::UNDERSCORE) || $this->scanEmWithinStrongElements()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanEmWithinStrongElements()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return $this->scanToken(TokenManager::UNDERSCORE);
	}

	private function scanEmElements() {
		$xsp = $this->scanPosition;
		if ($this->scanTextTokens()) {
			$this->scanPosition = $xsp;
			if ($this->scanImage()) {
				$this->scanPosition = $xsp;
				if ($this->scanLink()) {
					$this->scanPosition = $xsp;
					if ($this->scanCode()) {
						$this->scanPosition = $xsp;
						if ($this->scanStrongWithinEm()) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::ASTERISK)) {
								$this->scanPosition = $xsp;
								if ($this->scanToken(TokenManager::BACKTICK)) {
									$this->scanPosition = $xsp;
									return $this->scanToken(TokenManager::LBRACK);
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanEm() {
		if ($this->scanToken(TokenManager::UNDERSCORE) || $this->scanEmElements()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanEmElements()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return $this->scanToken(TokenManager::UNDERSCORE);
	}

	private function scanEmWithinStrongMultilineContent() {
		$xsp = $this->scanPosition;
		if ($this->scanTextTokens()) {
			$this->scanPosition = $xsp;
			if ($this->scanImage()) {
				$this->scanPosition = $xsp;
				if ($this->scanLink()) {
					$this->scanPosition = $xsp;
					if ($this->scanCode()) {
						$this->scanPosition = $xsp;
						if ($this->scanToken(TokenManager::ASTERISK)) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::BACKTICK)) {
								$this->scanPosition = $xsp;
								return $this->scanToken(TokenManager::LBRACK);
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function hasNoEmWithinStrongMultilineContentAhead() {
		if ($this->scanEmWithinStrongMultilineContent()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanEmWithinStrongMultilineContent()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return false;
	}

	private function scanEmWithinStrongMultiline() {
		if ($this->scanToken(TokenManager::UNDERSCORE) || $this->hasNoEmWithinStrongMultilineContentAhead()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanWhitespaceTokenBeforeEol() || $this->hasNoEmWithinStrongMultilineContentAhead()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return $this->scanToken(TokenManager::UNDERSCORE);
	}

	private function scanEmMultilineContentElements() {
		$xsp = $this->scanPosition;
		if ($this->scanTextTokens()) {
			$this->scanPosition = $xsp;
			if ($this->scanImage()) {
				$this->scanPosition = $xsp;
				if ($this->scanLink()) {
					$this->scanPosition = $xsp;
					$this->lookingAhead = true;
					$this->semanticLookAhead = $this->multilineAhead(TokenManager::BACKTICK);
					$this->lookingAhead = false;
					if (!$this->semanticLookAhead || $this->scanCodeMultiline()) {
						$this->scanPosition = $xsp;
						if ($this->scanStrongWithinEmMultiline()) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::ASTERISK)) {
								$this->scanPosition = $xsp;
								if ($this->scanToken(TokenManager::BACKTICK)) {
									$this->scanPosition = $xsp;
									return $this->scanToken(TokenManager::LBRACK);
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanStrongWithinEmElements() {
		$xsp = $this->scanPosition;
		if ($this->scanTextTokens()) {
			$this->scanPosition = $xsp;
			if ($this->scanImage()) {
				$this->scanPosition = $xsp;
				if ($this->scanLink()) {
					$this->scanPosition = $xsp;
					if ($this->scanCode()) {
						$this->scanPosition = $xsp;
						if ($this->scanToken(TokenManager::BACKTICK)) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::LBRACK)) {
								$this->scanPosition = $xsp;
								return $this->scanToken(TokenManager::UNDERSCORE);
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanStrongWithinEm() {
		if ($this->scanToken(TokenManager::ASTERISK) || $this->scanStrongWithinEmElements()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanStrongWithinEmElements()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return $this->scanToken(TokenManager::ASTERISK);
	}

	private function scanStrongElements() {
		$xsp = $this->scanPosition;
		if ($this->scanTextTokens()) {
			$this->scanPosition = $xsp;
			if ($this->scanImage()) {
				$this->scanPosition = $xsp;
				if ($this->scanLink()) {
					$this->scanPosition = $xsp;
					$this->lookingAhead = true;
					$this->semanticLookAhead = $this->multilineAhead(TokenManager::BACKTICK);
					$this->lookingAhead = false;
					if (!$this->semanticLookAhead || $this->scanCodeMultiline()) {
						$this->scanPosition = $xsp;
						if ($this->scanEmWithinStrong()) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::BACKTICK)) {
								$this->scanPosition = $xsp;
								if ($this->scanToken(TokenManager::LBRACK)) {
									$this->scanPosition = $xsp;
									return $this->scanToken(TokenManager::UNDERSCORE);
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanStrong() {
		if ($this->scanToken(TokenManager::ASTERISK) || $this->scanStrongElements()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanStrongElements()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return $this->scanToken(TokenManager::ASTERISK);
	}

	private function scanStrongWithinEmMultilineElements() {
		$xsp = $this->scanPosition;
		if ($this->scanTextTokens()) {
			$this->scanPosition = $xsp;
			if ($this->scanImage()) {
				$this->scanPosition = $xsp;
				if ($this->scanLink()) {
					$this->scanPosition = $xsp;
					if ($this->scanCode()) {
						$this->scanPosition = $xsp;
						if ($this->scanToken(TokenManager::BACKTICK)) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::LBRACK)) {
								$this->scanPosition = $xsp;
								return $this->scanToken(TokenManager::UNDERSCORE);
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanForMoreStrongWithinEmMultilineElements() {
		if ($this->scanStrongWithinEmMultilineElements()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanStrongWithinEmMultilineElements()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return false;
	}

	private function scanStrongWithinEmMultiline() {
		if ($this->scanToken(TokenManager::ASTERISK) || $this->scanForMoreStrongWithinEmMultilineElements()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanWhitespaceTokenBeforeEol() || $this->scanForMoreStrongWithinEmMultilineElements()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return $this->scanToken(TokenManager::ASTERISK);
	}

	private function scanStrongMultilineElements() {
		$xsp = $this->scanPosition;
		if ($this->scanTextTokens()) {
			$this->scanPosition = $xsp;
			if ($this->scanImage()) {
				$this->scanPosition = $xsp;
				if ($this->scanLink()) {
					$this->scanPosition = $xsp;
					if ($this->scanCode()) {
						$this->scanPosition = $xsp;
						if ($this->scanEmWithinStrongMultiline()) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::BACKTICK)) {
								$this->scanPosition = $xsp;
								if ($this->scanToken(TokenManager::LBRACK)) {
									$this->scanPosition = $xsp;
									return $this->scanToken(TokenManager::UNDERSCORE);
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanResourceTextElement() {
		$xsp = $this->scanPosition;
		if ($this->scanToken(TokenManager::ASTERISK)) {
			$this->scanPosition = $xsp;
			if ($this->scanToken(TokenManager::BACKSLASH)) {
				$this->scanPosition = $xsp;
				if ($this->scanToken(TokenManager::BACKTICK)) {
					$this->scanPosition = $xsp;
					if ($this->scanToken(TokenManager::CHAR_SEQUENCE)) {
						$this->scanPosition = $xsp;
						if ($this->scanToken(TokenManager::COLON)) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::DASH)) {
								$this->scanPosition = $xsp;
								if ($this->scanToken(TokenManager::DIGITS)) {
									$this->scanPosition = $xsp;
									if ($this->scanToken(TokenManager::DOT)) {
										$this->scanPosition = $xsp;
										if ($this->scanToken(TokenManager::EQ)) {
											$this->scanPosition = $xsp;
											if ($this->scanToken(TokenManager::ESCAPED_CHAR)) {
												$this->scanPosition = $xsp;
												if ($this->scanToken(TokenManager::IMAGE_LABEL)) {
													$this->scanPosition = $xsp;
													if ($this->scanToken(TokenManager::GT)) {
														$this->scanPosition = $xsp;
														if ($this->scanToken(TokenManager::LBRACK)) {
															$this->scanPosition = $xsp;
															if ($this->scanToken(TokenManager::LPAREN)) {
																$this->scanPosition = $xsp;
																if ($this->scanToken(TokenManager::LT)) {
																	$this->scanPosition = $xsp;
																	if ($this->scanToken(TokenManager::RBRACK)) {
																		$this->scanPosition = $xsp;
																		if ($this->scanToken(TokenManager::UNDERSCORE)) {
																			$this->scanPosition = $xsp;
																			$this->lookingAhead = true;
																			$this->semanticLookAhead = !$this->nextAfterSpace(array(TokenManager::RPAREN));
																			$this->lookingAhead = false;
																			return (!$this->semanticLookAhead || $this->scanWhitspaceToken());
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanImageElement() {
		$xsp = $this->scanPosition;
		if ($this->scanResourceElements()) {
			$this->scanPosition = $xsp;
			if ($this->scanLooseChar()) {
				return true;
			}
		}
		return false;
	}

	private function scanResourceTextElements() {
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanResourceTextElement()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return false;
	}

	private function scanResourceUrl() {
		return $this->scanToken(TokenManager::LPAREN) || $this->scanWhitspaceTokens() || $this->scanResourceTextElements()
			|| $this->scanWhitspaceTokens() || $this->scanToken(TokenManager::RPAREN);
	}

	private function scanLinkElement() {
		$xsp = $this->scanPosition;
		if ($this->scanImage()) {
			$this->scanPosition = $xsp;
			if ($this->scanStrong()) {
				$this->scanPosition = $xsp;
				if ($this->scanEm()) {
					$this->scanPosition = $xsp;
					if ($this->scanCode()) {
						$this->scanPosition = $xsp;
						if ($this->scanResourceElements()) {
							$this->scanPosition = $xsp;
							return $this->scanLooseChar();
						}
					}
				}
			}
		}
		return false;
	}

	private function scanResourceElement() {
		$xsp = $this->scanPosition;
		if ($this->scanToken(TokenManager::BACKSLASH)) {
			$this->scanPosition = $xsp;
			if ($this->scanToken(TokenManager::COLON)) {
				$this->scanPosition = $xsp;
				if ($this->scanToken(TokenManager::CHAR_SEQUENCE)) {
					$this->scanPosition = $xsp;
					if ($this->scanToken(TokenManager::DASH)) {
						$this->scanPosition = $xsp;
						if ($this->scanToken(TokenManager::DIGITS)) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::DOT)) {
								$this->scanPosition = $xsp;
								if ($this->scanToken(TokenManager::EQ)) {
									$this->scanPosition = $xsp;
									if ($this->scanToken(TokenManager::ESCAPED_CHAR)) {
										$this->scanPosition = $xsp;
										if ($this->scanToken(TokenManager::IMAGE_LABEL)) {
											$this->scanPosition = $xsp;
											if ($this->scanToken(TokenManager::GT)) {
												$this->scanPosition = $xsp;
												if ($this->scanToken(TokenManager::LPAREN)) {
													$this->scanPosition = $xsp;
													if ($this->scanToken(Tokenmanager::LT)) {
														$this->scanPosition = $xsp;
														if ($this->scanToken(TokenManager::RPAREN)) {
															$this->scanPosition = $xsp;
															$this->lookingAhead = true;
															$this->semanticLookAhead = !$this->nextAfterSpace(array(TokenManager::RBRACK));
															$this->lookingAhead = false;
															return (!$this->semanticLookAhead || $this->scanWhitspaceToken());
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanResourceElements() {
		if ($this->scanResourceElement()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanResourceElement()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return false;
	}

	private function scanLink() {
		if ($this->scanToken(TokenManager::LBRACK) || $this->scanWhitspaceTokens() || $this->scanLinkElement()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanLinkElement()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		if ($this->scanWhitspaceTokens() || $this->scanToken(TokenManager::RBRACK)) {
			return true;
		}
		$xsp = $this->scanPosition;
		if ($this->scanResourceUrl()) {
			$this->scanPosition = $xsp;
		}
		return false;
	}

	private function scanImage() {
		if ($this->scanToken(TokenManager::LBRACK) || $this->scanWhitspaceTokens() || $this->scanToken(TokenManager::IMAGE_LABEL) || $this->scanImageElement()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanImageElement()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		if ($this->scanWhitspaceTokens() || $this->scanToken(TokenManager::RBRACK)) {
			return true;
		}
		$xsp = $this->scanPosition;
		if ($this->scanResourceUrl()) {
			$this->scanPosition = $xsp;
		}
		return false;
	}

	private function scanInlineElement() {
		$xsp = $this->scanPosition;
		if ($this->scanTextTokens()) {
			$this->scanPosition = $xsp;
			if ($this->scanImage()) {
				$this->scanPosition = $xsp;
				if ($this->scanLink()) {
					$this->scanPosition = $xsp;
					$this->lookingAhead = true;
					$this->semanticLookAhead = $this->multilineAhead(TokenManager::ASTERISK);
					$this->lookingAhead = false;
					if (!$this->semanticLookAhead || $this->scanToken(TokenManager::ASTERISK)) {
						$this->scanPosition = $xsp;
						$this->lookingAhead = true;
						$this->semanticLookAhead = $this->multilineAhead(TokenManager::UNDERSCORE);
						$this->lookingAhead = false;
						if (!$this->semanticLookAhead || $this->scanToken(TokenManager::UNDERSCORE)) {
							$this->scanPosition = $xsp;
							$lookingAhead = true;
							$this->semanticLookAhead = $this->multilineAhead(TokenManager::BACKTICK);
							$this->lookingAhead = false;
							if (!$this->semanticLookAhead || $this->scanCodeMultiline()) {
								$this->scanPosition = $xsp;
								return $this->scanLooseChar();
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanParagraph() {
		$xsp = null;
		if ($this->scanInlineElement()) {
			return true;
		}
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanInlineElement()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return false;
	}

	private function scanWhitspaceToken() {
		$xsp = $this->scanPosition;
		if ($this->scanToken(TokenManager::SPACE)) {
			$this->scanPosition = $xsp;
			if ($this->scanToken(TokenManager::TAB)) {
				return true;
			}
		}
		return false;
	}
	
	private function scanNoFencedCodeBlockAhead() {
		if ($this->scanToken(TokenManager::EOL) || $this->scanWhitspaceTokens() || $this->scanToken(TokenManager::BACKTICK)) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanToken(TokenManager::BACKTICK)) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return false;
	}
	
	private function scanFencedCodeBlockTokens() {
 		$xsp = $this->scanPosition;
		if ($this->scanToken(TokenManager::ASTERISK)) {
			$this->scanPosition = $xsp;
			if ($this->scanToken(TokenManager::BACKSLASH)) {
				$this->scanPosition = $xsp;
				if ($this->scanToken(TokenManager::CHAR_SEQUENCE)) {
					$this->scanPosition = $xsp;
					if ($this->scanToken(TokenManager::COLON)) {
						$this->scanPosition = $xsp;
						if ($this->scanToken(TokenManager::DASH)) {
							$this->scanPosition = $xsp;
							if ($this->scanToken(TokenManager::DIGITS)) {
								$this->scanPosition = $xsp;
								if ($this->scanToken(TokenManager::DOT)) {
									$this->scanPosition = $xsp;
									if ($this->scanToken(TokenManager::EQ)) {
										$this->scanPosition = $xsp;
										if ($this->scanToken(TokenManager::ESCAPED_CHAR)) {
											$this->scanPosition = $xsp;
											if ($this->scanToken(TokenManager::IMAGE_LABEL)) {
												$this->scanPosition = $xsp;
												if ($this->scanToken(TokenManager::LT)) {
													$this->scanPosition = $xsp;
													if ($this->scanToken(TokenManager::GT)) {
														$this->scanPosition = $xsp;
														if ($this->scanToken(TokenManager::LBRACK)) {
															$this->scanPosition = $xsp;
															if ($this->scanToken(TokenManager::RBRACK)) {
																$this->scanPosition = $xsp;
																if ($this->scanToken(TokenManager::LPAREN)) {
																	$this->scanPosition = $xsp;
																	if ($this->scanToken(TokenManager::RPAREN)) {
																		$this->scanPosition = $xsp;
																		if ($this->scanToken(TokenManager::UNDERSCORE)) {
																			$this->scanPosition = $xsp;
																			if ($this->scanToken(TokenManager::BACKTICK)) {
																				$this->scanPosition = $xsp;
																				$this->lookingAhead = true;
																				$this->semanticLookAhead = !$this->nextAfterSpace(array(TokenManager::EOL, TokenManager::EOF));
																				$this->lookingAhead = false;
																				if (!$this->semanticLookAhead || $this->scanWhitspaceToken()) {
																					$this->scanPosition = $xsp;
																					$this->lookingAhead = true;
																					$this->semanticLookAhead = !$this->fencesAhead();
																					$this->lookingAhead = false;
																					// TODO: memory leak
																					return !$this->semanticLookAhead || $this->scanToken(TokenManager::EOL) || $this->scanWhitspaceTokens();
																				}
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function scanFencedCodeBlock() {
		return $this->scanToken(TokenManager::BACKTICK) || $this->scanToken(TokenManager::BACKTICK) || $this->scanToken(TokenManager::BACKTICK);
	}

	private function scanBlockQuoteEmptyLines() {
		return $this->scanBlockQuoteEmptyLine() || $this->scanToken(TokenManager::EOL);
	}

	private function scanBlockQuoteEmptyLine() {
		if ($this->scanToken(TokenManager::EOL) || $this->scanWhitspaceTokens() || $this->scanToken(TokenManager::GT) || $this->scanWhitspaceTokens()) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanToken(TokenManager::GT) || $this->scanWhitspaceTokens()) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return false;
	}

	private function scanForHeadersigns() {
		if ($this->scanToken(TokenManager::EQ)) {
			return true;
		}
		$xsp=null;
		while (true) {
			$xsp = $this->scanPosition;
			if ($this->scanToken(TokenManager::EQ)) {
				$this->scanPosition = $xsp;
				break;
			}
		}
		return false;
	}

	private function scanMoreBlockElements() {
		$xsp = $this->scanPosition;
		$this->lookingAhead = true;
		$this->semanticLookAhead = $this->headingAhead(1);
		$this->lookingAhead = false;
		if (!$this->semanticLookAhead || $this->scanForHeadersigns()) {
			$this->scanPosition = $xsp;
			if ($this->scanToken(TokenManager::GT)) {
				$this->scanPosition = $xsp;
				if ($this->scanToken(TokenManager::DASH)) {
					$this->scanPosition = $xsp;
					if ($this->scanToken(TokenManager::DIGITS) || $this->scanToken(TokenManager::DOT)) {
						$this->scanPosition = $xsp;
						if ($this->scanFencedCodeBlock()) {
							$this->scanPosition = $xsp;
							return $this->scanParagraph();
						}
					}
				}
			}
		}
		return false;
	}

	private function scanToken($kind) {
		if ($this->scanPosition == $this->lastPosition) {
			$this->lookAhead--;
			if ($this->scanPosition->next == null) {
				$this->lastPosition = $this->scanPosition = $this->scanPosition->next = $this->tm->getNextToken();
			} else {
				$this->lastPosition = $this->scanPosition = $this->scanPosition->next;
			}
		} else {
			$this->scanPosition = $this->scanPosition->next;
		}
		
		if ($this->scanPosition->kind != $kind) {
			return true;
		}
		if ($this->lookAhead == 0 && $this->scanPosition == $this->lastPosition) {
			throw $this->lookAheadSuccess;
		}
		return false;
	}
	
 	private function getNextTokenKind() {
 		if($this->nextTokenKind != -1) { 
 			return $this->nextTokenKind; 
 		} else if (($this->nextToken = $this->token->next) == null) {
 			$this->token->next = $this->tm->getNextToken();
			return ($this->nextTokenKind = $this->token->next->kind);
 		}
 		return ($this->nextTokenKind = $this->nextToken->kind);
 	}
	
 	private function consumeToken($kind) {
 		$old = $this->token;
		if ($this->token->next != null) {
			$this->token = $this->token->next;
		} else {
			$this->token = $this->token->next = $this->tm->getNextToken();
		}
		$this->nextTokenKind = -1;
		if ($this->token->kind == $kind) {
			return $this->token;
		}
		$this->token = $old;
 		return $this->token;
 	}
	
	private function getToken($index) {
		$t = $this->lookingAhead ? $this->scanPosition : $this->token;
		for ($i = 0; $i < $index; $i++) {
			if ($t->next != null) {
				$t = $t->next;
			} else {
				$t = $t->next = $this->tm->getNextToken();
			}
		}
		return $t;
	}
	
	public function setModules($modules) {
		$this->modules = $modules;
	}
	
}
