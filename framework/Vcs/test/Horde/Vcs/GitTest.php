<?php
/**
 * Prepare the test setup.
 */
require_once dirname(__FILE__) . '/TestBase.php';

/**
 * @author     Jan Schneider <jan@horde.org>
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @category   Horde
 * @package    Vcs
 * @subpackage UnitTests
 */

class Horde_Vcs_GitTest extends Horde_Vcs_TestBase
{
    public function setUp()
    {
        if (!self::$conf) {
            $this->markTestSkipped();
        }
        $this->vcs = Horde_Vcs::factory(
            'Git',
            array_merge(self::$conf,
                        array('sourceroot' => dirname(__FILE__) . '/repos/git')));
    }

    public function testFactory()
    {
        $this->assertInstanceOf('Horde_Vcs_Git', $this->vcs);

        /* Test features. */
        $this->assertTrue($this->vcs->hasFeature('branches'));
        $this->assertFalse($this->vcs->hasFeature('deleted'));
        $this->assertTrue($this->vcs->hasFeature('patchsets'));
        $this->assertTrue($this->vcs->hasFeature('snapshots'));
        $this->assertFalse($this->vcs->hasFeature('foo'));

        /* Test base object methods. */
        $this->assertTrue($this->vcs->isValidRevision('1e4c45df'));
        $this->assertTrue($this->vcs->isValidRevision('1234'));
        $this->assertTrue($this->vcs->isValidRevision('abcd'));
        $this->assertFalse($this->vcs->isValidRevision('ghijk'));
        $this->assertFalse($this->vcs->isValidRevision('1.1'));
    }

    public function testDirectory()
    {
        $dir = $this->vcs->getDirectory('');
        $this->assertInstanceOf('Horde_Vcs_Directory_Git', $dir);
        $this->assertEquals(array('dir1'), $dir->getDirectories());
        $files = $dir->getFiles();
        $this->assertInternalType('array', $files);
        $this->assertEquals(1, count($files));
        $this->assertInstanceOf('Horde_Vcs_File_Git', $files[0]);
        $this->assertEquals(1, count($dir->getFiles(true)));
        $this->assertEquals(array('branch1', 'master'), $dir->getBranches());

        $dir = $this->vcs->getDirectory('dir1');
        $this->assertInstanceOf('Horde_Vcs_Directory_Git', $dir);
        $this->assertEquals(array(), $dir->getDirectories());
        $files = $dir->getFiles();
        $this->assertInternalType('array', $files);
        $this->assertEquals(1, count($files));
        $this->assertInstanceOf('Horde_Vcs_File_Git', $files[0]);
        $this->assertEquals(1, count($dir->getFiles(true)));
        $this->assertEquals(array('branch1', 'master'), $dir->getBranches());

        /* Test non-existant directory. */
        /*
        try {
            $this->vcs->getDirectory('foo');
            $this->fail('Expected Horde_Vcs_Exception');
        } catch (Horde_Vcs_Exception $e) {
        }
        */
    }

