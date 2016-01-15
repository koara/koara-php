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
    private $reader;
    private $buffer = [];
    private $maxNextCharInd = 0;
    private $inBuf = 0;
    private $tabSize = 4;

    public function __construct($reader) {
        $this->reader = $reader;
    }

    public function beginToken() {
        $this->tokenBegin = -1;
        $c = $this->readChar();
        $this->tokenBegin = $this->bufpos;
        return $c;
    }

    public function readChar() {
        if ($this->inBuf > 0) {
            --$this->inBuf;
            if (++$this->bufpos === $this->bufsize) {
                $this->bufpos = 0;
            }
            return $this->buffer[$this->bufpos];
        }
        if (++$this->bufpos >= $this->maxNextCharInd) {
            $this->fillBuff();
        }
        $c = $this->buffer[$this->bufpos];
        $this->updateLineColumn(ord($c));
        return $c;
    }

    private function fillBuff() {
        if ($this->maxNextCharInd === $this->available) {
            if ($this->available === $this->bufsize) {
                $this->bufpos = 0;
                $this->maxNextCharInd = 0;
                if ($this->tokenBegin > 2048) {
                    $this->available = $this->tokenBegin;
                }
            } else {
                $this->available = $this->bufsize;
            }
        }
        $i;
  
        try {
            if (($i = $this->reader->read($this->buffer, $this->maxNextCharInd, $this->available - $this->maxNextCharInd)) === -1) {
                throw new Exception();
            } else {
                $this->maxNextCharInd += $i;
            }
        } catch (Exception $e) {
            --$this->bufpos;
            $this->backup(0);
            if ($this->tokenBegin === -1) {
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
        case 10:
            $this->prevCharIsLF = true;
            break;
        case 9:
            $this->column--;
            $this->column += ($this->tabSize - ($this->column % $this->tabSize));
            break;
        }
        $this->bufline[$this->bufpos] = $this->line;
        $this->bufcolumn[$this->bufpos] = $this->column;
    }

    public function getImage() {
        if ($this->bufpos >= $this->tokenBegin) {
        	return implode(array_slice($this->buffer, $this->tokenBegin, $this->bufpos - $this->tokenBegin + 1));
        } else {
        	return implode(array_slice($this->buffer, $this->tokenBegin, $this->bufsize - $this->tokenBegin))
        		.implode(array_slice($this->buffer, 0, $this->bufpos + 1));
        }
    }

	public function getEndColumn() {
		return array_key_exists($this->tokenBegin, $this->bufcolumn) ? $this->bufcolumn[$this->bufpos] : 0;
	}

	public function getEndLine() {
		return array_key_exists($this->tokenBegin, $this->bufline) ? $this->bufline[$this->bufpos] : 0;
	}

	public function getBeginColumn() {
		return array_key_exists($this->tokenBegin, $this->bufcolumn) ? $this->bufcolumn[$this->tokenBegin] : 0;
	}

	public function getBeginLine() {
		return array_key_exists($this->tokenBegin, $this->bufline) ? $this->bufline[$this->tokenBegin] : 0;
	}
    

}
