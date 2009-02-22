<?php
require_once 'test_config.php';
require_once 'simpletest/autorun.php';
require_once 'Director.php';

class DirectorTest extends UnitTestCase {

	function setUp()
	{
		$this->director = new Director;

		$this->director->add_patterns(array(
			'' => 'Default.index',
			'a/path/to/((something|else):bar)' => 'Foo.(bar)',
		));
	}

	function testDirectorCanParsePatterns()
	{
		$result = Director::parse_pattern('some/(patterns+:foo)');
		$expected = array(
			'!^some/(?P<foo>patterns+)$!ui',
			'some/(foo)'
		);

		$this->assertIdentical($result, $expected);
	}
}
?>