    public function testFile()
    {
        /* Test top-level file. */
        $file = $this->vcs->getFile('file1');
        $this->assertInstanceOf('Horde_Vcs_File_Git', $file);
        $this->assertEquals('file1', $file->getFileName());
        $this->assertEquals('file1', $file->getSourcerootPath());
        $this->assertEquals('file1', $file->getPath());
        $this->assertEquals(
            'da46ee2e478c6d3a9963eaafcd8f43e83d630526',
            $file->getRevision());
        $this->assertEquals(
            'd8561cd227c800ee5b0720701c8b6b77e6f6db4a',
            $file->getPreviousRevision('160a468250615b713a7e33d34243530afc4682a9'));
        $this->assertEquals(
             '160a468250615b713a7e33d34243530afc4682a9',
             $file->getPreviousRevision('da46ee2e478c6d3a9963eaafcd8f43e83d630526'));
        $this->assertEquals(3, $file->revisionCount());
        $this->assertEquals(array('tag1' => '160a468250615b713a7e33d34243530afc4682a9'),
                                  $file->getTags());
        $this->assertEquals(
            array('master' => '160a468250615b713a7e33d34243530afc4682a9',
                  'branch1' => 'da46ee2e478c6d3a9963eaafcd8f43e83d630526'),
            $file->getBranches());
        $this->assertFalse($file->isDeleted());

        /* Test master branch. */
        $file = $this->vcs->getFile('file1', array('branch' => 'master'));
        $this->assertEquals(
            '160a468250615b713a7e33d34243530afc4682a9',
            $file->getRevision());
        $this->assertEquals(
            'd8561cd227c800ee5b0720701c8b6b77e6f6db4a',
            $file->getPreviousRevision('160a468250615b713a7e33d34243530afc4682a9'));
        $this->assertEquals(2, $file->revisionCount());

        /* Test branch1 branch. */
        $file = $this->vcs->getFile('file1', array('branch' => 'branch1'));
        $this->assertEquals(
            'da46ee2e478c6d3a9963eaafcd8f43e83d630526',
            $file->getRevision());
        $this->assertEquals(
            'd8561cd227c800ee5b0720701c8b6b77e6f6db4a',
            $file->getPreviousRevision('da46ee2e478c6d3a9963eaafcd8f43e83d630526'));
        $this->assertEquals(2, $file->revisionCount());

        $log = $file->getLastLog();
        $this->assertInstanceOf('Horde_Vcs_Log_Git', $log);

        /* Test sub-directory file. */
        $file = $this->vcs->getFile('dir1/file1_1');
        $this->assertInstanceOf('Horde_Vcs_File_Git', $file);
        $this->assertEquals('file1_1', $file->getFileName());
        $this->assertEquals('dir1/file1_1', $file->getSourcerootPath());
        $this->assertEquals('dir1/file1_1', $file->getPath());
        $this->assertEquals(
            'd8561cd227c800ee5b0720701c8b6b77e6f6db4a',
            $file->getRevision());
        $this->assertEquals(1, $file->revisionCount());
        $this->assertEquals(array('tag1' => '160a468250615b713a7e33d34243530afc4682a9'),
                                  $file->getTags());
        $this->assertEquals(
            array('master' => '160a468250615b713a7e33d34243530afc4682a9',
                  'branch1' => 'da46ee2e478c6d3a9963eaafcd8f43e83d630526'),
            $file->getBranches());
        $this->assertFalse($file->isDeleted());

        /* Test deleted file. */
        $file = $this->vcs->getFile('deletedfile1');
        $this->assertInstanceOf('Horde_Vcs_File_Git', $file);
        $this->assertEquals('deletedfile1', $file->getFileName());
        $this->assertEquals('deletedfile1', $file->getSourcerootPath());
        $this->assertEquals('deletedfile1', $file->getPath());
        /* FIXME
        $this->assertEquals('1.2', $file->getRevision());
        $this->assertEquals('1.1', $file->getPreviousRevision('1.2'));
        $this->assertEquals(2, $file->revisionCount());
        $this->assertEquals(array(), $file->getTags());
        $this->assertTrue($file->isDeleted());
        */

        /* Test non-existant file. */
        $file = $this->vcs->getFile('foo');
        $this->assertInstanceOf('Horde_Vcs_File_Git', $file);
    }

