<?php
namespace Tpg\ExtjsBundle\Tests\Command;

include_once(__DIR__.'/../app/AppKernel.php');

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tpg\ExtjsBundle\Command\GenerateRestControllerCommand;

class GenerateRestControllerCommandTest extends \PHPUnit_Framework_TestCase {
    public function testGenerateController() {
        $kernel = new \AppKernel('test', true);
        $app = new Application($kernel);
        $app->add(new GenerateRestControllerCommand());
        $kernel->boot();
        $command = $app->find('generate:rest:controller');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            '--controller' => 'TestTestBundle:Car',
            '--entity' => 'TestTestBundle:Car'
        ), array('interactive'=>false));
        $kernel->shutdown();
    }
}