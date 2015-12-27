<?php 
namespace Koara;

use Koara\Ast\Document;
use Koara\Ast\Heading;

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
 		$this->modules = array(Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, 
 				Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE
 		);
 	}
 	
// 	public Document parse(String text) {
// 		return parse(new StringReader(text));
// 	}
	
 	/**
 	 * @return Document
 	 */
 	public function parseFile($resource) {
 		$this->cs = new CharStream(fopen($resource, 'r'));
 		$this->tm = new TokenManager($this->cs);
 		$this->token = new Token();
		$this->tree = new TreeState();
 		$this->nextTokenKind = -1;
		
 		$document = new Document();
 		$this->tree->openScope();
 		do {
 			$this->consumeToken(TokenManager::EOL);
 		} while ($this->getNextTokenKind() == TokenManager::EOL);
 		$this->whiteSpace();
 		if ($this->hasAnyBlockElementsAhead()) {
 			$this->blockElement();
 			while ($this->blockAhead(0)) {
 				while (getNextTokenKind() == TokenManager::EOL) {
 					$this->consumeToken(TokenManager::EOL);
 					$this->whiteSpace();			
 				}
 				$this->blockElement();
 			}
			do {
 				consumeToken(TokenManager::EOL);
 			} while($this->getNextTokenKind() == TokenManager::EOL);
 			$this->whiteSpace();
 		} 
 		$this->consumeToken(TokenManager::EOF);
 		$this->tree->closeScope($document);
 		return $document;
 	}
	
 	private function blockElement() {
 		$this->currentBlockLevel++;
 		if (in_array(modules, Module::HEADINGS) && $this->headingAhead(1)) {
 			$this->heading();
 		} else if(in_array(modules, Module::BLOCKQUOTES) && getNextTokenKind() == TokenManager::GT) {
 			$this->blockquote();
 		} else if(in_array(modules, Module::LISTS) && getNextTokenKind() == TokenManager::DASH) {
 			$this->unorderedList();
 		} else if(in_array(modules, Module::LISTS) && hasOrderedListAhead()) {
 			$this->orderedList();
 		} else if(in_array(modules, Module::CODE) && hasFencedCodeBlockAhead()) {
 			$this->fencedCodeBlock();
 		} else {
 			$this->paragraph();
 		}
 		$this->currentBlockLevel--;
 	}
	
 	private function heading() {
 		$heading = new Heading();
 		$this->tree.openScope();
 		$headingLevel = 0;

 		while($this->getNextTokenKind() == TokenManager::EQ) {
 			$this->consumeToken(TokenManager::EQ);
 			$this->headingLevel++;
 		}
 		$this->whiteSpace();
 	    while ($this->headingHasInlineElementsAhead()) {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array(modules, Module::IMAGES) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array(modules, Module::LINKS) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array(modules, Module::FORMATTING) && $this->hasStrongAhead()) {
 				$this->strong();
 			} else if (in_array(modules, Module::FORMATTING) && $this->hasEmAhead()) {
 				$this->em();
 			} else if (in_array(modules, Module::CODE) && $this->hasCodeAhead()) {
 				$this->code();
 			} else {
				$this->looseChar();
 			}
 		}
 		$heading.setValue($headingLevel);
 		$this->tree->closeScope($heading);
 	}
	
	private function blockquote() {
 		$blockquote = new Blockquote();
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
 		while ($this->hasBlockquoteEmptyLinesAhead()) {
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
 				$this->consumeToken(EOL);
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
 		$t = consumeToken(TokenManager::DIGITS);
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
 		$listItem->setNumber(intval(t.image));
 		$this->tree->closeScope($listItem);
 		return $t->beginColumn;
 	}

 	private function fencedCodeBlock() {
 		$codeBlock = new CodeBlock();
 		$this->tree.openScope();
 		$s;
 		$beginColumn = consumeToken(TokenManager::BACKTICK)->beginColumn;
 		do {
 			$this->consumeToken(TokenManager::BACKTICK);
 		} while($this->getNextTokenKind() == TokenManager::BACKTICK);
 		$this->whiteSpace();
 		if ($this->getNextTokenKind() == TokenManager::CHAR_SEQUENCE) {
 			$this->codeBlock->setLanguage($this->codeLanguage()); 
 		}
 		if ($this->getNextTokenKind() != TokenManager::EOF && !$this->fencesAhead()) {
 			$this->consumeToken(TokenManager::EOL);
 			$this->levelWhiteSpace($beginColumn);
 		}
 		while (fencedCodeBlockHasInlineTokens()) {
 			switch (getNextTokenKind()) {
 			case TokenManager::ASTERISK: 		$s .= $this->consumeToken(TokenManager::ASTERISK)->image; break;
 			case TokenManager::BACKSLASH: 		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
			case TokenManager::CHAR_SEQUENCE: 	$s .= $this->consumeToken(CHAR_SEQUENCE)->image; break;
			case TokenManager::COLON: 			$s .= $this->consumeToken(COLON)->image; break;
			case TokenManager::DASH: 			$s .= $this->consumeToken(DASH)->image; break;
			case TokenManager::DIGITS: 			$s .= $this->consumeToken(DIGITS)->image; break;
			case TokenManager::DOT: 			$s .= $this->consumeToken(DOT)->image; break;
			case TokenManager::EQ: 				$s .= $this->consumeToken(EQ)->image; break;
			case TokenManager::ESCAPED_CHAR: 	$s .= $this->consumeToken(ESCAPED_CHAR)->image; break;
			case TokenManager::IMAGE_LABEL: 	$s .= $this->consumeToken(IMAGE_LABEL)->image; break;
			case TokenManager::LT: 				$s .= $this->consumeToken(LT)->image; break;
			case TokenManager::GT: 				$s .= $this->consumeToken(GT)->image; break;
			case TokenManager::LBRACK:			$s .= $this->consumeToken(LBRACK)->image; break;
			case TokenManager::RBRACK:			$s .= $this->consumeToken(RBRACK)->image; break;
			case TokenManager::LPAREN:			$s .= $this->consumeToken(LPAREN)->image; break;
			case TokenManager::RPAREN:			$s .= $this->consumeToken(RPAREN)->image; break;
			case TokenManager::UNDERSCORE:		$s .= $this->consumeToken(UNDERSCORE)->image; break;
			case TokenManager::BACKTICK:		$s .= $this->consumeToken(BACKTICK)->image; break;
 			default:
 				if (!$this->nextAfterSpace(TokenManager::EOL, TokenManager::EOF)) {
 					switch ($this->getNextTokenKind()) {
 					case TokenManager::SPACE: 	$s .= $this->consumeToken(TokenManager::SPACE).image; break;
 					case TokenManager::TAB: 	$this->consumeToken(TokenManager::TAB); $s .= "    "; break;
 					}
 				} else if (!$this->fencesAhead()) {
 					$this->consumeToken(TokenManager::EOL);
 					$s .= "\n";
 					$this->levelWhiteSpace($beginColumn);
 				}
 			}
 		}
 		if (fencesAhead()) {
 			$this->consumeToken(TokenManager::EOL);
 			$this->whiteSpace();
 			while ($this->getNextTokenKind() == TokenManager::BACKTICK) {
 				$this->consumeToken(TokenManager::BACKTICK);
 			}
 		}
 		$codeBlock->setValue($s);
 		$this->tree->closeScope($codeBlock);
 	}
	
 	private function paragraph() {
 		$paragraph;
 		if(in_array(modules, Module.PARAGRAPHS)) {
 			$paragraph = new Paragraph();			
 		} else {
 			$paragraph = new BlockElement();
 		}
		
 		$this->tree->openScope();
 		$this->inline();
 		while ($this->textAhead()) {
 			$this->lineBreak();
 			$this->whiteSpace();
 			if(in_array(modules, Module.BLOCKQUOTES)) {
 				while ($this->getNextTokenKind() == TokenManager::GT) {
 					$this->consumeToken(TokenManager::GT);
 					$this->twhiteSpace();
 				}
 			}
 			$this->inline();
 		}
 		$this->tree->closeScope($paragraph);
 	}
	
 	private function text() {
 		$text = new Text();
 		$this->tree->openScope($text);
		$s;
 		while ($this->textHasTokensAhead()) {
 			switch ($this->getNextTokenKind()) {
 			case TokenManager::BACKSLASH: 		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
			case TokenManager::CHAR_SEQUENCE:	$s .= $this->consumeToken(TokenManager::CHAR_SEQUENCE)->image; break;
			case TokenManager::COLON: 			$s .= $this->consumeToken(TokenManager::COLON)->image; break;
			case TokenManager::DASH: 			$s .= $this->consumeToken(TokenManager::DASH)->image; break;
			case TokenManager::DIGITS: 			$s .= $this->consumeToken(TokenManager::DIGITS)->image; break;
			case TokenManager::DOT: 			$s .= $this->consumeToken(TokenManager::DOT)->image; break;
			case TokenManager::EQ: 				$s .= $this->consumeToken(TokenManager::EQ)->image; break;
			case TokenManager::ESCAPED_CHAR:	$s .= substr($this->consumeToken(TokenManager::ESCAPED_CHAR)->image, 1); break;
			case TokenManager::GT: 				$s .= $this->consumeToken(TokenManager::GT)->image; break;
			case TokenManager::IMAGE_LABEL: 	$s .= $this->consumeToken(TokenManager::IMAGE_LABEL)->image; break;
			case TokenManager::LPAREN: 			$s .= $this->consumeToken(TokenManager::LPAREN)->image; break;
			case TokenManager::LT: 				$s .= $this->consumeToken(TokenManager::LT)->image; break;
			case TokenManager::RBRACK: 			$s .= $this->consumeToken(TokenManager::RBRACK)->image; break;
			case TokenManager::RPAREN: 			$s .= $this->consumeToken(TokenManager::RPAREN)->image; break;
 			default:
 				if (!$this->nextAfterSpace(TokenManager::EOL, TokenManager::EOF)) {
 					switch ($this->getNextTokenKind()) {
 					case TokenManager::SPACE:	$s .= $this->consumeToken(TokenManager::SPACE)->image; break;
 					case TokenManager::TAB:		consumeToken(TokenManager::TAB); $s .= "    "; break;
 					}
 				}
 			}
 		}
 		$this->text->setValue($s);
 		$this->tree->closeScope($text);
 	}
	
 	private function image() {
 		$image = new Image();
 		tree.openScope();
 		$ref = "";
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
 		$ref = "";
 		$this->consumeToken(TokenManager::LBRACK);
 		$this->whiteSpace();
 		while ($this->linkHasAnyElements()) {
 			if (in_array(modules, Module::IMAGES) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array(modules, Module::FORMATTING) && $this->hasStrongAhead()) {
 				$this->strong();
 			} else if (in_array(modules, Module::FORMATTING) && $this->hasEmAhead()) {
 				$this->em();
 			} else if (in_array(modules, Module::CODE) && $this->hasCodeAhead()) {
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
 			} else if (in_array(modules, Module::IMAGES) && $this->hasImage()) {
 				$this->image();
 			} else if (in_array(modules, Module::LINKS) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array(modules, Module::CODE) && $this->multilineAhead(TokenManager::BACKTICK)) {
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
 			} else if (in_array(modules, Module::IMAGES) && $this->hasImage()) {
 				$this->image();
 			} else if (in_array(modules, Module::LINKS) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array(modules, Module::CODE) && $this->hasCodeAhead()) {
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
 		$s;
 		do {
 			switch ($this->getNextTokenKind()) {
 			case TokenManager::ASTERISK: 		$s .= $this->consumeToken(TokenManager::ASTERISK)->image; break;
 			case TokenManager::BACKSLASH: 		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
 			case TokenManager::CHAR_SEQUENCE: 	$s .= $this->consumeToken(TokenManager::CHAR_SEQUENCE)->image; break;
 			case TokenManager::COLON: 			$s .= $this->consumeToken(TokenManager::COLON)->image; break;
 			case TokenManager::DASH: 			$s .= $this->consumeToken(TokenManager::DASH)->image; break;
 			case TokenManager::DIGITS:			$s .= $this->consumeToken(TokenManager::DIGITS)->image; break;
 			case TokenManager::DOT:				$s .= $this->consumeToken(TokenManager::DOT)->image; break;
 			case TokenManager::EQ:				$s .= $this->consumeToken(TokenManager::EQ)->image; break;
 			case TokenManager::ESCAPED_CHAR:	$s .= $this->consumeToken(TokenManager::ESCAPED_CHAR)->image; break;
 			case TokenManager::IMAGE_LABEL:		$s .= $this->consumeToken(TokenManager::IMAGE_LABEL)->image; break;
 			case TokenManager::LT:				$s .= $this->consumeToken(TokenManager::LT)->image; break;
 			case TokenManager::LBRACK:			$s .= $this->consumeToken(TokeneManager::LBRACK)->image; break;
 			case TokenManager::RBRACK:			$s .= $this->consumeToken(TokenManager::RBRACK)->image; break;
 			case TokenManager::LPAREN:			$s .= $this->consumeToken(TokenManager::LPAREN)->image; break;
 			case TokenManager::GT:				$s .= $this->consumeToken(TokenManager::GT)->image; break;
 			case TokenManager::RPAREN:			$s .= $this->consumeToken(TokenManager::RPAREN)->image; break;
 			case TokenManager::UNDERSCORE:		$s .= $this->consumeToken(TokenManager::UNDERSCORE)->image; break;
 			default:
 				if (!$this->nextAfterSpace(TokenManager::EOL, TokenManager::EOF)) {
 					switch ($this->getNextTokenKind()) {
 					case TokenManager::SPACE:	$s .= $this->consumeToken(TokenManager::SPACE)->image; break;
 					case TokenManager::TAB: 	$this->consumeToken(TokenManager::TAB); $s .= "    "; break;
 					}
 				}
 			}
 		} while($this->codeTextHasAnyTokenAhead());
 		$this->text->setValue($s);
 		$this->tree->closeScope($text);
 	}

 	private function looseChar() {
 		$text = new Text();
		$this->tree->openScope();
 		switch ($this->getNextTokenKind()) {
 		case TokenManager::ASTERISK:		$text->setValue($this->consumeToken(TokenManager::ASTERISK)->image); break;
 		case TokenManager::BACKTICK:		$text->setValue($this->consumeToken(TokenManager::BACKTICK)->image); break;
 		case TokenManager::LBRACK:			$text->setValue($this->consumeToken(TokenManager::LBRACK)->image); break;
 		case TokenManger::UNDERSCORE:		$text->setValue($this->consumeToken(TokenManager::UNDERSCORE)->image); break;		
 		}
 		$this->tree->closeScope($text);
 	}

 	private function lineBreak() {
 		$linebreak = new LineBreak();
 		$this->tree->openScope();
 		while ($this->getNextTokenKind() == TokenManager::SPACE || $this->getNextTokenKind() == TokenManager::TAB) {
 			$this->consumeToken($this->getNextTokenKind());
 		}
 		$this->consumeToken(TokenManager::EOL);
 		$this->tree->closeScope($linebreak);
 	}
	
 	private function levelWhiteSpace($threshold) {
 		$currentPos = 1;
 	    while($this->getNextTokenKind() == TokenManager::GT) {
 	    	$this->consumeToken($this->getNextTokenKind());
 	    }
 		while (($this->getNextTokenKind() == TokenManager::SPACE || $this->getNextTokenKind() == TokenManager::TAB) && $currentPos < ($this->threshold - 1)) {
 			$currentPos = $this->consumeToken($this->getNextTokenKind())->beginColumn;
 		}
 	}

 	private function codeLanguage() {
 		$s;
 		do {
 			switch ($this->getNextTokenKind()) {
 			case TokenManager::ASTERISK: 		$s .= $this->consumeToken(TokenManager::ASTERISK)->image; break;
 			case TokenManager::BACKSLASH: 		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
 			case TokenManager::BACKTICK: 		$s .= $this->consumeToken(TokenManager::BACKTICK)->image; break;
 			case TokenManager::CHAR_SEQUENCE:	$s .= $this->consumeToken(TokenManager::CHAR_SEQUENCE)->image; break;
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
 			case TokenManager::TAB: 			$s .= "    "; break;
 			default: break;
 			}
 		} while ($this->getNextTokenKind() != TokenManager::EOL && $this->getNextTokenKind() != TokenManager::EOF);
 		return $s;
 	}

 	private function inline() {
 		do {
 			if ($this->hasInlineTextAhead()) {
 				$this->text();
 			} else if (in_array(modules, Module::IMAGES) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array(modules, Module::LINKS) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array(modules, Module::FORMATTING) && $this->multilineAhead(TokenManager::ASTERISK)) {
 				$this->strongMultiline();
 			} else if (in_array(modules, Module::FORMATTING) && $this->multilineAhead(TokenManager::UNDERSCORE)) {
 				$this->emMultiline();
 			} else if (in_array(modules, Module::CODE) && $this->multilineAhead(TokenManager::BACKTICK)) {
 				$this->codeMultiline();
 			} else {
 				$this->looseChar();
 			}
 		} while ($this->hasInlineElementAhead());
 	}

 	private function resourceText() {
 		$text = new Text();
 		$this->tree->openScope();
		$s;
 		do {
 			switch ($this->getNextTokenKind()) {
 			case TokenManager::BACKSLASH:		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
 			case TokenManager::COLON:			$s .= $this->consumeToken(TokenManager::COLON)->image; break;
 			case TokenManager::CHAR_SEQUENCE:	$s .= $this->consumeToken(TokenManager::CHAR_SEQUENCE)->image; break;
 			case TokenManager::DASH:			$s .= $this->consumeToken(TokenManager::DASH)->image; break;
 			case TokenManager::DIGITS:			$s .= $this->consumeToken(TokenManager::DIGITS)->image; break;
 			case TokenManager::DOT:				$s .= $this->consumeToken(TokenManager::DOT)->image; break;
 			case TokenManager::EQ:				$s .= $this->consumeToken(TokenManager::EQ)->image; break;
 			case TokenManager::ESCAPED_CHAR:	$s .= substr($this->consumeToken(TokenManager::ESCAPED_CHAR)->image, 1); break;
 			case TokenManager::IMAGE_LABEL:		$s .= $this->consumeToken(TokenManager::IMAGE_LABEL)->image; break;
 			case TokenManager::GT:				$s .= $this->consumeToken(TokenManager::GT)->image; break;
 			case TokenManager::LPAREN:			$s .= $this->consumeToken(TokenManager::LPAREN)->image; break;
 			case TokenManager::LT:				$s .= $this->consumeToken(TokenManager::LT)->image; break;
 			case TokenManager::RPAREN:			$s .= $this->consumeToken(TokenManager::RPAREN)->image; break;
 			default:
 				if (!$this->nextAfterSpace(TokenManager::RBRACK)) {
 					switch ($this->getNextTokenKind()) {
 					case TokenManager::SPACE:	$s .= $this->consumeToken(TokenManager::SPACE)->image; break;
 					case TokenManager::TAB:		$this->consumeToken(TokenManager::TAB); $s .= "    "; break;
 					}
 				}
 			}
 		} while($this->resourceHasElementAhead());
 		$text->setValue($s);
 		tree.closeScope(text);
 	}

 	private function resourceUrl() {
 		$this->consumeToken(TokenManager::LPAREN);
 		$this->whiteSpace();
 		$ref = resourceUrlText();
 		$this->whiteSpace();
 		$this->consumeToken(TokenManager::RPAREN);
 		return $ref;
 	}

 	private function resourceUrlText() {
 		$s;
 		while ($this->resourceTextHasElementsAhead()) {
 			switch ($this->getNextTokenKind()) {
 			case TokenManager::ASTERISK: 		$s .= $this->consumeToken(TokenManager::ASTERISK)->image; break;
 			case TokenManager::BACKSLASH:		$s .= $this->consumeToken(TokenManager::BACKSLASH)->image; break;
 			case TokenManager::BACKTICK:		$s .= $this->consumeToken(TokenManager::BACKTICK)->image; break;
 			case TokenManager::CHAR_SEQUENCE:	$s .= $this->consumeToken(TokenManager::CHAR_SEQUENCE)->image; break;
 			case TokenManager::COLON:			$s .= $this->consumeToken(TokenManager::COLON)->image; break;
 			case TokenManager::DASH:			$s .= $this->consumeToken(TokenManager::DASH)->image; break;
 			case TokenManager::DIGITS:			$s .= $this->consumeToken(TokenManager::DIGITS)->image; break;
 			case TokenManager::DOT:				$s .= $this->consumeToken(TokenManager::DOT)->image; break;
 			case TokenManager::EQ:				$s .= $this->consumeToken(TokenManager::EQ)->image; break;
 			case TokenManager::ESCAPED_CHAR:	$s .= substr($this->consumeToken(TokenManager::ESCAPED_CHAR)->image, 1); break;
 			case TokenManager::IMAGE_LABEL:		$s .= $this->consumeToken(TokenManager::IMAGE_LABEL)->image; break;
 			case TokenManager::GT:				$s .= $this->consumeToken(TokenManager::GT)->image; break;
 			case TokenManager::LBRACK:			$s .= $this->consumeToken(TokenManager::LBRACK)->image; break;
 			case TokenManager::LPAREN:			$s .= $this->consumeToken(TokenManager::LPAREN)->image; break;
 			case TokenManager::LT:				$s .= $this->consumeToken(TokenManager::LT)->image; break;
 			case TokenManager::RBRACK:			$s .= $this->consumeToken(TokenManager::RBRACK)->image; break;
 			case TokenManager::UNDERSCORE:		$s .= $this->consumeToken(TokenManager::UNDERSCORE)->image; break;
 			default:
 				if (!$this->nextAfterSpace(TokenManager::RPAREN)) {
 					switch ($this->getNextTokenKind()) {
 					case TokenManager::SPACE:		$s .= $this->consumeToken(TokenManager::SPACE)->image; break;
 					case TokenManager::TAB:			$this->consumeToken(TokenManager::TAB); $s .= "    "; break;
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
 			$this->strongMultilineContent();
 		}
 		$this->consumeToken(TokenManager::ASTERISK);
 		$this->tree->closeScope($strong);
 	}

 	private function strongMultilineContent() {
 		do {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array(modules, Module::IMAGES) && $this->hasImageAhead()) {
				$this->image();
 			} else if (in_array(modules, Module::LINKS) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array(modules, Module::CODE) && $this->hasCodeAhead()) {
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
 			$this->strongWithinEmMultilineContent();
 		}
 		$this->consumeToken(TokenManager::ASTERISK);
 		$this->tree->closeScope($strong);
 	}

 	private function strongWithinEmMultilineContent() {
 		do {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array(modules, Module::IMAGES) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array(modules, Module::LINKS) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array(modules, Module::CODE) && $this->hasCodeAhead()) {
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
 			} else if (in_array(modules, Module::IMAGES) && $this->hasImageAhead()) {
				$this->image();
 			} else if (in_array(modules, Module::LINKS) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array(modules, Module::CODE) && $this->hasCodeAhead()) {
 				$this->code();
 			} else {
 				switch ($this->getNextTokenKind()) {
 				case TokenManager::BACKTICK:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::BACKTICK)); break;
 				case TokenManager::LBRACK:		$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::LBRACK)); break;
 				case TokenManager::UNDERSCORE:	$this->tree->addSingleValue(new Text(), $this->consumeToken(TokenManager::UNDERSCORE)); break;
 				}
 			}
 		} while($this->strongWithinEmHasElementsAhead());
 		consumeToken(TokenManager::ASTERISK);
 		$this->tree->closeScope($strong);
 	}

 	private function emMultiline() {
 		$em = new Em();
 		$this->tree->openScope();
		$this->consumeToken(TokenManager::UNDERSCORE);
 		$this->emMultilineContent();
 		while ($this->textAhead()) {
 			$this->lineBreak();
 			$this->emMultilineContent();
 		}
 		$this->consumeToken(TokenManager::UNDERSCORE);
 		$this->tree->closeScope($em);
 	}

 	private function emMultilineContent() {
 		do {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array(modules, Module::IMAGES) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array(modules, Module::LINKS) && $this->hasLinkAhead()) {
				$this->link();
 			} else if (in_array(modules, Module::CODE) && $this->multilineAhead(TokenManager::BACKTICK)) {
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
 			$this->emWithinStrongMultilineContent();
 		}
 		$this->consumeToken(TokenManager::UNDERSCORE);
 		$this->tree->closeScope($em);
 	}

 	private function emWithinStrongMultilineContent() {
 		do {
 			if ($this->hasTextAhead()) {
 				$this->text();
 			} else if (in_array(modules, Module::IMAGES) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array(modules, Module::LINKS) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array(modules, Module::CODE) && $this->hasCodeAhead()) {
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
 			} else if (in_array(modules, Module::IMAGES) && $this->hasImageAhead()) {
 				$this->image();
 			} else if (in_array(modules, Module::LINKS) && $this->hasLinkAhead()) {
 				$this->link();
 			} else if (in_array(modules, Module::CODE) && hasCodeAhead()) {
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
 	 	$quoteLevel;
 	 	
 	 	if($this->getToken(1)->kind == TokenManager::EOL) {
    		$t;
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
    			return $t->kind != TokenManager::EOF && ($this->currentBlockLevel == 0 || $t->beginColumn >= $this->blockBeginColumn + 2) ;
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
				$i = $this->skip($i+1, TokenManager::SPACE, TokenManager::TAB);
				$quoteLevel = $this->newQuoteLevel($i);
				if($quoteLevel == $this->currentQuoteLevel) {
					$i = $this->skip($i, TokenManager::SPACE, TokenManager::TAB, TokenManager::GT);
					if($this->getToken($i)->kind == $token
						|| $this->getToken($i)->kind == TokenManager::EOL
						|| $this->getToken($i)->kind == TokenManager::DASH
						|| ($this->getToken($i)->kind == TokenManager::DIGITS && $this->getToken($i+1)->kind == TokenManager::DOT)
						|| ($this->getToken($i)->kind == TokenManager::BACKTICK && $this->getToken($i+1)->kind == TokenManager::BACKTICK && $this->getToken(i+2)->kind == TokenManager::BACKTICK)
						|| $this->headingAhead($i)) {
							return false;
						}
					} else {
						return false;
					}
				} else if(t.kind == EOF) {
					return false;
				}
			}
		}
		return false;
	}

	private function fencesAhead() {
		if($this->getToken(1)->kind == TokenManager::EOL) {
			$i = $this->skip(2, TokenManager::SPACE, TokenManager::TAB, TokenManager::GT);
			if($this->getToken($i)->kind == TokenManager::BACKTICK && $this->getToken($i+1)->kind == TokenManager::BACKTICK && $this->getToken($i+2).kind == TokenManager::BACKTICK) {
				$i = $this->skip($i+3, TokenManager::SPACE, TokenManager::TAB);
				return $this->getToken($i)->kind == TokenManager::EOL || $this->getToken($i)->kind == TokenManager::EOF;
			}
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
						return (t.kind == DIGITS && getToken(i+1).kind == DOT && t.beginColumn >= listBeginColumn);
					}
					return t.kind == DASH && t.beginColumn >= listBeginColumn;
				}
			}
		}
			return false;
	}

	private function textAhead() {
		if($this->getToken(1)->kind == TokenManager::EOL && $this->getToken(2)->kind != TokenManager::EOL) {
			$i = $this->skip(2, TokenMananger::SPACE, TokenManager::TAB);
			$quoteLevel = $this->newQuoteLevel($i);
			if($quoteLevel == $this->currentQuoteLevel || !in_array(modules, Module::BLOCKQUOTES)) {
				$i = $this->skip($i, TokenManager::SPACE, TokenManager::TAB, TokenManager::GT);
				$t = $this->getToken($i);
				return $this->getToken($i)->kind != TokenManager::EOL
					&& !(in_array(modules, Module::LISTS) && $t->kind == TokenManager::DASH)
					&& !(in_array(modules, Module::LISTS) && $t->kind == TokenManager::DIGITS && $this->getToken($i+1)->kind == TokenManager::DOT) && !($this->getToken($i)->kind == TokenManager::BACKTICK && $this->getToken($i+1)->kind == TokenManager::BACKTICK && $this->getToken($i+2)->kind == TokenManager::BACKTICK)
	                && !(in_array(modules, Module::HEADINGS) && $this->headingAhead($i));
// 	           return result;
// 	           } 
// 	}
// 	return false;
// }

// private boolean nextAfterSpace(Integer... tokens) {
// int i = skip(1, SPACE, TAB);
// return Arrays.asList(tokens).contains(getToken(i).kind);
// }

//  private int newQuoteLevel(int offset) {
//    int quoteLevel = 0;
//    for(int i=offset;;i++) {
//            Token t = getToken(i);
//            if(t.kind == GT) {
//                    quoteLevel++;
//        } else if(t.kind != SPACE && t.kind != TAB) {
//            return quoteLevel;
//        }

//    }
// }

// private int skip(int offset, Integer... tokens) {
// for(int i=offset;;i++) {
//  Token t = getToken(i);
//  if(t.kind == EOF || !Arrays.asList(tokens).contains(t.kind)) { return i; }
// }
// }
	
// 	private boolean hasOrderedListAhead() {
// 		lookAhead = 2;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanToken(DIGITS) && !scanToken(DOT);
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}
	
// 	private boolean hasFencedCodeBlockAhead() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanFencedCodeBlock();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}
	
	
// 	private boolean headingHasInlineElementsAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			Token xsp = scanPosition;
// 			if (scanTextTokens()) {
// 				scanPosition = xsp;
// 				if (scanImage()) {
// 					scanPosition = xsp;
// 					if (scanLink()) {
// 						scanPosition = xsp;
// 						if (scanStrong()) {
// 							scanPosition = xsp;
// 							if (scanEm()) {
// 								scanPosition = xsp;
// 								if (scanCode()) {
// 									scanPosition = xsp;
// 									if (scanLooseChar()) {
// 										return false;
// 									}
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
// 			return true;
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}
	
// 	private boolean hasTextAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanTextTokens();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasImageAhead() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanImage();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}
	
// 	private boolean blockquoteHasEmptyLineAhead() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanBlockquoteEmptyLine();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasStrongAhead() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanStrong();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasEmAhead() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanEm();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasCodeAhead() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanCode();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean blockquoteHasAnyBlockElementseAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanMoreBlockElements();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasBlockquoteEmptyLinesAhead() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanBlockquoteEmptyLines();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean listItemHasInlineElements() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanMoreBlockElements();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean fencedCodeBlockHasInlineTokens() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanFencedCodeBlockTokens();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasInlineTextAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanTextTokens();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasInlineElementAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanInlineElement();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean imageHasAnyElements() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanImageElement();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasResourceTextAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanResourceElements();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean linkHasAnyElements() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanLinkElement();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasResourceUrlAhead() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanResourceUrl();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean resourceHasElementAhead() {
// 		lookAhead = 2;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanResourceElement();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean resourceTextHasElementsAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanResourceTextElement();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasEmWithinStrongMultiline() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanEmWithinStrongMultiline();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean strongMultilineHasElementsAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanStrongMultilineElements();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}
	
// 	private boolean strongWithinEmMultilineHasElementsAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanStrongWithinEmMultilineElements();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasImage() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanImage();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasLinkAhead() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanLink();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean strongEmWithinStrongAhead() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanEmWithinStrong();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean strongHasElements() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanStrongElements();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean strongWithinEmHasElementsAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanStrongWithinEmElements();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean hasStrongWithinEmMultilineAhead() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanStrongWithinEmMultiline();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean emMultilineContentHasElementsAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanEmMultilineContentElements();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean emWithinStrongMultilineContentHasElementsAhaed() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanEmWithinStrongMultilineContent();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean emHasStrongWithinEm() {
// 		lookAhead = 2147483647;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanStrongWithinEm();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean emHasElements() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanEmElements();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean emWithinStrongHasElementsAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanEmWithinStrongElements();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean codeTextHasAnyTokenAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanCodeTextTokens();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean textHasTokensAhead() {
// 		lookAhead = 1;
// 		lastPosition = scanPosition = token;
// 		try {
// 			return !scanText();
// 		} catch (LookaheadSuccess ls) {
// 			return true;
// 		}
// 	}

// 	private boolean scanLooseChar() {
// 		Token xsp = scanPosition;
// 		if (scanToken(ASTERISK)) {
// 			scanPosition = xsp;
// 			if (scanToken(BACKTICK)) {
// 				scanPosition = xsp;
// 				if (scanToken(LBRACK)) {
// 					scanPosition = xsp;
// 					return scanToken(UNDERSCORE);
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanText() {
// 		Token xsp = scanPosition;
// 		if (scanToken(BACKSLASH)) {
// 			scanPosition = xsp;
// 			if (scanToken(CHAR_SEQUENCE)) {
// 				scanPosition = xsp;
// 				if (scanToken(COLON)) {
// 					scanPosition = xsp;
// 					if (scanToken(DASH)) {
// 						scanPosition = xsp;
// 						if (scanToken(DIGITS)) {
// 							scanPosition = xsp;
// 							if (scanToken(DOT)) {
// 								scanPosition = xsp;
// 								if (scanToken(EQ)) {
// 									scanPosition = xsp;
// 									if (scanToken(ESCAPED_CHAR)) {
// 										scanPosition = xsp;
// 										if (scanToken(GT)) {
// 											scanPosition = xsp;
// 											if (scanToken(IMAGE_LABEL)) {
// 												scanPosition = xsp;
// 												if (scanToken(LPAREN)) {
// 													scanPosition = xsp;
// 													if (scanToken(LT)) {
// 														scanPosition = xsp;
// 														if (scanToken(RBRACK)) {
// 															scanPosition = xsp;
// 															if (scanToken(RPAREN)) {
// 																scanPosition = xsp;
// 																lookingAhead = true;
// 																semanticLookAhead = !nextAfterSpace(EOL, EOF);
// 																lookingAhead = false;
// 																if (!semanticLookAhead || scanWhitspaceToken()) {
// 																	return true;
// 																}
// 															}
// 														}
// 													}
// 												}
// 											}
// 										}
// 									}
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanTextTokens() {
// 		if (scanText()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanText()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return false;
// 	}
	
// 	private boolean scanCodeTextTokens() {
// 		Token xsp = scanPosition;
// 		if (scanToken(ASTERISK)) {
// 			scanPosition = xsp;
// 			if (scanToken(BACKSLASH)) {
// 				scanPosition = xsp;
// 				if (scanToken(CHAR_SEQUENCE)) {
// 					scanPosition = xsp;
// 					if (scanToken(COLON)) {
// 						scanPosition = xsp;
// 						if (scanToken(DASH)) {
// 							scanPosition = xsp;
// 							if (scanToken(DIGITS)) {
// 								scanPosition = xsp;
// 								if (scanToken(DOT)) {
// 									scanPosition = xsp;
// 									if (scanToken(EQ)) {
// 										scanPosition = xsp;
// 										if (scanToken(ESCAPED_CHAR)) {
// 											scanPosition = xsp;
// 											if (scanToken(IMAGE_LABEL)) {
// 												scanPosition = xsp;
// 												if (scanToken(LT)) {
// 													scanPosition = xsp;
// 													if (scanToken(LBRACK)) {
// 														scanPosition = xsp;
// 														if (scanToken(RBRACK)) {
// 															scanPosition = xsp;
// 															if (scanToken(LPAREN)) {
// 																scanPosition = xsp;
// 																if (scanToken(GT)) {
// 																	scanPosition = xsp;
// 																	if (scanToken(RPAREN)) {
// 																		scanPosition = xsp;
// 																		if (scanToken(UNDERSCORE)) {
// 																			scanPosition = xsp;
// 																			lookingAhead = true;
// 																			semanticLookAhead = !nextAfterSpace(EOL, EOF);
// 																			lookingAhead = false;
// 																			if (!semanticLookAhead || scanWhitspaceToken()) {
// 																				return true;
// 																			}
// 																		}
// 																	}
// 																}
// 															}
// 														}
// 													}
// 												}
// 											}
// 										}
// 									}
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}


// 	private boolean scanCode() {
// 		return scanToken(BACKTICK) || scanCodeTextTokensAhead() || scanToken(BACKTICK);
// 	}

// 	private boolean scanCodeMultiline() {
// 		if (scanToken(BACKTICK) || scanCodeTextTokensAhead()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (hasCodeTextOnNextLineAhead()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return scanToken(BACKTICK);
// 	}
	
// 	private boolean scanCodeTextTokensAhead() {
// 		if (scanCodeTextTokens()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanCodeTextTokens()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean hasCodeTextOnNextLineAhead() {
// 		if (scanWhitespaceTokenBeforeEol()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanToken(GT)) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return scanCodeTextTokensAhead();
// 	}
	
// 	private boolean scanWhitspaceTokens() {
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanWhitspaceToken()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanWhitespaceTokenBeforeEol() {
// 		return scanWhitspaceTokens() || scanToken(EOL);
// 	}

// 	private boolean scanEmWithinStrongElements() {
// 		Token xsp = scanPosition;
// 		if (scanTextTokens()) {
// 			scanPosition = xsp;
// 			if (scanImage()) {
// 				scanPosition = xsp;
// 				if (scanLink()) {
// 					scanPosition = xsp;
// 					if (scanCode()) {
// 						scanPosition = xsp;
// 						if (scanToken(ASTERISK)) {
// 							scanPosition = xsp;
// 							if (scanToken(BACKTICK)) {
// 								scanPosition = xsp;
// 								return scanToken(LBRACK);
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanEmWithinStrong() {
// 		if (scanToken(UNDERSCORE) || scanEmWithinStrongElements()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanEmWithinStrongElements()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return scanToken(UNDERSCORE);
// 	}

// 	private boolean scanEmElements() {
// 		Token xsp = scanPosition;
// 		if (scanTextTokens()) {
// 			scanPosition = xsp;
// 			if (scanImage()) {
// 				scanPosition = xsp;
// 				if (scanLink()) {
// 					scanPosition = xsp;
// 					if (scanCode()) {
// 						scanPosition = xsp;
// 						if (scanStrongWithinEm()) {
// 							scanPosition = xsp;
// 							if (scanToken(ASTERISK)) {
// 								scanPosition = xsp;
// 								if (scanToken(BACKTICK)) {
// 									scanPosition = xsp;
// 									return scanToken(LBRACK);
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanEm() {
// 		if (scanToken(UNDERSCORE) || scanEmElements()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanEmElements()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return scanToken(UNDERSCORE);
// 	}

// 	private boolean scanEmWithinStrongMultilineContent() {
// 		Token xsp = scanPosition;
// 		if (scanTextTokens()) {
// 			scanPosition = xsp;
// 			if (scanImage()) {
// 				scanPosition = xsp;
// 				if (scanLink()) {
// 					scanPosition = xsp;
// 					if (scanCode()) {
// 						scanPosition = xsp;
// 						if (scanToken(ASTERISK)) {
// 							scanPosition = xsp;
// 							if (scanToken(BACKTICK)) {
// 								scanPosition = xsp;
// 								return scanToken(LBRACK);
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean hasNoEmWithinStrongMultilineContentAhead() {
// 		if (scanEmWithinStrongMultilineContent()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanEmWithinStrongMultilineContent()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanEmWithinStrongMultiline() {
// 		if (scanToken(UNDERSCORE) || hasNoEmWithinStrongMultilineContentAhead()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanWhitespaceTokenBeforeEol() || hasNoEmWithinStrongMultilineContentAhead()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return scanToken(UNDERSCORE);
// 	}

// 	private boolean scanEmMultilineContentElements() {
// 		Token xsp = scanPosition;
// 		if (scanTextTokens()) {
// 			scanPosition = xsp;
// 			if (scanImage()) {
// 				scanPosition = xsp;
// 				if (scanLink()) {
// 					scanPosition = xsp;
// 					lookingAhead = true;
// 					semanticLookAhead = multilineAhead(BACKTICK);
// 					lookingAhead = false;
// 					if (!semanticLookAhead || scanCodeMultiline()) {
// 						scanPosition = xsp;
// 						if (scanStrongWithinEmMultiline()) {
// 							scanPosition = xsp;
// 							if (scanToken(ASTERISK)) {
// 								scanPosition = xsp;
// 								if (scanToken(BACKTICK)) {
// 									scanPosition = xsp;
// 									return scanToken(LBRACK);
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanStrongWithinEmElements() {
// 		Token xsp = scanPosition;
// 		if (scanTextTokens()) {
// 			scanPosition = xsp;
// 			if (scanImage()) {
// 				scanPosition = xsp;
// 				if (scanLink()) {
// 					scanPosition = xsp;
// 					if (scanCode()) {
// 						scanPosition = xsp;
// 						if (scanToken(BACKTICK)) {
// 							scanPosition = xsp;
// 							if (scanToken(LBRACK)) {
// 								scanPosition = xsp;
// 								return scanToken(UNDERSCORE);
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanStrongWithinEm() {
// 		if (scanToken(ASTERISK) || scanStrongWithinEmElements()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanStrongWithinEmElements()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return scanToken(ASTERISK);
// 	}

// 	private boolean scanStrongElements() {
// 		Token xsp = scanPosition;
// 		if (scanTextTokens()) {
// 			scanPosition = xsp;
// 			if (scanImage()) {
// 				scanPosition = xsp;
// 				if (scanLink()) {
// 					scanPosition = xsp;
// 					lookingAhead = true;
// 					semanticLookAhead = multilineAhead(BACKTICK);
// 					lookingAhead = false;
// 					if (!semanticLookAhead || scanCodeMultiline()) {
// 						scanPosition = xsp;
// 						if (scanEmWithinStrong()) {
// 							scanPosition = xsp;
// 							if (scanToken(BACKTICK)) {
// 								scanPosition = xsp;
// 								if (scanToken(LBRACK)) {
// 									scanPosition = xsp;
// 									return scanToken(UNDERSCORE);
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanStrong() {
// 		if (scanToken(ASTERISK) || scanStrongElements()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanStrongElements()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return scanToken(ASTERISK);
// 	}

// 	private boolean scanStrongWithinEmMultilineElements() {
// 		Token xsp = scanPosition;
// 		if (scanTextTokens()) {
// 			scanPosition = xsp;
// 			if (scanImage()) {
// 				scanPosition = xsp;
// 				if (scanLink()) {
// 					scanPosition = xsp;
// 					if (scanCode()) {
// 						scanPosition = xsp;
// 						if (scanToken(BACKTICK)) {
// 							scanPosition = xsp;
// 							if (scanToken(LBRACK)) {
// 								scanPosition = xsp;
// 								return scanToken(UNDERSCORE);
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanForMoreStrongWithinEmMultilineElements() {
// 		if (scanStrongWithinEmMultilineElements()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanStrongWithinEmMultilineElements()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanStrongWithinEmMultiline() {
// 		if (scanToken(ASTERISK) || scanForMoreStrongWithinEmMultilineElements()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanWhitespaceTokenBeforeEol() || scanForMoreStrongWithinEmMultilineElements()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return scanToken(ASTERISK);
// 	}

// 	private boolean scanStrongMultilineElements() {
// 		Token xsp = scanPosition;
// 		if (scanTextTokens()) {
// 			scanPosition = xsp;
// 			if (scanImage()) {
// 				scanPosition = xsp;
// 				if (scanLink()) {
// 					scanPosition = xsp;
// 					if (scanCode()) {
// 						scanPosition = xsp;
// 						if (scanEmWithinStrongMultiline()) {
// 							scanPosition = xsp;
// 							if (scanToken(BACKTICK)) {
// 								scanPosition = xsp;
// 								if (scanToken(LBRACK)) {
// 									scanPosition = xsp;
// 									return scanToken(UNDERSCORE);
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanResourceTextElement() {
// 		Token xsp = scanPosition;
// 		if (scanToken(ASTERISK)) {
// 			scanPosition = xsp;
// 			if (scanToken(BACKSLASH)) {
// 				scanPosition = xsp;
// 				if (scanToken(BACKTICK)) {
// 					scanPosition = xsp;
// 					if (scanToken(CHAR_SEQUENCE)) {
// 						scanPosition = xsp;
// 						if (scanToken(COLON)) {
// 							scanPosition = xsp;
// 							if (scanToken(DASH)) {
// 								scanPosition = xsp;
// 								if (scanToken(DIGITS)) {
// 									scanPosition = xsp;
// 									if (scanToken(DOT)) {
// 										scanPosition = xsp;
// 										if (scanToken(EQ)) {
// 											scanPosition = xsp;
// 											if (scanToken(ESCAPED_CHAR)) {
// 												scanPosition = xsp;
// 												if (scanToken(IMAGE_LABEL)) {
// 													scanPosition = xsp;
// 													if (scanToken(GT)) {
// 														scanPosition = xsp;
// 														if (scanToken(LBRACK)) {
// 															scanPosition = xsp;
// 															if (scanToken(LPAREN)) {
// 																scanPosition = xsp;
// 																if (scanToken(LT)) {
// 																	scanPosition = xsp;
// 																	if (scanToken(RBRACK)) {
// 																		scanPosition = xsp;
// 																		if (scanToken(UNDERSCORE)) {
// 																			scanPosition = xsp;
// 																			lookingAhead = true;
// 																			semanticLookAhead = !nextAfterSpace(RPAREN);
// 																			lookingAhead = false;
// 																			return (!semanticLookAhead || scanWhitspaceToken());
// 																		}
// 																	}
// 																}
// 															}
// 														}
// 													}
// 												}
// 											}
// 										}
// 									}
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanImageElement() {
// 		Token xsp = scanPosition;
// 		if (scanResourceElements()) {
// 			scanPosition = xsp;
// 			if (scanLooseChar()) {
// 				return true;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanResourceTextElements() {
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanResourceTextElement()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanResourceUrl() {
// 		return scanToken(LPAREN) || scanWhitspaceTokens() || scanResourceTextElements() || scanWhitspaceTokens() || scanToken(RPAREN);
// 	}

// 	private boolean scanLinkElement() {
// 		Token xsp = scanPosition;
// 		if (scanImage()) {
// 			scanPosition = xsp;
// 			if (scanStrong()) {
// 				scanPosition = xsp;
// 				if (scanEm()) {
// 					scanPosition = xsp;
// 					if (scanCode()) {
// 						scanPosition = xsp;
// 						if (scanResourceElements()) {
// 							scanPosition = xsp;
// 							return scanLooseChar();
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanResourceElement() {
// 		Token xsp = scanPosition;
// 		if (scanToken(BACKSLASH)) {
// 			scanPosition = xsp;
// 			if (scanToken(COLON)) {
// 				scanPosition = xsp;
// 				if (scanToken(CHAR_SEQUENCE)) {
// 					scanPosition = xsp;
// 					if (scanToken(DASH)) {
// 						scanPosition = xsp;
// 						if (scanToken(DIGITS)) {
// 							scanPosition = xsp;
// 							if (scanToken(DOT)) {
// 								scanPosition = xsp;
// 								if (scanToken(EQ)) {
// 									scanPosition = xsp;
// 									if (scanToken(ESCAPED_CHAR)) {
// 										scanPosition = xsp;
// 										if (scanToken(IMAGE_LABEL)) {
// 											scanPosition = xsp;
// 											if (scanToken(GT)) {
// 												scanPosition = xsp;
// 												if (scanToken(LPAREN)) {
// 													scanPosition = xsp;
// 													if (scanToken(LT)) {
// 														scanPosition = xsp;
// 														if (scanToken(RPAREN)) {
// 															scanPosition = xsp;
// 															lookingAhead = true;
// 															semanticLookAhead = !nextAfterSpace(RBRACK);
// 															lookingAhead = false;
// 															return (!semanticLookAhead || scanWhitspaceToken());
// 														}
// 													}
// 												}
// 											}
// 										}
// 									}
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanResourceElements() {
// 		if (scanResourceElement()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanResourceElement()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanLink() {
// 		if (scanToken(LBRACK) || scanWhitspaceTokens() || scanLinkElement()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanLinkElement()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		if (scanWhitspaceTokens() || scanToken(RBRACK)) {
// 			return true;
// 		}
// 		xsp = scanPosition;
// 		if (scanResourceUrl()) {
// 			scanPosition = xsp;
// 		}
// 		return false;
// 	}

// 	private boolean scanImage() {
// 		if (scanToken(LBRACK) || scanWhitspaceTokens() || scanToken(IMAGE_LABEL) || scanImageElement()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanImageElement()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		if (scanWhitspaceTokens() || scanToken(RBRACK)) {
// 			return true;
// 		}
// 		xsp = scanPosition;
// 		if (scanResourceUrl()) {
// 			scanPosition = xsp;
// 		}
// 		return false;
// 	}

// 	private boolean scanInlineElement() {
// 		Token xsp = scanPosition;
// 		if (scanTextTokens()) {
// 			scanPosition = xsp;
// 			if (scanImage()) {
// 				scanPosition = xsp;
// 				if (scanLink()) {
// 					scanPosition = xsp;
// 					lookingAhead = true;
// 					semanticLookAhead = multilineAhead(ASTERISK);
// 					lookingAhead = false;
// 					if (!semanticLookAhead || scanToken(ASTERISK)) {
// 						scanPosition = xsp;
// 						lookingAhead = true;
// 						semanticLookAhead = multilineAhead(UNDERSCORE);
// 						lookingAhead = false;
// 						if (!semanticLookAhead || scanToken(UNDERSCORE)) {
// 							scanPosition = xsp;
// 							lookingAhead = true;
// 							semanticLookAhead = multilineAhead(BACKTICK);
// 							lookingAhead = false;
// 							if (!semanticLookAhead || scanCodeMultiline()) {
// 								scanPosition = xsp;
// 								return scanLooseChar();
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanParagraph() {
// 		Token xsp;
// 		if (scanInlineElement()) {
// 			return true;
// 		}
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanInlineElement()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanForCodeLanguageElement() {
// 		Token xsp = scanPosition;
// 		if (scanToken(CHAR_SEQUENCE)) {
// 			scanPosition = xsp;
// 			if (scanToken(BACKTICK)) {
// 				return true;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanForCodeLanguageElements() {
// 		if (scanForCodeLanguageElement()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanForCodeLanguageElement()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanWhitspaceToken() {
// 		Token xsp = scanPosition;
// 		if (scanToken(SPACE)) {
// 			scanPosition = xsp;
// 			if (scanToken(TAB)) {
// 				return true;
// 			}
// 		}
// 		return false;
// 	}
// 	private boolean scanNoFencedCodeBlockAhead() {
// 		if (scanToken(EOL) || scanWhitspaceTokens() || scanToken(BACKTICK)) {
// 			return true;
// 		}
// 		Token xsp;
// 		while (true) {
// 			xsp = scanPosition;
// 			if (scanToken(BACKTICK)) {
// 				scanPosition = xsp;
// 				break;
// 			}
// 		}
// 		return false;
// 	}
	
// 	private boolean scanFencedCodeBlockTokens() {
// 		Token xsp = scanPosition;
// 		if (scanToken(ASTERISK)) {
// 			scanPosition = xsp;
// 			if (scanToken(BACKSLASH)) {
// 				scanPosition = xsp;
// 				if (scanToken(CHAR_SEQUENCE)) {
// 					scanPosition = xsp;
// 					if (scanToken(COLON)) {
// 						scanPosition = xsp;
// 						if (scanToken(DASH)) {
// 							scanPosition = xsp;
// 							if (scanToken(DIGITS)) {
// 								scanPosition = xsp;
// 								if (scanToken(DOT)) {
// 									scanPosition = xsp;
// 									if (scanToken(EQ)) {
// 										scanPosition = xsp;
// 										if (scanToken(ESCAPED_CHAR)) {
// 											scanPosition = xsp;
// 											if (scanToken(IMAGE_LABEL)) {
// 												scanPosition = xsp;
// 												if (scanToken(LT)) {
// 													scanPosition = xsp;
// 													if (scanToken(GT)) {
// 														scanPosition = xsp;
// 														if (scanToken(LBRACK)) {
// 															scanPosition = xsp;
// 															if (scanToken(RBRACK)) {
// 																scanPosition = xsp;
// 																if (scanToken(LPAREN)) {
// 																	scanPosition = xsp;
// 																	if (scanToken(RPAREN)) {
// 																		scanPosition = xsp;
// 																		if (scanToken(UNDERSCORE)) {
// 																			scanPosition = xsp;
// 																			if (scanToken(BACKTICK)) {
// 																				scanPosition = xsp;
// 																				lookingAhead = true;
// 																				semanticLookAhead = !nextAfterSpace(EOL, EOF);
// 																				lookingAhead = false;
// 																				if (!semanticLookAhead || scanWhitspaceToken()) {
// 																					scanPosition = xsp;
// 																					lookingAhead = true;
// 																					semanticLookAhead = !fencesAhead();
// 																					lookingAhead = false;
// 																					return !semanticLookAhead || scanToken(EOL) || scanWhitspaceTokens();
// 																				}
// 																			}
// 																		}
// 																	}
// 																}
// 															}
// 														}
// 													}
// 												}
// 											}
// 										}
// 									}
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanFencedCodeBlock() {
// 		if (scanToken(BACKTICK) || scanToken(BACKTICK) || scanToken(BACKTICK)) {
// 			return true;
// 		}
// 		Token xsp;
// 		while (true) {
// 			xsp = scanPosition;
// 			if (scanToken(BACKTICK)) {
// 				scanPosition = xsp;
// 				break;
// 			}
// 		}
// 		if (scanWhitspaceTokens()) {
// 			return true;
// 		}
// 		xsp = scanPosition;
// 		if (scanForCodeLanguageElements()) {
// 			scanPosition = xsp;
// 		}
// 		xsp = scanPosition;
// 		if (scanToken(EOL) || scanWhitspaceTokens()) {
// 			scanPosition = xsp;
// 		}
// 		while (true) {
// 			xsp = scanPosition;
// 			if (scanFencedCodeBlockTokens()) {
// 				scanPosition = xsp;
// 				break;
// 			}
// 		}
// 		xsp = scanPosition;
// 		if (scanNoFencedCodeBlockAhead()) {
// 			scanPosition = xsp;
// 		}
// 		return false;
// 	}

// 	private boolean scanBlockquoteEmptyLines() {
// 		return scanBlockquoteEmptyLine() || scanToken(EOL);
// 	}

// 	private boolean scanBlockquoteEmptyLine() {
// 		if (scanToken(EOL) || scanWhitspaceTokens() || scanToken(GT) || scanWhitspaceTokens()) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanToken(GT) || scanWhitspaceTokens()) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanForHeadersigns() {
// 		if (scanToken(EQ)) {
// 			return true;
// 		}
// 		Token xsp;
// 		loop: while (true) {
// 			xsp = scanPosition;
// 			if (scanToken(EQ)) {
// 				scanPosition = xsp;
// 				break loop;
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanMoreBlockElements() {
// 		Token xsp = scanPosition;
// 		lookingAhead = true;
// 		semanticLookAhead = headingAhead(1);
// 		lookingAhead = false;
// 		if (!semanticLookAhead || scanForHeadersigns()) {
// 			scanPosition = xsp;
// 			if (scanToken(GT)) {
// 				scanPosition = xsp;
// 				if (scanToken(DASH)) {
// 					scanPosition = xsp;
// 					if (scanToken(DIGITS) || scanToken(DOT)) {
// 						scanPosition = xsp;
// 						if (scanFencedCodeBlock()) {
// 							scanPosition = xsp;
// 							return scanParagraph();
// 						}
// 					}
// 				}
// 			}
// 		}
// 		return false;
// 	}

// 	private boolean scanToken(int kind) {
// 		if (scanPosition == lastPosition) {
// 			lookAhead--;
// 			if (scanPosition.next == null) {
// 				lastPosition = scanPosition = scanPosition.next = tm.getNextToken();
// 			} else {
// 				lastPosition = scanPosition = scanPosition.next;
// 			}
// 		} else {
// 			scanPosition = scanPosition.next;
// 		}
// 		if (scanPosition.kind != kind) {
// 			return true;
// 		}
// 		if (lookAhead == 0 && scanPosition == lastPosition) {
// 			throw lookAheadSuccess;
// 		}
// 		return false;
// 	}
	
 	private function getNextTokenKind() {
 		if($this->nextTokenKind != -1) { 
 			return $this->nextTokenKind; 
 		} else if (($this->nextToken = $this->token->next) == null) {
 			$this->token->next = $this->tm->getNextToken();
			return ($nextTokenKind = $token->next->kind);
 		}
 		return ($this->nextTokenKind = $this->nextToken->kind);
 	}
	
 	private function consumeToken($kind) {
 		$old = $this->token;
// 		if ($this->token->next != null) {
// 			token = token.next;
// 		} else {
// 			token = token.next = tm.getNextToken();
// 		}
// 		nextTokenKind = -1;
// 		if (token.kind == kind) {
// 			return token;
// 		}
// 		token = old;
 		return $this->token;
 	}
	
// 	private Token getToken(int index) {
// 		Token t = lookingAhead ? scanPosition : token;
// 		for (int i = 0; i < index; i++) {
// 			if (t.next != null) {
// 				t = t.next;
// 			} else {
// 				t = t.next = tm.getNextToken();
// 			}
// 		}
// 		return t;
// 	}
	
// 	public void setModules(Module... modules) {
// 		this.modules = Arrays.asList(modules);
// 	}
	
}
