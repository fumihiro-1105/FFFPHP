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

    /**
     * SELECT 文を発行
     */
    public function testSelect()
    {
        $db = new Database();
        $db->open();
        $result = $db->query('select 1 + 1 as `sum`');
        $this->assertTrue(is_array($result));
        $this->assertEquals(1, count($result));
        $this->assertEquals(2, $result[0]->sum);
        $db->close();
    }

    /**
     * SELECT 文を発行（2行）
     */
    public function testSelectTwoRows()
    {
        $db = new Database();
        $db->open();
        $result = $db->query('select 1 + 1 as `sum` union all select 2 + 2 as `sum`');
        $this->assertTrue(is_array($result));
        $this->assertEquals(2, count($result));
        $this->assertEquals(2, $result[0]->sum);
        $this->assertEquals(4, $result[1]->sum);
        $db->close();
    }

    /**
     * DDL 文を発行（CREATE TABLE, DROP TABLE）
     */
    public function testDdl()
    {
        $db = new Database();
        $db->open();
        $ddl = 'create table test (id int(11) unsigned not null auto_increment, name varchar(255) not null, primary key (id))';
        $this->assertTrue($db->query($ddl));
        $ddl = 'drop table test';
        $this->assertTrue($db->query($ddl));
        $db->close();
    }

    /**
     * DDL, DQL を発行（CREATE TABLE, INSERT, UPDATE, DELETE, SELECT, DROP TABLE）
     */
    public function testDdlAndDQL()
    {
        $db = new Database();
        $db->open();
        $ddl = 'create table test (id int(11) unsigned not null auto_increment, name varchar(255) not null, primary key (id))';
        $this->assertTrue($db->query($ddl));

        $dql = "insert into test (name) values ('okumura')";
        $this->assertTrue($db->query($dql));

        $dql    = "select * from test";
        $result = $db->query($dql);
        $this->assertTrue(is_array($result));
        $this->assertEquals(1, count($result));
        $this->assertEquals(1, $result[0]->id);
        $this->assertEquals('okumura', $result[0]->name);

        $dql = "update test set name = 'F.Okumura' where id = 1";
        $this->assertTrue($db->query($dql));
        $dql    = "select * from test";
        $result = $db->query($dql);
        $this->assertTrue(is_array($result));
        $this->assertEquals(1, count($result));
        $this->assertEquals(1, $result[0]->id);
        $this->assertEquals('F.Okumura', $result[0]->name);

        $dql = "delete from test where name = 'F.Okumura'";
        $this->assertTrue($db->query($dql));
        $dql    = "select * from test";
        $result = $db->query($dql);
        $this->assertTrue(is_array($result));
        $this->assertEquals(0, count($result));

        $ddl = 'drop table test';
        $this->assertTrue($db->query($ddl));
        $db->close();
    }

}
