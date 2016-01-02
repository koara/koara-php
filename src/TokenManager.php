<?php

namespace Koara;

use Exception;

class TokenManager
{
    const EOF = 0;
    const ASTERISK = 1;
    const BACKSLASH = 2;
    const BACKTICK = 3;
    const CHAR_SEQUENCE = 4;
    const COLON = 5;
    const DASH = 6;
    const DIGITS = 7;
    const DOT = 8;
    const EOL = 9;
    const EQ = 10;
    const ESCAPED_CHAR = 11;
    const GT = 12;
    const IMAGE_LABEL = 13;
    const LBRACK = 14;
    const LPAREN = 15;
    const LT = 16;
    const RBRACK = 17;
    const RPAREN = 18;
    const SPACE = 19;
    const TAB = 20;
    const UNDERSCORE = 21;
    const DEFAULT_KIND = 0;

    private $cs;
    private $jjrounds = array(8);
    private $jjstateSet = array(16);
    private $curChar;
    private $jjbitVec0 = array(0xfffffffffffffffe, 0xffffffffffffffff, 0xffffffffffffffff, 0xffffffffffffffff);
    private $jjbitVec2 = array(0x0, 0x0, 0xffffffffffffffff, 0xffffffffffffffff);
    private $jjnextStates = array('2', '3', '5');
    private $jjnewStateCnt;
    private $round;
    private $matchedPos;
    private $matchedKind;

    public function __construct(CharStream $stream)
    {
        $this->cs = $stream;
    }

    public function getNextToken()
    {
         try {
            $curPos = 0;
            while (true) {
                try {
                    $this->curChar = $this->cs->beginToken();
                } catch (Exception $e) {
                	$this->matchedKind = 0;
                    $this->matchedPos = -1;
                    return $this->fillToken();
                }
                $this->matchedKind = 0x7fffffff;
                $this->matchedPos = 0;
                $curPos = $this->moveStringLiteralDfa0_0();
                if ($this->matchedKind != 0x7fffffff) {
                    if ($this->matchedPos + 1 < $curPos) {
                        $this->cs->backup($curPos - $this->matchedPos - 1);
                    }
                    return $this->fillToken();
                }
            }
        } catch (Exception $e) {
        	echo $e;
            return;
        }
    }

    private function fillToken()
    {
        return new Token($this->matchedKind, $this->cs->getBeginLine(), $this->cs->getBeginColumn(),
                $this->cs->getEndLine(), $this->cs->getEndColumn(), $this->cs->getImage());
    }

    private function moveStringLiteralDfa0_0()
    {
        switch (ord($this->curChar)) {
        case 9:  return $this->startNfaWithStates(0, self::TAB, 8);
        case 32: return $this->startNfaWithStates(0, self::SPACE, 8);
        case 40: return $this->stopAtPos(0, self::LPAREN);
        case 41: return $this->stopAtPos(0, self::RPAREN);
        case 42: return $this->stopAtPos(0, self::ASTERISK);
        case 45: return $this->stopAtPos(0, self::DASH);
        case 46: return $this->stopAtPos(0, self::DOT);
        case 58: return $this->stopAtPos(0, self::COLON);
        case 60: return $this->stopAtPos(0, self::LT);
        case 61: return $this->stopAtPos(0, self::EQ);
        case 62: return $this->stopAtPos(0, self::GT);
        case 73: return $this->moveStringLiteralDfa1_0(0x2000);
        case 91: return $this->stopAtPos(0, self::LBRACK);
        case 92: return $this->startNfaWithStates(0, self::BACKSLASH, 7);
        case 93: return $this->stopAtPos(0, self::RBRACK);
        case 95: return $this->stopAtPos(0, self::UNDERSCORE);
        case 96: return $this->stopAtPos(0, self::BACKTICK);
        case 105: return $this->moveStringLiteralDfa1_0(0x2000);
        default: return $this->moveNfa(6, 0);
        }
    }

    private function startNfaWithStates($pos, $kind, $state)
    {
        $this->matchedKind = $kind;
        $this->matchedPos = $pos;
        try {
            $this->curChar = $this->cs->readChar();
        } catch (Exception $e) {
            return pos + 1;
        }

        return $this->moveNfa($state, $pos + 1);
    }

