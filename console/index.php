<?php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Application;

class TestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('test:hello')
            ->setDescription('test command')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                '要跟誰說hello?'
            )
            ->addOption(
                'from',
                null,
                InputOption::VALUE_OPTIONAL,
                '誰說的?'
            );
    }

    protected function execute(InputInterface $inp, OutputInterface $out)
    {
        $text = 'Hello';
        $name = $inp->getArgument('name') and $text = "{$text} {$name}";

        $from = $inp->getOption('from') and $text = "{$from}: {$text}";

        $format = "<fire>%s</fire>";

        $out->writeln(sprintf($format, $text));
    }
}

$app = new Application();
$app->add(new TestCommand());
$app->run();
