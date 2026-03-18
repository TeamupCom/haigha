<?php

namespace Haigha\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LinkORB\Component\DatabaseManager\DatabaseManager;
use Nelmio\Alice\Fixtures\Loader as AliceLoader;
use Haigha\TableRecordInstantiator;
use Haigha\Persister\PdoPersister;
use Haigha\Exception\FileNotFoundException;

class LoadCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('fixtures:load')
            ->setDescription('Load Alice fixture data into database')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Filename'
            )
            ->addArgument(
                'url',
                InputArgument::OPTIONAL,
                'Database connection url'
            )
            ->addOption(
                'dry-run',
                'd',
                InputOption::VALUE_NONE,
                'Do not run any SQL query - just pass to output',
                null
            )
            ->addArgument(
                'autouuidfield',
                InputArgument::OPTIONAL,
                'Fieldname for automatically generating uuids on all records'
            )
            ->addOption(
                'append',
                'a',
                InputOption::VALUE_NONE,
                'Do not reset DB schema before loading fixtures',
                null
            )
            ->addOption(
                'locale',
                'l',
                InputOption::VALUE_REQUIRED,
                'Locale for Alice',
                'en_US'
            )
            ->addOption(
                'seed',
                null,
                InputOption::VALUE_REQUIRED,
                'Seed for Alice',
                1
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $dburl = $input->getArgument('url');
        if (!$dburl) {
            $dburl = getenv('PDO');
        }
        if (!$dburl) {
            throw new \RuntimeException('Database URL unspecified. Either pass as an argument, or configure your PDO environment variable.');
        }
        $filename  = $input->getArgument('filename');
        $autoUuidField  = $input->getArgument('autouuidfield');
        $locale = $input->getOption('locale');
        $seed = $input->getOption('seed');

        if (!file_exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        $manager = new DatabaseManager();
        $pdo = $manager->getPdo($dburl, 'default');

        $providers = array();
        $loader = new AliceLoader($locale, $providers, $seed);

        $instantiator = new TableRecordInstantiator();
        if ($autoUuidField) {
            $instantiator->setAutoUuidColumn($autoUuidField);
        }
        $loader->addInstantiator($instantiator);

        $output->writeln(sprintf(
            "Loading '%s' into %s",
            $filename,
            $dburl
        ));
        $objects = $loader->load($filename);

        $output->writeln(sprintf(
            "Persisting '%s' objects in database '%s'",
            count($objects),
            $dburl
        ));

        $persister = new PdoPersister($pdo, $output, $input->getOption('dry-run'));
        if (!is_null($input->getOption('append'))) {
            $persister->reset($objects);
        }
        $persister->persist($objects);

        $output->writeln("Done");

        return 0;
    }
}