    private function stopAtPos($pos, $kind)
    {
        $this->matchedKind = $kind;
        $this->matchedPos = $pos;
        return $pos + 1;
    }

    private function moveStringLiteralDfa1_0($active)
    {
        $this->curChar = $this->cs->readChar();
        if (ord($this->curChar) == 77 || ord($this->curChar) == 109) {
            return $this->moveStringLiteralDfa2_0($active, 0x2000);
        }

        return $this->startNfa(0, $active);
    }

    private function moveStringLiteralDfa2_0(long $old, long $active)
    {
        $this->curChar = $this->cs.readChar();
        if (ord($this->curChar == 65) || ord($this->curChar) == 97) {
            return moveStringLiteralDfa3_0($active, 0x2000);
        }

        return startNfa(1, $active);
    }

    private function moveStringLiteralDfa3_0($old, $active)
    {
        $this->curChar = $this->cs->readChar();
        if (ord($this->curChar) == 71 || ord($this->curChar) == 103) {
            return $this->moveStringLiteralDfa4_0(active, 0x2000);
        }

        return $this->startNfa(2, $active);
    }

    private function moveStringLiteralDfa4_0($old, $active)
    {
        $this->curChar = $this->cs->readChar();
        if (ord(curChar == 69) || (curChar == 101)) {
            return $this->moveStringLiteralDfa5_0($active, 0x2000);
        }

        return $this->startNfa(3, $active);
    }

    private function moveStringLiteralDfa5_0($old, $active)
    {
        $this->curChar = $this->cs->readChar();
        if (ord($this->curChar) == 58 && (($active & 0x2000) != 0)) {
            return $this->stopAtPos(5, 13);
        }

        return startNfa(4, $active);
    }

    private function startNfa($pos, $active)
    {
        return $this->moveNfa($this->stopStringLiteralDfa($pos, $active), $pos + 1);
    }

