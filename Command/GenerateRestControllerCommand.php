<?php
namespace Tpg\ExtjsBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Tpg\ExtjsBundle\Generator\RestControllerGenerator;

class GenerateRestControllerCommand extends GeneratorCommand {

    /** @var  InputInterface */
    protected $input;
    /**
     * @var OutputInterface
     */
    protected $output;

    public function configure() {
        $this
            ->setDefinition(array(
                    new InputOption(
                        'controller',
                        '',
                        InputOption::VALUE_REQUIRED,
                        'The name of the controller to create'
                    ),
                    new InputOption(
                        'entity',
                        '',
                        InputOption::VALUE_REQUIRED,
                        "Entity this rest controller will manage"
                    ),
                    new InputOption(
                        'trait',
                        't',
                        InputOption::VALUE_NONE,
                        "Generate Trait and Rest Controller"
                    ),
                    new InputOption(
                        'mongo',
                        'm',
                        InputOption::VALUE_NONE,
                        "Generate Rest Controller for Mongo Document"
                    ),
                    new InputOption(
                        'phpcr',
                        'p',
                        InputOption::VALUE_NONE,
                        "Generate Rest Controller for PHPCR Document"
                    )
            ))
            ->setDescription('Generates a controller')
            ->setHelp(<<<EOT
The <info>generate:rest:controller</info> command helps you generates new FOSRest based controllers inside bundles.

If you want to specify the controller and entity to generate the controller for,
<info>php app/console generate:controller --controller=AcmeBlogBundle:Post --entity=AcmeBlogBundle:Post</info>

Every generated file is based on a template. There are default templates but they can be overriden by placing custom
templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/SensioGeneratorBundle/skeleton/controller
APP_PATH/Resources/SensioGeneratorBundle/skeleton/controller</info>

EOT
            )
            ->setName('generate:rest:controller');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        Validators::validateEntityName($input->getOption('entity'));

        $dialog = $this->getDialogHelper();

        if ($input->getOption('trait')) {
            if (PHP_MAJOR_VERSION < 5 || PHP_MINOR_VERSION < 4) {
                throw new \RuntimeException('You need PHP > 5.4 to use trait feature');
            }
        }

        if (null === $input->getOption('controller')) {
            throw new \RuntimeException('The controller option must be provided.');
        }

        list($bundle, $controller) = $this->parseShortcutNotation($input->getOption('controller'));
        /** @var Bundle $bundle */
        if (is_string($bundle)) {
            $bundle = Validators::validateBundleName($bundle);

            try {
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exists.</>', $bundle));
            }
        }

        $dialog->writeSection($output, 'Controller generation: ' . $controller . 'Controller (' . $bundle->getName() . ')');

        /** @var RestControllerGenerator $generator */
        $generator = $this->getGenerator($bundle);
        if ($input->getOption('trait')) {
            $output->writeln("<info>Generating Controller with Traits</info>");
            $generator->setUseTrait(true);
            $generator->generate($bundle,$controller,'','');
            $output->writeln("<info>Trait Controller Generated</info>");
            $generator->setUseTrait(false);
            $generator->setTemplateFile('UseTraitController.php');
            try {
                $generator->generate($bundle,$controller,'','');
                $output->writeln("<info>Controller Generated</info>");
            } catch (\RuntimeException $e) {
                $output->writeln("<info>Controller Skipped</info>");
            }
        } else {
            $generator->generate($bundle,$controller,'','');
            $output->writeln("<info>Controller Generated</info>");
        }

        $output->writeln('Generating the bundle code: <info>OK</info>');

        $dialog->writeGeneratorSummary($output, array());

    }

    protected function createGenerator()
    {
        $generator = new RestControllerGenerator($this->getContainer()->get('filesystem'));
        list($bundle, $entity) = $this->parseShortcutNotation($this->input->getOption('entity'));
        $generator->setEntityName($entity);
        if (is_string($bundle)) {
            $bundle = Validators::validateBundleName($bundle);
            try {
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
            } catch (\Exception $e) {
                $this->output->writeln(sprintf('<bg=red>Bundle "%s" does not exists.</>', $bundle));
            }
        }
        /** @todo Check entity is a valid entity */
        if ($this->input->getOption("mongo")) {
            $generator->setMongo(true);
        }
            /** @todo Check entity is a valid entity */
        if ($this->input->getOption("phpcr")) {
            $generator->setPhpcr(true);
        }
        $generator->setEntityBundle($bundle);
        return $generator;
    }

    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $dirs = parent::getSkeletonDirs($bundle);
        $dirs[] = __DIR__.'/../Resources/skeleton';
        return $dirs;
    }


    public function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The controller name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Post)', $entity));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }
}
