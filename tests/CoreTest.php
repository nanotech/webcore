<?php
require_once 'test_config.php';
require_once 'simpletest/autorun.php';
require_once 'Core.php';

class CoreTest extends UnitTestCase {

	function testCoreStartsWithNoResources() {
        $this->assertIdentical(Core::$resources, array());
    }

    function testCoreFindsResources() {
		$resource = 'Foo';
		$path = '/path/to/Foo.php';

		Core::$resources = array(
			'php' => array(
				$resource => $path
			)
		);

        $this->assertIdentical(Core::find_resource($resource), $path);
    }

	function testCoreDoesntFindResourcesThatDontExist() {
		$name = 'DoesntExist';
		$this->expectException(new MissingResource($name));
		Core::find_resource($name);
	}

	function testResettingCoreMakesLikeNew()
	{
		Core::reset();
		$this->testCoreStartsWithNoResources();
	}

	function tearDown() {
		Core::reset();
	}
}
?>
