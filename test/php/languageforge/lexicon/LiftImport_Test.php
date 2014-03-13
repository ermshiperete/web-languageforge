<?php

use models\languageforge\lexicon\LexiconProjectModel;
use models\languageforge\lexicon\LexEntryListModel;
use models\languageforge\lexicon\LiftImport;
use models\languageforge\lexicon\LiftMergeRule;
use models\languageforge\lexicon\LexEntryModel;

require_once(dirname(__FILE__) . '/../../TestConfig.php');
require_once(SimpleTestPath . 'autorun.php');
require_once(TestPath . 'common/MongoTestEnvironment.php');
require_once(dirname(__FILE__) . '/LexTestData.php');

class TestLiftImport extends UnitTestCase {

	function testLiftImportMerge_XmlOldVer_Exception() {
		$e = new LexiconMongoTestEnvironment();
		$e->clean();
		
		$project = $e->createProject(SF_TESTPROJECT);
		$liftXml = LexTestData::liftOneEntryV0_12;
		
		$e->inhibitErrorDisplay();
		$this->expectError(new PatternExpectation("/Element lift failed to validate content/i"));
		LiftImport::merge($liftXml, $project);
		$e->restoreErrorDisplay();
	}

	function testLiftImportMerge_XmlInvalidAttribute_Exception() {
		$e = new LexiconMongoTestEnvironment();
		$e->clean();
		
		$project = $e->createProject(SF_TESTPROJECT);
		$liftXml = LexTestData::liftInvalidAttribute;
		
		$e->inhibitErrorDisplay();
		$this->expectError(new PatternExpectation("/Expecting an element pronunciation, got nothing/i"));
		$this->expectError(new PatternExpectation("/Invalid attribute xXxXx for element entry/i"));
		$this->expectError(new PatternExpectation("/Element lift has extra content: entry/i"));
		LiftImport::merge($liftXml, $project);
		$e->restoreErrorDisplay();
	}

	function testLiftImportMerge_XmlValidAndNoExistingData_NoExceptionAndMergeOk() {
		$e = new LexiconMongoTestEnvironment();
		$e->clean();
		
		$project = $e->createProject(SF_TESTPROJECT);
		$liftXml = LexTestData::liftTwoEntriesV0_13;
		$mergeRule =  LiftMergeRule::IMPORT_WINS;
		$skipSameModTime = false;
		
		LiftImport::merge($liftXml, $project, $mergeRule, $skipSameModTime);
		
		$entryList = new LexEntryListModel($project);
		$entryList->read();
		$entries = $entryList->entries;
		$this->assertEqual($entryList->count, 2, "Should be 2 entries");
		$this->assertEqual($entries[0]['guid'], "dd15cbc4-9085-4d66-af3d-8428f078a7da", "First entry should have given guid");
		$this->assertEqual($entries[0]['lexeme']['th-fonipa']['value'], "chùuchìi mǔu krɔ̂ɔp", "First entry should have given IPA form");
		$this->assertEqual($entries[0]['lexeme']['th']['value'], "ฉู่ฉี่หมูกรอบ", "First entry should have given Thai form");
		$this->assertEqual($entries[1]['guid'], "05473cb0-4165-4923-8d81-02f8b8ed3f26", "Second entry should have given guid");
		$this->assertEqual($entries[1]['lexeme']['th-fonipa']['value'], "khâaw kài thɔ̀ɔt", "Second entry should have given IPA form");
		$this->assertEqual($entries[1]['lexeme']['th']['value'], "ข้าวไก่ทอด", "Second entry should have given Thai form");
		
		$entry = new LexEntryModel($project, $entries[0]['id']);
		echo "<pre>";
		echo "entries[0] as entry->senses[0]->examples[0]: ";
		echo var_dump($entry->senses[0]->examples[0]);
		echo "entries[0]: " . var_export($entries[0], true);
// 		echo "entries[1]: " . var_export($entries[1], true);
// 		echo "entries: " . var_export($entries, true);
		echo "</pre>";
	}

