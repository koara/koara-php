<?php
namespace Koara;

use Koara\Io\StringReader;

class CharStreamTest extends \PHPUnit_Framework_TestCase {

 	private $cs;
	
	public function testBeginToken() {
		$this->cs = new CharStream(new StringReader("abcd"));
		$this->assertEquals('a', $this->cs->beginToken());
		$this->assertEquals(1, $this->cs->getBeginColumn());
		$this->assertEquals(1, $this->cs->getBeginLine());
		$this->assertEquals(1, $this->cs->getEndColumn());
		$this->assertEquals(1, $this->cs->getEndColumn());
	}
	
	public function testReadChar() {
		$this->cs = new CharStream(new StringReader("abcd"));
		$this->assertEquals('a', $this->cs->readChar());
		$this->assertEquals('b', $this->cs->readChar());
		$this->assertEquals('c', $this->cs->readChar());
		$this->assertEquals('d', $this->cs->readChar());
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testReadCharTillEof() {
		$this->cs = new CharStream(new StringReader("abcd"));
		$this->cs->readChar();
		$this->cs->readChar();
		$this->cs->readChar();
		$this->cs->readChar();
		$this->cs->readChar();
	}
	
	public function testGetImage() {
		$this->cs = new CharStream(new StringReader("abcd"));
		$this->cs->readChar();
		$this->cs->readChar();
		$this->assertEquals("ab", $this->cs->getImage());
	}
	
	public function testBeginTokenWithUnicode() {
		$this->cs = new CharStream(new StringReader("ðinæ"));
		$this->assertEquals('ð', $this->cs->beginToken());
		$this->assertEquals(1, $this->cs->getBeginColumn());
		$this->assertEquals(1, $this->cs->getBeginLine());
		$this->assertEquals(1, $this->cs->getEndColumn());
		$this->assertEquals(1, $this->cs->getEndColumn());
	}
	
	public function testReadCharWithUnicode() {
		$this->cs = new CharStream(new StringReader("ðinæ"));
		$this->assertEquals('ð', $this->cs->readChar());
		$this->assertEquals('i', $this->cs->readChar());
		$this->assertEquals('n', $this->cs->readChar());
		$this->assertEquals('æ', $this->cs->readChar());
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testReadCharTillEofWithUnicode() {
		$this->cs = new CharStream(new StringReader("ðinæ"));
		$this->cs->readChar();
		$this->cs->readChar();
		$this->cs->readChar();
		$this->cs->readChar();
		$this->cs->readChar();
	}
	
	public function testGetImageWithUnicode() {
		$this->cs = new CharStream(new StringReader("ðinæ"));
		$this->cs->readChar();
		$this->cs->readChar();
		$this->assertEquals("ði", $this->cs->getImage());
	}
	
}