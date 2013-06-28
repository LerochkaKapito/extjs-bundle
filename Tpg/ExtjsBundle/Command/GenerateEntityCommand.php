<?php
namespace Tpg\ExtjsBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tpg\ExtjsBundle\Service\GeneratorService;

class GenerateEntityCommand extends ContainerAwareCommand {
    public function configure() {
        parent::configure();
        $this->setName('generate:extjs:entity');
        $this->addArgument('name', InputArgument::REQUIRED, "A bundle name, a namespace, or a class name");
        $this->setDescription("Generate Sencha ExtJs model base on an existing PHP entity");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));
        try {
            $bundle = $this->getContainer()->get("kernel")->getBundle($input->getArgument('name'));
            $output->writeln(sprintf('Generating entities for bundle "<info>%s</info>"', $bundle->getName()));
            $metadata = $manager->getBundleMetadata($bundle);
        } catch (\InvalidArgumentException $e) {
            $name = strtr($input->getArgument('name'), '/', '\\');

            if (false !== $pos = strpos($name, ':')) {
                $name = $this->getContainer()->get('doctrine')
                    ->getAliasNamespace(substr($name, 0, $pos)).'\\'.substr($name, $pos + 1);
            }

            if (class_exists($name)) {
                $output->writeln(sprintf('Generating entity "<info>%s</info>"', $name));
                $metadata = $manager->getClassMetadata($name, $input->getOption('path'));
            } else {
                $output->writeln(sprintf('Generating entities for namespace "<info>%s</info>"', $name));
                $metadata = $manager->getNamespaceMetadata($name, $input->getOption('path'));
            }
        }
        $reader = $this->getContainer()->get('annotation_reader');
        $generator = $this->getContainer()->get("tpg_extjs.generator");
        foreach($metadata->getMetadata() as $classMetadata) {
            /** @var ClassMetadata $classMetadata */
            $classMetadata->reflClass = new \ReflectionClass($classMetadata->name);
            if ($reader->getClassAnnotation($classMetadata->getReflectionClass(), 'Tpg\ExtjsBundle\Annotation\Model') !== null) {
                $output->write($generator->generateMarkupForEntity($classMetadata->name));
            }
        }
    }
}