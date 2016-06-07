<?php
namespace Koara;

use Koara\Io\StringReader;

class TokenManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testEof()
    {
        $tm = new TokenManager(new CharStream(new StringReader("")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::EOF, $token->kind);
    }

    public function testAsterisk()
    {
        $tm = new TokenManager(new CharStream(new StringReader("*")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::ASTERISK, $token->kind);
        $this->assertEquals("*", $token->image);
    }

    public function testBackslash()
    {
        $tm = new TokenManager(new CharStream(new StringReader("\\")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::BACKSLASH, $token->kind);
        $this->assertEquals("\\", $token->image);
    }

    public function testBacktick()
    {
        $tm = new TokenManager(new CharStream(new StringReader("`")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::BACKTICK, $token->kind);
        $this->assertEquals("`", $token->image);
    }

    public function testCharSequenceLowerCase()
    {
        $tm = new TokenManager(new CharStream(new StringReader("m")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::CHAR_SEQUENCE, $token->kind);
        $this->assertEquals("m", $token->image);
    }

    public function testCharSequenceUpperCase()
    {
        $tm = new TokenManager(new CharStream(new StringReader("C")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::CHAR_SEQUENCE, $token->kind);
        $this->assertEquals("C", $token->image);
    }

    public function testColon()
    {
        $tm = new TokenManager(new CharStream(new StringReader(":")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::COLON, $token->kind);
        $this->assertEquals(":", $token->image);
    }

    public function testDash()
    {
        $tm = new TokenManager(new CharStream(new StringReader("-")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::DASH, $token->kind);
        $this->assertEquals("-", $token->image);
    }

    public function testDigits()
    {
        $tm = new TokenManager(new CharStream(new StringReader("4")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::DIGITS, $token->kind);
        $this->assertEquals("4", $token->image);
    }

    public function testDot()
    {
        $tm = new TokenManager(new CharStream(new StringReader(".")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::DOT, $token->kind);
        $this->assertEquals(".", $token->image);
    }

    public function testEol()
    {
        $tm = new TokenManager(new CharStream(new StringReader("\n")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::EOL, $token->kind);
        $this->assertEquals("\n", $token->image);
    }

    public function testEq()
    {
        $tm = new TokenManager(new CharStream(new StringReader("=")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::EQ, $token->kind);
        $this->assertEquals("=", $token->image);
    }

    public function testEscapedChar()
    {
        $tm = new TokenManager(new CharStream(new StringReader("\\*")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::ESCAPED_CHAR, $token->kind);
        $this->assertEquals("\\*", $token->image);
    }

    public function testGt()
    {
        $tm = new TokenManager(new CharStream(new StringReader(">")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::GT, $token->kind);
        $this->assertEquals(">", $token->image);
    }

    public function testImageLabel()
    {
        $tm = new TokenManager(new CharStream(new StringReader("image:")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::IMAGE_LABEL, $token->kind);
        $this->assertEquals("image:", $token->image);
    }

    public function testLbrack()
    {
        $tm = new TokenManager(new CharStream(new StringReader("[")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::LBRACK, $token->kind);
        $this->assertEquals("[", $token->image);
    }

    public function testLparen()
    {
        $tm = new TokenManager(new CharStream(new StringReader("(")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::LPAREN, $token->kind);
        $this->assertEquals("(", $token->image);
    }

    public function testLt()
    {
        $tm = new TokenManager(new CharStream(new StringReader("<")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::LT, $token->kind);
        $this->assertEquals("<", $token->image);
    }

    public function testRbrack()
    {
        $tm = new TokenManager(new CharStream(new StringReader("]")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::RBRACK, $token->kind);
        $this->assertEquals("]", $token->image);
    }

    public function testRparen()
    {
        $tm = new TokenManager(new CharStream(new StringReader(")")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::RPAREN, $token->kind);
        $this->assertEquals(")", $token->image);
    }

    public function testSpace()
    {
        $tm = new TokenManager(new CharStream(new StringReader(" ")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::SPACE, $token->kind);
        $this->assertEquals(" ", $token->image);
    }

    public function testTab()
    {
        $tm = new TokenManager(new CharStream(new StringReader("\t")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::TAB, $token->kind);
        $this->assertEquals("\t", $token->image);
    }

    public function testUnderscore()
    {
        $tm = new TokenManager(new CharStream(new StringReader("_")));
        $token = $tm->getNextToken();
        $this->assertEquals(TokenManager::UNDERSCORE, $token->kind);
        $this->assertEquals("_", $token->image);
    }

    public function testSpaceAfterCharSequence()
    {
        $tm = new TokenManager(new CharStream(new StringReader("a ")));
        $this->assertEquals("a", $tm->getNextToken()->image);
        $this->assertEquals(" ", $tm->getNextToken()->image);
    }


}