    public function testLog()
    {
        $logs = $this->vcs->getFile('file1')->getLog();
        $this->assertInternalType('array', $logs);
        $this->assertEquals(
            array('da46ee2e478c6d3a9963eaafcd8f43e83d630526',
                  '160a468250615b713a7e33d34243530afc4682a9',
                  'd8561cd227c800ee5b0720701c8b6b77e6f6db4a'),
            array_keys($logs));
        $this->assertInstanceOf(
            'Horde_Vcs_Log_Git',
            $logs['160a468250615b713a7e33d34243530afc4682a9']);

        $log = $logs['160a468250615b713a7e33d34243530afc4682a9'];
        $this->assertEquals(
            '160a468250615b713a7e33d34243530afc4682a9',
            $log->getRevision());
        $this->assertEquals(1322495899, $log->getDate());
        $this->assertEquals('Jan Schneider <jan@horde.org>', $log->getAuthor());
        $this->assertEquals(
            'Commit 2nd version to master branch.',
            $log->getMessage());
        $this->assertEquals(array('master'), $log->getBranch());
        //FIXME $this->assertEquals('+1 -1', $log->getChanges());
        $this->assertEquals(array(), $log->getTags());
        $this->assertEquals(array(), $log->getSymbolicBranches());
        $this->assertEquals(
            array('file1' => array(
                'srcMode' => '100644',
                'dstMode' => '100644',
                'srcSha1' => 'd00491fd7e5bb6fa28c517a0bb32b8b506539d4d',
                'dstSha1' => '0cfbf08886fca9a91cb753ec8734c84fcbe52c9f',
                'status'  => 'M',
                'srcPath' => 'file1',
                'dstPath' => '',
                'added'   => '1',
                'deleted' => '1')),
            $log->getFiles());
        $this->assertEquals(1, $log->getAddedLines());
        $this->assertEquals(1, $log->getDeletedLines());

        $log = $logs['d8561cd227c800ee5b0720701c8b6b77e6f6db4a'];
        $this->assertEquals(
            'd8561cd227c800ee5b0720701c8b6b77e6f6db4a',
            $log->getRevision());
        $this->assertEquals(1322253995, $log->getDate());
        $this->assertEquals('Jan Schneider <jan@horde.org>', $log->getAuthor());
        $this->assertEquals(
            'Add first files.',
            $log->getMessage());
        $this->assertEquals(array('branch1', 'master'), $log->getBranch());
        //FIXME $this->assertEquals('+1 -1', $log->getChanges());
        $this->assertEquals(array(), $log->getTags());
        $this->assertEquals(array(), $log->getSymbolicBranches());
        $this->assertEquals(
            array('dir1/file1_1' => array(
                'srcMode' => '000000',
                'dstMode' => '100644',
                'srcSha1' => '0000000000000000000000000000000000000000',
                'dstSha1' => 'd00491fd7e5bb6fa28c517a0bb32b8b506539d4d',
                'status'  => 'A',
                'srcPath' => 'dir1/file1_1',
                'dstPath' => '',
                'added'   => '1',
                'deleted' => '0'),
                  'file1' => array(
                'srcMode' => '000000',
                'dstMode' => '100644',
                'srcSha1' => '0000000000000000000000000000000000000000',
                'dstSha1' => 'd00491fd7e5bb6fa28c517a0bb32b8b506539d4d',
                'status'  => 'A',
                'srcPath' => 'file1',
                'dstPath' => '',
                'added'   => '1',
                'deleted' => '0',
            )),
            $log->getFiles());
        $this->assertEquals(2, $log->getAddedLines());
        $this->assertEquals(0, $log->getDeletedLines());

        $log = $logs['da46ee2e478c6d3a9963eaafcd8f43e83d630526'];
        $this->assertEquals(
            'da46ee2e478c6d3a9963eaafcd8f43e83d630526',
            $log->getRevision());
        $this->assertEquals(1322495911, $log->getDate());
        $this->assertEquals('Jan Schneider <jan@horde.org>', $log->getAuthor());
        $this->assertEquals(
            'Commit 2nd version to branch1 branch.',
            $log->getMessage());
        $this->assertEquals(array('branch1'), $log->getBranch());
        //FIXME $this->assertEquals('+1 -1', $log->getChanges());
        $this->assertEquals(array(), $log->getTags());
        $this->assertEquals(array(), $log->getSymbolicBranches());
        $this->assertEquals(
            array('file1' => array(
                'srcMode' => '100644',
                'dstMode' => '100644',
                'srcSha1' => 'd00491fd7e5bb6fa28c517a0bb32b8b506539d4d',
                'dstSha1' => '0cfbf08886fca9a91cb753ec8734c84fcbe52c9f',
                'status'  => 'M',
                'srcPath' => 'file1',
                'dstPath' => '',
                'added'   => '1',
                'deleted' => '1')),
            $log->getFiles());
        $this->assertEquals(1, $log->getAddedLines());
        $this->assertEquals(1, $log->getDeletedLines());
    }

    public function testPatchset()
    {
        $this->markTestSkipped();
        try {
            $ps = $this->vcs->getPatchset(array('file' => 'foo'));
            $this->fail('Expected Horde_Vcs_Exception');
        } catch (Horde_Vcs_Exception $e) {
        }
        //$this->assertInstanceOf('Horde_Vcs_Patchset_Git', $ps);
    }
}