	function testLiftImportMerge_ExistingDataAndImportWins_MergeOk() {
		$e = new LexiconMongoTestEnvironment();
		
		$project = $e->createProject(SF_TESTPROJECT);
		$liftXml = LexTestData::liftTwoEntriesV0_13;
		LiftImport::merge($liftXml, $project);	// create existing data
		$liftXml = LexTestData::liftTwoEntriesCorrectedV0_13;
		$mergeRule =  LiftMergeRule::IMPORT_WINS;
		$skipSameModTime = false;
		
		LiftImport::merge($liftXml, $project, $mergeRule, $skipSameModTime);
		
		$entryList = new LexEntryListModel($project);
		$entryList->read();
		$entries = $entryList->entries;
		$this->assertEqual($entryList->count, 2, "Should be 2 entries");
		$this->assertEqual($entries[0]['guid'], "dd15cbc4-9085-4d66-af3d-8428f078a7da", "First entry should have given guid");
		$this->assertEqual($entries[0]['lexeme']['th-fonipa']['value'], "chùuchìi mǔu krɔ̀ɔp", "First entry should have corrected IPA form");
		$this->assertEqual($entries[1]['guid'], "05473cb0-4165-4923-8d81-02f8b8ed3f26", "Second entry should have given guid");
		$this->assertEqual($entries[1]['lexeme']['th-fonipa']['value'], "khâaw kài thɔ̂ɔt", "Second entry should have corrected IPA form");
	}

	function testLiftImportMerge_ExistingDataAndImportWinsAndSkip_NoMerge() {
		$e = new LexiconMongoTestEnvironment();
		
		$project = $e->createProject(SF_TESTPROJECT);
		$liftXml = LexTestData::liftTwoEntriesV0_13;
		LiftImport::merge($liftXml, $project);	// create existing data
		$liftXml = LexTestData::liftTwoEntriesCorrectedV0_13;
		$mergeRule =  LiftMergeRule::IMPORT_WINS;
		$skipSameModTime = true;
		
		LiftImport::merge($liftXml, $project, $mergeRule, $skipSameModTime);
		
		$entryList = new LexEntryListModel($project);
		$entryList->read();
		$entries = $entryList->entries;
		$this->assertEqual($entryList->count, 2, "Should be 2 entries");
		$this->assertEqual($entries[0]['guid'], "dd15cbc4-9085-4d66-af3d-8428f078a7da", "First entry should have given guid");
		$this->assertEqual($entries[0]['lexeme']['th-fonipa']['value'], "chùuchìi mǔu krɔ̂ɔp", "First entry should have uncorrected IPA form");
		$this->assertEqual($entries[1]['guid'], "05473cb0-4165-4923-8d81-02f8b8ed3f26", "Second entry should have given guid");
		$this->assertEqual($entries[1]['lexeme']['th-fonipa']['value'], "khâaw kài thɔ̀ɔt", "Second entry should have uncorrected IPA form");
	}

	function testLiftImportMerge_ExistingDataAndImportWinsAndSkip_MergeOk() {
		$e = new LexiconMongoTestEnvironment();
		
		$project = $e->createProject(SF_TESTPROJECT);
		$liftXml = LexTestData::liftTwoEntriesV0_13;
		LiftImport::merge($liftXml, $project);	// create existing data
		$liftXml = LexTestData::liftTwoEntriesModifiedV0_13;
		$mergeRule =  LiftMergeRule::IMPORT_WINS;
		$skipSameModTime = true;
		
		LiftImport::merge($liftXml, $project, $mergeRule, $skipSameModTime);
		
		$entryList = new LexEntryListModel($project);
		$entryList->read();
		$entries = $entryList->entries;
		$this->assertEqual($entryList->count, 2, "Should be 2 entries");
		$this->assertEqual($entries[0]['guid'], "dd15cbc4-9085-4d66-af3d-8428f078a7da", "First entry should have given guid");
		$this->assertEqual($entries[0]['lexeme']['th-fonipa']['value'], "chùuchìi mǔu krɔ̀ɔp", "First entry should have corrected IPA form");
		$this->assertEqual($entries[1]['guid'], "05473cb0-4165-4923-8d81-02f8b8ed3f26", "Second entry should have given guid");
		$this->assertEqual($entries[1]['lexeme']['th-fonipa']['value'], "khâaw kài thɔ̂ɔt", "Second entry should have corrected IPA form");
	}

