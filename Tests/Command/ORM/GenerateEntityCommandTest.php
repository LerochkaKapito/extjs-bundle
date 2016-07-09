<?php

namespace Tpg\ExtjsBundle\Tests\Command\ORM;

include_once(__DIR__.'/../../app/AppKernel.php');

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * GenerateEntityCommandTest
 */
class GenerateEntityCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var string Path to temp dir */
    private static $tmpDir;
    /** @var string Path to data dir */
    private static $dataDir;
    /** @var Filesystem */
    private static $fs;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$tmpDir = __DIR__.'/tmp_data';
        self::$dataDir = __DIR__.'/GenerateEntityCommandTest';
        self::$fs = new Filesystem();
    }

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        self::$fs->remove(self::$tmpDir);
        self::$fs->mkdir(self::$tmpDir);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        self::$fs->remove(self::$tmpDir);
    }

    public function testGenerateEntityFromBundleInDir()
    {
        $kernel = new \AppKernel('test', true);
        $app = new Application($kernel);
        $app->all(); // for commands registration
        $command = $app->find('generate:extjs:entity');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array(
                'command' => $command->getName(),
                'name' => 'TestTestBundle',
                '--overwrite' => 'y',
                '--output' => self::$tmpDir,
            ),
            array('interactive' => false)
        );
        $kernel->shutdown();
        $this->assertFileExists(self::$tmpDir.'/Test/TestBundle/Entity/Car.js');
        $this->assertFileExists(self::$tmpDir.'/Test/TestBundle/Entity/CarOwner.js');
        $this->assertFileEquals(self::$dataDir.'/generated/Car.js', self::$tmpDir.'/Test/TestBundle/Entity/Car.js');
        $this->assertFileEquals(
            self::$dataDir.'/generated/CarOwner.js',
            self::$tmpDir.'/Test/TestBundle/Entity/CarOwner.js'
        );
    }
}