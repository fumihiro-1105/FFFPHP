<?php
/**
 * Created By Fumihiro
 * Created At 2014/06/20 23:26
 */

namespace core\test\database;

use core\library\database\Database;

require_once __DIR__ . '/../../library/database/Database.php';

class DbTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
    }

    /**
     * セッティング情報（yml ファイル）が正しく読み込まれているかをテスト
     */
    public function testSetting()
    {
        $db = new Database();
        $this->assertEquals('127.0.0.1', $db->getHost());
        $this->assertEquals(3306, $db->getPort());
        $this->assertEquals('msr', $db->getDatabase());
        $this->assertEquals('root', $db->getUser());
        $this->assertEquals('', $db->getPassword());
    }

    /**
     * セッティング情報（yml ファイル）が正しく読み込まれているかをテスト
     * コネクション切り替え
     */
    public function testSettingOtherConnection()
    {
        $db = new Database('platform');
        $this->assertEquals('db.platform.local', $db->getHost());
        $this->assertEquals(3306, $db->getPort());
        $this->assertEquals('msr_platform', $db->getDatabase());
        $this->assertEquals('root', $db->getUser());
        $this->assertEquals('', $db->getPassword());
    }

    /**
     * 接続のテスト
     */
    public function testDatabaseOpen()
    {
        $db = new Database();
        $this->assertTrue($db->open());
        $this->assertTrue($db->isOpen());
    }

    /**
     * 切断のテスト
     */
    public function testDatabaseClose()
    {
        $db = new Database();
        $this->assertTrue($db->open());
        $this->assertTrue($db->Close());
        $this->assertFalse($db->isOpen());
    }

    /**
     * 接続せずに切断をした場合
     */
    public function testDatabaseCloseNotOpen()
    {
        $db = new Database();
        $this->assertTrue($db->close());
        $this->assertFalse($db->isOpen());
    }

    /**
     * 切断を2回以上連続で行った場合
     */
    public function testDatabaseCloseAndClose()
    {
        $db = new Database();
        $this->assertTrue($db->open());
        $this->assertTrue($db->close());
        $this->assertTrue($db->close());
        $this->assertFalse($db->isOpen());
    }

}
