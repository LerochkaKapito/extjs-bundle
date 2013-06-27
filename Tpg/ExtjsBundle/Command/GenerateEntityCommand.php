<?php
namespace Tpg\ExtjsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateEntityCommand extends ContainerAwareCommand {
    public function configure() {
        parent::configure();
        $this->setName('generate:extjs:entity');
        $this->addOption('entity', '', InputOption::VALUE_REQUIRED, "Entity this rest controller will manage");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        Validators::validateEntityName($input->getOption('entity'));
        parent::execute($input, $output);
    }
}