<?php
namespace Koara;

use Exception;

class CharStream {

	private $available = 4096;
	private $bufsize = 4096;
	private $tokenBegin;
	private $bufcolumn = [];
	private $bufpos = -1;
	private $bufline = [];
	private $column = 0;
	private $line = 1;
	private $prevCharIsLF;
	private $resource;
	private $buffer;
	private $maxNextCharInd = 0;
	private $inBuf = 0;
	private $tabSize = 4;

	public function __construct($resource) {
 		$this->resource = $resource;
	}

	public function beginToken() {
		$this->tokenBegin = -1;
		$c = $this->readChar();
		$this->tokenBegin = $this->bufpos;
		return $c;
	}

	public function readChar() {
		if ($this->inBuf > 0) {
			--$inBuf;
			if (++$this->bufpos == $this->bufsize) {
				$this->bufpos = 0;
			}
			return substr($this->buffer, $this->bufpos, 1);
		}
		if (++$this->bufpos >= $this->maxNextCharInd) {
			$this->fillBuff();
		}

		$c = substr($this->buffer, $this->bufpos, 1);
		$this->updateLineColumn($c);
		return $c;
	}

	private function fillBuff() {
		if ($this->maxNextCharInd == $this->available) {
			if ($this->available == $this->bufsize) {
				$this->bufpos = 0;
				$this->maxNextCharInd = 0;
				if ($this->tokenBegin > 2048) {
					$this->available = $this->tokenBegin;
				}
			} else {
				$this->available = $this->bufsize;
			}
		}
 		try {
 			$this->buffer = file_get_contents($this->resource, false, null, $this->maxNextCharInd, $this->available - $this->maxNextCharInd);
 			if($this->buffer == NULL) {
 				throw new Exception('No more data');
 			} else {
 				$this->maxNextCharInd += strlen($this->buffer);
 			}
 		} catch (Exception $e) {
 			--$this->bufpos;
 			$this->backup(0);
 			if ($this->tokenBegin == -1) {
				$this->tokenBegin = $this->bufpos;
			}
			throw $e;
		}
 	}

	public function backup($amount) {
		$this->inBuf += $amount;
		if (($this->bufpos -= $amount) < 0) {
			$this->bufpos += $this->bufsize;
		}
	}

	private function updateLineColumn($c) {
		$this->column++;
		if ($this->prevCharIsLF) {
			$this->prevCharIsLF = false;
			$this->column = 1;
			$this->line += $this->column;
		}

		switch ($c) {
			case '\n': $this->prevCharIsLF = true; break;
			case '\t':
				$this->column--;
				$this->column += ($this->tabSize - ($this->column % $this->tabSize));
				break;
		}
		$this->bufline[$this->bufpos] = $this->line;
		$this->bufcolumn[$this->bufpos] = $this->column;
	}

	public function getImage() {
		if ($this->bufpos >= $this->tokenBegin) {
			return substr($this->buffer, $this->tokenBegin, $this->bufpos - $this->tokenBegin + 1);
 		} else {
 			return substr($this->buffer, $this->tokenBegin, $this->bufsize - $this->tokenBegin).substr($this->buffer, 0, $this->bufpos + 1);
 		}
 	}

	public function getEndColumn() {
		return $this->bufcolumn[$this->bufpos];
	}

	public function getEndLine() {
		return $this->bufline[$this->bufpos];
	}

	public function getBeginColumn() {
		return $this->bufcolumn[$this->tokenBegin];
	}

	public function getBeginLine() {
		return $this->bufline[$this->tokenBegin];
	}

}
