<?php
namespace Koara;

use Koara\Io\FileReader;

class FileReaderTest extends \PHPUnit_Framework_TestCase {

 	private $buffer;
	
	protected function setUp() {
		$this->buffer = [];
	}
	
  	public function testRead() {
 		$reader = new FileReader("tests/resources/filereader.kd");
 		$this->assertTrue($reader->read($this->buffer, 0, 4));
 		$this->assertEquals("a", $this->buffer[0]);
		$this->assertEquals("b", $this->buffer[1]);
		$this->assertEquals("c", $this->buffer[2]);
		$this->assertEquals("d", $this->buffer[3]);
		$this->assertEquals(4, count($this->buffer));
 		$this->assertFalse($reader->read($this->buffer, 0, 4));
  	}
 	
 	public function testReadPartOfString() {
 		$reader = new FileReader("tests/resources/filereader.kd");
 		$this->assertTrue($reader->read($this->buffer, 0, 2));
 		$this->assertEquals("a", $this->buffer[0]);
 		$this->assertEquals("b", $this->buffer[1]);
 		$this->assertEquals(2, count($this->buffer));
 	}
 	
 	public function testReadWithOffsetPartOfString() {
 		$reader = new FileReader("tests/resources/filereader.kd");
 		$this->assertTrue($reader->read($this->buffer, 2, 4));
 		$this->assertFalse(array_key_exists(0, $this->buffer));
 		$this->assertFalse(array_key_exists(1, $this->buffer));
 		$this->assertEquals("a", $this->buffer[2]);
 		$this->assertEquals("b", $this->buffer[3]);
 	 }
 	
 	 public function testReadWithOffsetTooLargePartOfString() {
 	 	$reader = new FileReader("tests/resources/filereader.kd");
 	 	$this->assertTrue($reader->read($this->buffer, 6, 4));
 	 	$this->assertFalse(array_key_exists(0, $this->buffer));
 	 	$this->assertFalse(array_key_exists(1, $this->buffer));
 	 	$this->assertFalse(array_key_exists(2, $this->buffer));
 	 	$this->assertFalse(array_key_exists(3, $this->buffer));
 	 }
 	
 	public function testReadUntilEof() {
 		$reader = new FileReader("tests/resources/filereader.kd");
 		$this->assertTrue($reader->read($this->buffer, 0, 2));
 		$this->assertEquals("a", $this->buffer[0]);
 		$this->assertEquals("b", $this->buffer[1]);
 	
 		$this->assertTrue($reader->read($this->buffer, 0, 3));
 		$this->assertEquals("c", $this->buffer[0]);
 		$this->assertEquals("d", $this->buffer[1]);
 	
 		$this->assertFalse($reader->read($this->buffer, 0, 2));
 	}
 	
 	 public function testReadWithUnicode() {
 	 	$reader = new FileReader("tests/resources/filereader-unicode.kd");
 	 	$this->assertTrue($reader->read($this->buffer, 0, 4));
 	 	$this->assertEquals("ð", $this->buffer[0]);
 	 	$this->assertEquals("i", $this->buffer[1]);
 	 	$this->assertEquals("n", $this->buffer[2]);
 	 	$this->assertEquals("æ", $this->buffer[3]);
 	 	$this->assertEquals(4, count($this->buffer));
 	 }
 	
 	 public function testReadWithUnicodePartOfString() {
 	 	$reader = new FileReader("tests/resources/filereader-unicode.kd");
 	 	$this->assertTrue($reader->read($this->buffer, 0, 2));
 	 	$this->assertEquals("ð", $this->buffer[0]);
 	 	$this->assertEquals("i", $this->buffer[1]);
 	 	$this->assertEquals(2, count($this->buffer));
 	 }
 	
 	 public function testReadWithUnicodeAndOffsetPartOfString() {
 	 	$reader = new FileReader("tests/resources/filereader-unicode.kd");
 	 	$this->assertTrue($reader->read($this->buffer, 2, 4));
 	 	$this->assertFalse(array_key_exists(0, $this->buffer));
 	 	$this->assertFalse(array_key_exists(1, $this->buffer));
 	 	$this->assertEquals("ð", $this->buffer[2]);
 	 	$this->assertEquals("i", $this->buffer[3]);
 	 }
 	
 	 public function testReadWithUnicodeAndOffsetTooLargePartOfString() {
 	 	$reader = new FileReader("tests/resources/filereader-unicode.kd");
 	 	$this->assertTrue($reader->read($this->buffer, 6, 4));
 	 	$this->assertFalse(array_key_exists(0, $this->buffer));
 	 	$this->assertFalse(array_key_exists(1, $this->buffer));
 	 	$this->assertFalse(array_key_exists(2, $this->buffer));
 	 	$this->assertFalse(array_key_exists(3, $this->buffer));
 	 }
 	
 	 public function testReadWithUnicodeUntilEof() {
 	 	$reader = new FileReader("tests/resources/filereader-unicode.kd");
 	 	$this->assertTrue($reader->read($this->buffer, 0, 3));
 	 	$this->assertEquals("ð", $this->buffer[0]);
 	 	$this->assertEquals("i", $this->buffer[1]);
 		
 	 	$this->assertTrue($reader->read($this->buffer, 0, 3));
 	 	$this->assertEquals("æ", $this->buffer[0]);
 		
 	 	$this->assertFalse($reader->read($this->buffer, 0, 2));
 	 }
	

}