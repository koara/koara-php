<?php

namespace Koara;

class CharStream
{
    private $available = 4096;
    private $bufsize = 4096;
    private $tokenBegin;
    private $bufcolumn = [];
    private $bufpos = -1;
    private $bufline = [];
    private $column = 0;
    private $line = 1;
    private $reader;
    private $buffer = [];
    private $maxNextCharInd = 0;
    private $inBuf = 0;
    private $prevCharIsLF;
    private $tabSize = 4;

    public function __construct($reader)
    {
        $this->reader = $reader;
    }

    public function beginToken()
    {
        $this->tokenBegin = -1;
        $c = $this->readChar();
        $this->tokenBegin = $this->bufpos;

        return $c;
    }

    protected function readChar()
    {
        if ($this->inBuf > 0) {
            --$this->inBuf;
            if (++$this->bufpos == $this->bufsize) {
                $this->bufpos = 0;
            }

            return $buffer[$this->bufpos];
        }
        if (++$this->bufpos >= $this->maxNextCharInd) {
            $this->fillBuff();
        }
        $c = $this->buffer[$this->bufpos];
        $this->updateLineColumn($c);

        return $c;
    }

    private function fillBuff()
    {
        // 		if (maxNextCharInd == available) {
    // 			if (available == bufsize) {
    // 				bufpos = 0;
    // 				maxNextCharInd = 0;
    // 				if (tokenBegin > 2048) {
    // 					available = tokenBegin;
    // 				} 
    // 			} else {
    // 				available = bufsize;
    // 			} 
    // 		}
    // 		int i;
    // 		try {
    // 			if ((i = reader.read(buffer, maxNextCharInd, available - maxNextCharInd)) == -1) {
    // 				reader.close();
    // 				throw new IOException();
    // 			} else {
    // 				maxNextCharInd += i;
    // 			}
    // 		} catch (IOException e) {
    // 			--bufpos;
    // 			backup(0);
    // 			if (tokenBegin == -1) {
    // 				tokenBegin = bufpos;
    // 			}
    // 			throw e;
    // 		}
    }

    protected function backup($amount)
    {
        $this->inBuf += $amount;
        if (($this->bufpos -= $amount) < 0) {
            $this->bufpos += $this->bufsize;
        }
    }

    private function updateLineColumn($c)
    {
        ++$this->column;
        if ($this->prevCharIsLF) {
            $this->prevCharIsLF = false;
            $this->column = 1;
            $this->line += $this->column++;
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

    // TODO: can token
    protected function getImage()
    {
        if ($this->bufpos >= $this->tokenBegin) {
            //return new String($this->buffer, $this->tokenBegin, $this->bufpos - $this->tokenBegin + 1);
        } else {
            //return new String(buffer, tokenBegin, bufsize - tokenBegin) + new String(buffer, 0, bufpos + 1);
        }
    }

    public function getEndColumn()
    {
        return $this->bufcolumn[$this->bufpos];
    }

    public function getEndLine()
    {
        return $this->bufline[$this->bufpos];
    }

    public function getBeginColumn()
    {
        return $this->bufcolumn[$this->tokenBegin];
    }

    public function getBeginLine()
    {
        return bufline[tokenBegin];
    }
}
