<?php
//require_once('simpletest/autorun.php');
require_once(dirname(__FILE__)."/../unlink_directory.php");

class TestOfFileDelete extends PHPUnit_Framework_TestCase { //UnitTestCase {
	public $unlink_directory;
	public function setUp() {
		$this->unlink_directory = new unlink_directory();
	}
	function testremove_directory( $directory = 'tmp_dir' ) {
		$this->unlink_directory->remove_directory( $directory );
		$this->assertFalse(file_exists('tmp_dir'));
	}
	public function tearDown() {
		unset( $this->unlink_directory );
	}
}
?>
