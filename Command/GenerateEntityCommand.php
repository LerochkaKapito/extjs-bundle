<?php

namespace Tpg\ExtjsBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Tpg\ExtjsBundle\Service\GeneratorService;

/**
 * Generates Extjs model based on entity
 */
class GenerateEntityCommand extends ContainerAwareCommand
{
    /** @var AnnotationReader A reader for docblock annotations */
    private $annotationReader;
    /** @var GeneratorService Extjs Generator */
    private $generator;
    /** @var Registry References all Doctrine connections and entity managers in a given Container */
    private $doctrine;
    /** @var KernelInterface Kernel */
    private $kernel;

    /**
     * Constructor
     *
     * @param AnnotationReader $annotationReader A reader for docblock annotations
     * @param GeneratorService $generator Extjs Generator
     * @param Registry $doctrine References all Doctrine connections and entity managers in a given Container
     * @param KernelInterface $kernel Kernel
     */
    public function __construct(
        AnnotationReader $annotationReader,
        GeneratorService $generator,
        Registry $doctrine,
        KernelInterface $kernel
    ) {
        parent::__construct();

        $this->annotationReader = $annotationReader;
        $this->generator = $generator;
        $this->doctrine = $doctrine;
        $this->kernel = $kernel;
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        parent::configure();

        $this->setName('generate:extjs:entity');
        $this->addArgument('name', InputArgument::REQUIRED, "A bundle name, a namespace, or a class name");
        $this->addOption(
            'output',
            '',
            InputOption::VALUE_OPTIONAL,
            "File/Directory for the output of the ExtJs model file"
        );
        $this->addOption('overwrite', 'y', InputOption::VALUE_NONE, "Overwrite existing file");
        $this->setDescription("Generate Sencha ExtJs model base on an existing PHP entity");
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $outputLocation = null;
        if ($input->hasOption("output")) {
            $outputLocation = $this->getOutputLocationFromOutputOption($input->getOption("output"), $input, $output);
            if ($outputLocation === null) {
                return 1;
            }
        }

        $metadata = $this->getMetadata($input, $output, $outputLocation === null);
        foreach ($metadata->getMetadata() as $classMetadata) {
            /** @var ClassMetadata $classMetadata */
            $classMetadata->reflClass = new \ReflectionClass($classMetadata->name);
            if ($this->annotationReader->getClassAnnotation(
                    $classMetadata->getReflectionClass(),
                    'Tpg\ExtjsBundle\Annotation\Model'
                ) !== null
            ) {
                if ($outputLocation) {
                    if (is_dir($outputLocation)) {
                        $baseDir = $outputLocation;
                        foreach (explode("\\", $classMetadata->namespace) as $dir) {
                            @mkdir($baseDir.DIRECTORY_SEPARATOR.$dir);
                            $baseDir .= DIRECTORY_SEPARATOR.$dir;
                        }
                        $fileName = $baseDir.DIRECTORY_SEPARATOR.substr(
                                $classMetadata->name,
                                strlen($classMetadata->namespace) + 1
                            ).".js";
                        if (!$this->canWriteFile($input, $output, $fileName)) {
                            continue;
                        }
                        file_put_contents(
                            $fileName,
                            $this->generator->generateMarkupForEntity($classMetadata->name)
                        );
                        $output->writeln("Generated $fileName");
                    } else {
                        file_put_contents(
                            $outputLocation,
                            $this->generator->generateMarkupForEntity($classMetadata->name),
                            FILE_APPEND
                        );
                        $output->writeln("Appending to $outputLocation");
                    }
                } else {
                    $output->write($this->generator->generateMarkupForEntity($classMetadata->name));
                }
            }
        }

        return 0;
    }

    /**
     * Get path to output location
     *
     * @param string $outputPath Output path to process
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|string Path to output location or null, if location not recognized|found
     */
    protected function getOutputLocationFromOutputOption($outputPath, InputInterface $input, OutputInterface $output)
    {
        if (is_dir($outputPath)) {
            return realpath($outputPath);
        }

        // if outputPath is not a dir, than it is file
        $outputFilePath = $outputPath;
        if (!is_dir(dirname($outputFilePath))) {
            $output->writeln("Invalid output directory");

            return null;
        }

        if (!$this->canWriteFile($input, $output, $outputFilePath)) {
            return null;
        }

        file_put_contents($outputFilePath, '');

        return realpath($outputFilePath);
    }

    protected function getMetadata(InputInterface $input, OutputInterface $output, $displayStatus)
    {
        $manager = new DisconnectedMetadataFactory($this->doctrine);
        try {
            $bundle = $this->kernel->getBundle($input->getArgument('name'));
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

    /**
     * Check can write to file
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @param string $fileName Filename
     *
     * @return bool Can write to file
     */
    protected function canWriteFile(InputInterface $input, OutputInterface $output, $fileName)
    {
        if (!file_exists($fileName) || $input->getOption("overwrite")) {
            return true;
        }

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
    }
}