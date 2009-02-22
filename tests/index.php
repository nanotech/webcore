<?php
require_once 'test_config.php';
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';

$test = &new TestSuite('All WebCore Tests');
$test->addTestFile('CoreTest.php');
$test->addTestFile('DirectorTest.php');
$test->run(new HtmlReporter());
?>
