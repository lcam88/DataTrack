<?php
/**
 * All DataTrack plugin tests
 */
class AllDataTrackTest extends CakeTestCase {

/**
 * Suite define the tests for this plugin
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All DataTrack test');

		$path = CakePlugin::path('DataTrack') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
