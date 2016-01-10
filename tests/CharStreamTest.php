<?php
namespace Koara;

use Koara\Io\StringReader;

class StringReaderTest extends \PHPUnit_Framework_TestCase {

 	private $cs;
	
	protected function setUp() {
		$this->cs = new CharStream(new StringReader("abcd"));
	}
	
	public function testBeginToken() {
		$this->assertEquals('a', $this->cs->beginToken());
		$this->assertEquals(1, $this->cs->getBeginColumn());
		$this->assertEquals(1, $this->cs->getBeginLine());
		$this->assertEquals(1, $this->cs->getEndColumn());
		$this->assertEquals(1, $this->cs->getEndColumn());
	}
	
	public function testReadChar() {
		$this->assertEquals('a', $this->cs->readChar());
		$this->assertEquals('b', $this->cs->readChar());
		$this->assertEquals('c', $this->cs->readChar());
		$this->assertEquals('d', $this->cs->readChar());
	}
	
	/**
	 * @expectedException Exception
	 */
	public function testReadCharTillEof() {
		$this->cs->readChar();
		$this->cs->readChar();
		$this->cs->readChar();
		$this->cs->readChar();
		$this->cs->readChar();
	}
	
	public function testGetImage() {
		$this->cs->readChar();
		$this->cs->readChar();
		$this->assertEquals("ab", $this->cs->getImage());
	}
	
}