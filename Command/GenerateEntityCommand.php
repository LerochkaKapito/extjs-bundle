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
        $this->addOption('output', '', InputOption::VALUE_OPTIONAL, "File/Directory for the output of the ExtJs model file");
        $this->addOption('overwrite', 'y', InputOption::VALUE_NONE, "Overwrite existing file");
        $this->setDescription("Generate Sencha ExtJs model base on an existing PHP entity");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $reader = $this->getContainer()->get('annotation_reader');
        $generator = $this->getContainer()->get("tpg_extjs.generator");
        $outputLocation = false;
        if ($input->getOption("output")) {
            if (is_dir($input->getOption("output"))) {
                $outputLocation = realpath($input->getOption("output"));
            } else if (is_dir(dirname($input->getOption("output")))) {
                if (!$this->canWriteFile($input, $output, $input->getOption("output"))) {
                    exit(1);
                }
                file_put_contents($input->getOption("output"), '');
                $outputLocation = realpath($input->getOption("output"));
            } else {
                $output->writeln("Invalid output directory");
                exit(1);
            }
        }
        $metadata = $this->getMetadata($input, $output, $outputLocation===false);
        foreach($metadata->getMetadata() as $classMetadata) {
            /** @var ClassMetadata $classMetadata */
            $classMetadata->reflClass = new \ReflectionClass($classMetadata->name);
            if ($reader->getClassAnnotation($classMetadata->getReflectionClass(), 'Tpg\ExtjsBundle\Annotation\Model') !== null) {
                if ($outputLocation) {
                    if (is_dir($outputLocation)) {
                        $baseDir = $outputLocation;
                        foreach(explode("\\", $classMetadata->namespace) as $dir) {
                            @mkdir($baseDir.DIRECTORY_SEPARATOR.$dir);
                            $baseDir .= DIRECTORY_SEPARATOR.$dir;
                        }
                        $fileName = $baseDir.DIRECTORY_SEPARATOR.substr($classMetadata->name, strlen($classMetadata->namespace)+1).".js";
                        if (!$this->canWriteFile($input, $output, $fileName)) {
                            continue;
                        }
                        file_put_contents(
                            $fileName,
                            $generator->generateMarkupForEntity($classMetadata->name)
                        );
                        $output->writeln("Generated $fileName");
                    } else {
                        file_put_contents(
                            $outputLocation,
                            $generator->generateMarkupForEntity($classMetadata->name),
                            FILE_APPEND
                        );
                        $output->writeln("Appending to $outputLocation");
                    }
                } else {
                    $output->write($generator->generateMarkupForEntity($classMetadata->name));
                }
            }
        }
    }

    protected function getMetadata(InputInterface $input, OutputInterface $output, $displayStatus) {
        $manager = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));
        try {
            $bundle = $this->getContainer()->get("kernel")->getBundle($input->getArgument('name'));
            if ($displayStatus) {
                $output->writeln(sprintf('Generating entities for bundle "<info>%s</info>"', $bundle->getName()));
            }
            $metadata = $manager->getBundleMetadata($bundle);
        } catch (\InvalidArgumentException $e) {
            $name = strtr($input->getArgument('name'), '/', '\\');

            if (false !== $pos = strpos($name, ':')) {
                $name = $this->getContainer()->get('doctrine')
                        ->getAliasNamespace(substr($name, 0, $pos)).'\\'.substr($name, $pos + 1);
            }

            if (class_exists($name)) {
                if ($displayStatus) {
                    $output->writeln(sprintf('Generating entity "<info>%s</info>"', $name));
                }
                $metadata = $manager->getClassMetadata($name, $input->getOption('path'));
            } else {
                if ($displayStatus) {
                    $output->writeln(sprintf('Generating entities for namespace "<info>%s</info>"', $name));
                }
                $metadata = $manager->getNamespaceMetadata($name, $input->getOption('path'));
            }
        }
        return $metadata;
    }

    protected function canWriteFile(InputInterface $input, OutputInterface $output, $fileName) {
        if (!$input->getOption("overwrite") && file_exists($fileName)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $result = $dialog->askConfirmation(
                $output,
                '<question>'.$fileName.' already exist, overwrite?</question>',
                false
            );
            if (!$result) {
                $output->writeln("Skipping $fileName");
            }
            return $result;
        } else {
            return true;
        }
    }
}