	function testLiftImportMerge_ExistingDataAndImportLoses_NoMerge() {
		$e = new LexiconMongoTestEnvironment();
		
		$project = $e->createProject(SF_TESTPROJECT);
		$liftXml = LexTestData::liftTwoEntriesV0_13;
		LiftImport::merge($liftXml, $project);	// create existing data
		$liftXml = LexTestData::liftTwoEntriesCorrectedV0_13;
		$mergeRule =  LiftMergeRule::IMPORT_LOSES;
		$skipSameModTime = false;
		
		LiftImport::merge($liftXml, $project, $mergeRule, $skipSameModTime);
		
		$entryList = new LexEntryListModel($project);
		$entryList->read();
		$entries = $entryList->entries;
		$this->assertEqual($entryList->count, 2, "Should be 2 entries");
		$this->assertEqual($entries[0]['guid'], "dd15cbc4-9085-4d66-af3d-8428f078a7da", "First entry should have given guid");
		$this->assertEqual($entries[0]['lexeme']['th-fonipa']['value'], "chùuchìi mǔu krɔ̂ɔp", "First entry should have uncorrected IPA form");
		$this->assertEqual($entries[1]['guid'], "05473cb0-4165-4923-8d81-02f8b8ed3f26", "Second entry should have given guid");
		$this->assertEqual($entries[1]['lexeme']['th-fonipa']['value'], "khâaw kài thɔ̀ɔt", "Second entry should have uncorrected IPA form");
	}

	function testLiftImportMerge_ExistingDataAndCreateDuplicates_DuplicatesCreated() {
		$e = new LexiconMongoTestEnvironment();
		
		$project = $e->createProject(SF_TESTPROJECT);
		$liftXml = LexTestData::liftTwoEntriesV0_13;
		LiftImport::merge($liftXml, $project);	// create existing data
		$liftXml = LexTestData::liftTwoEntriesCorrectedV0_13;
		$mergeRule =  LiftMergeRule::CREATE_DUPLICATES;
		$skipSameModTime = false;
		
		LiftImport::merge($liftXml, $project, $mergeRule, $skipSameModTime);
		
		$entryList = new LexEntryListModel($project);
		$entryList->read();
		$entries = $entryList->entries;
		$this->assertEqual($entryList->count, 4, "Should be 4 entries");
		$this->assertEqual($entries[0]['guid'], "dd15cbc4-9085-4d66-af3d-8428f078a7da", "First entry should have given guid");
		$this->assertEqual($entries[0]['lexeme']['th-fonipa']['value'], "chùuchìi mǔu krɔ̂ɔp", "First entry should have uncorrected IPA form");
		$this->assertEqual($entries[1]['guid'], "05473cb0-4165-4923-8d81-02f8b8ed3f26", "Second entry should have given guid");
		$this->assertEqual($entries[1]['lexeme']['th-fonipa']['value'], "khâaw kài thɔ̀ɔt", "Second entry should have uncorrected IPA form");
		$this->assertEqual($entries[2]['guid'], "dd15cbc4-9085-4d66-af3d-8428f078a7da", "First entry should have given guid");
		$this->assertEqual($entries[2]['lexeme']['th-fonipa']['value'], "chùuchìi mǔu krɔ̀ɔp", "First entry should have corrected IPA form");
		$this->assertEqual($entries[3]['guid'], "05473cb0-4165-4923-8d81-02f8b8ed3f26", "Second entry should have given guid");
		$this->assertEqual($entries[3]['lexeme']['th-fonipa']['value'], "khâaw kài thɔ̂ɔt", "Second entry should have corrected IPA form");
	}

	function testLiftImportMerge_ExistingDataAndCreateDuplicatesAndSkip_NoMerge() {
		$e = new LexiconMongoTestEnvironment();
		
		$project = $e->createProject(SF_TESTPROJECT);
		$liftXml = LexTestData::liftTwoEntriesV0_13;
		LiftImport::merge($liftXml, $project);	// create existing data
		$liftXml = LexTestData::liftTwoEntriesCorrectedV0_13;
		$mergeRule =  LiftMergeRule::CREATE_DUPLICATES;
		$skipSameModTime = true;
		
		LiftImport::merge($liftXml, $project, $mergeRule, $skipSameModTime);
		
		$entryList = new LexEntryListModel($project);
		$entryList->read();
		$entries = $entryList->entries;
		$this->assertEqual($entryList->count, 2, "Should be 2 entries");
		$this->assertEqual($entries[0]['guid'], "dd15cbc4-9085-4d66-af3d-8428f078a7da", "First entry should have given guid");
		$this->assertEqual($entries[0]['lexeme']['th-fonipa']['value'], "chùuchìi mǔu krɔ̂ɔp", "First entry should have uncorrected IPA form");
		$this->assertEqual($entries[1]['guid'], "05473cb0-4165-4923-8d81-02f8b8ed3f26", "Second entry should have given guid");
		$this->assertEqual($entries[1]['lexeme']['th-fonipa']['value'], "khâaw kài thɔ̀ɔt", "Second entry should have uncorrected IPA form");
	}

}

?>