    private function moveNfa($startState, $curPos)
    {
        $startsAt = 0;
        $this->jjnewStateCnt = 8;
        $i = 1;
        $this->jjstateSet[0] = $startState;
        $kind = 0x7fffffff;
        while (true) {
            if (++$this->round == 0x7fffffff) {
                $this->round = 0x80000001;
            }
            if (ord($this->curChar) < 64) {
                $l = 1 << $this->curChar;
                do {
                    switch ($this->jjstateSet[--$i]) {
                    case 6:
                        if ((0x880098feffffd9ff & $l) != 0) {
                            if ($kind > 4) {
                                $kind = 4;
                            }
                            $this->checkNAdd(0);
                        } elseif ((0x3ff000000000000 & $l) != 0) {
                            if ($kind > 7) {
                                $kind = 7;
                            }
                            $this->checkNAdd(1);
                        } elseif ((0x2400 & $l) != 0) {
                            if ($kind > 9) {
                                $kind = 9;
                            }
                        } elseif ((0x100000200 & $l) != 0) {
                            $this->checkNAddStates(0, 2);
                        }
                        if (ord($this->curChar) == 13) {
                            $this->jjstateSet[$this->jjnewStateCnt++] = 4;
                        }
                        break;
                    case 8:
                        if ((0x2400 & $l) != 0) {
                            if ($kind > 9) {
                                $kind = 9;
                            }
                        } elseif ((0x100000200 & $l) != 0) {
                            $this->checkNAddStates(0, 2);
                        }
                        if (ord($this->curChar) == 13) {
                            $this->jjstateSet[$this->jjnewStateCnt++] = 4;
                        }
                        break;
                    case 0:
                        if ((0x880098feffffd9ff & $l) != 0) {
                            $kind = 4;
                            $this->checkNAdd(0);
                        }
                        break;
                    case 1:
                        if ((0x3ff000000000000 & $l) != 0) {
                            if ($kind > 7) {
                                $kind = 7;
                            }
                            $this->checkNAdd(1);
                        }
                        break;
                    case 2:
                        if ((0x100000200 & $l) != 0) {
                            $this->checkNAddStates(0, 2);
                        }
                        break;
                    case 3:
                        if ((0x2400 & $l) != 0 && $kind > 9) {
                            $kind = 9;
                        }
                        break;
                    case 4:
                        if (ord($this->curChar) == 10 && $kind > 9) {
                            $kind = 9;
                        }
                        break;
                    case 5:
                        if (ord($this->curChar == 13)) {
                            $this->jjstateSet[$jjnewStateCnt++] = 4;
                        }
                        break;
                    case 7:
                        if ((0x77ff670000000000 & $l) != 0 && $kind > 11) {
                            $kind = 11;
                        }
                        break;
                    }
                } while ($i != $startsAt);
            } elseif (ord($this->curChar) < 128) {
                $l = 1 << (ord($this->curChar) & 077);
                
                do {
                    switch ($this->jjstateSet[--$i]) {
                    case 6:
                        if ($l != 0) {
                            if ($kind > 4) {
                                $kind = 4;
                            }
                            $this->checkNAdd(0);
                        } elseif (ord($this->curChar) == 92) {
                            $this->jjstateSet[$this->jjnewStateCnt++] = 7;
                        }
                        break;
                    case 0:
                        if ((0xfffffffe47ffffff & $l) != 0) {
                            $kind = 4;
                            $this->checkNAdd(0);
                        }
                        break;
                    case 7:
                        if ((0x1b8000000 & $l) != 0 && $kind > 11) {
                            $kind = 11;
                        }
                        break;
                    }
                } while ($i != $startsAt);
            } else {
                $hiByte = ($this->curChar >> 8);
                $i1 = $hiByte >> 6;
                $l1 = 1 << ($hiByte & 077);
                $i2 = ($this->curChar & 0xff) >> 6;
                $l2 = 1 << ($this->curChar & 077);
                do {
                    switch ($this->jjstateSet[--$i]) {
                    case 6:
                    case 0:
                        if ($this->canMove($hiByte, $i1, $i2, $l1, $l2)) {
                            if ($kind > 4) {
                                $kind = 4;
                            }
                            $this->checkNAdd(0);
                        }
                        break;
                    }
                } while ($i != $startsAt);
            }
            if ($kind != 0x7fffffff) {
                $this->matchedKind = $kind;
                $this->matchedPos = $curPos;
                $kind = 0x7fffffff;
            }
            ++$curPos;
            if (($i = $this->jjnewStateCnt) == ($startsAt = 8 - ($this->jjnewStateCnt = $startsAt))) {
                return $curPos;
            }
            try {
                $this->curChar = $this->cs->readChar();
            } catch (Exception $e) {
                return $curPos;
            }
        }
    }

    private function canMove($hiByte, $i1, $i2, $l1, $l2)
    {
        if ($hiByte == 0) {
            return ($this->jjbitVec2[$i2] & $l2) != 0;
        }

        return ($this->jjbitVec0[$i1] & $l1) != 0;
    }

    private function checkNAddStates($start, $end)
    {
        do {
            $this->checkNAdd($this->jjnextStates[$start]);
        } while ($start++ != $end);
    }

    private function checkNAdd($state)
    {
        if ($this->jjrounds[$state] != $this->round) {
            $this->jjstateSet[$this->jjnewStateCnt++] = $state;
            $this->jjrounds[$state] = $this->round;
        }
    }

    private function stopStringLiteralDfa($pos, $active)
    {
        if ($pos == 0) {
            if (($active & 0x2000) != 0) {
                $this->matchedKind = 4;

                return 0;
            } elseif (($active & 0x180000) != 0) {
                return 8;
            } elseif (($active & 0x4) != 0) {
                return 7;
            }
        } elseif ($pos == 1 && ($active & 0x2000) != 0) {
            $this->matchedKind = 4;
            $this->matchedPos = 1;

            return 0;
        } elseif ($pos == 2 && ($active & 0x2000) != 0) {
            $this->matchedKind = 4;
            $this->matchedPos = 2;

            return 0;
        } elseif ($pos == 3 && ($active & 0x2000) != 0) {
            $this->matchedKind = 4;
            $this->matchedPos = 3;

            return 0;
        } elseif ($pos == 4 && ($active & 0x2000) != 0) {
            $this->matchedKind = 4;
            $this->matchedPos = 4;

            return 0;
        }

        return -1;
    }
}
