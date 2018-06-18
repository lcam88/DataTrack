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

	public $fixtures = array('plugin.data_track.deleted_user');

	public function setUp() {
		parent::setUp();
		$this->DeletedUser = ClassRegistry::init('User');
		$this->DeletedUser->useTable = 'deleted_users';
		$this->DeletedUser->Behaviors->load('DataTrack.DataTrack');
	}

	public function tearDown() {
		unset($this->DeletedUser);
		parent::tearDown();
	}

	public function testFindDeleted() {
		$records = $this->DeletedUser->find('all', array(
			'conditions' => array('flag ==' => 0)
		));
		$this->assertEqual(1, count($records));
	}
	public function testFindNonDeleted() {
		$records = $this->DeletedUser->find('all', array(
			'conditions' => array('flag ==' => 1)
		));
		$this->assertEqual(2, count($records));
	}
}
