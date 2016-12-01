<?php
namespace Mardraze\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SqlCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('sql')
            ->addArgument('bundle')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arg = $input->getArgument('bundle');
        if(!$arg){
            $arg = 'AppBundle';
        }
        $output->writeln(shell_exec('php app/console doctrine:generate:entities '.$arg.' --no-backup'));
        $output->writeln(shell_exec('php app/console doctrine:schema:update --force'));

    }
}
