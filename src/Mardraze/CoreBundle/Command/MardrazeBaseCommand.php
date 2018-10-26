<?php

namespace Mardraze\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MardrazeBaseCommand extends Command{


    /**
     * @var \Mardraze\CoreBundle\Service\Dependencies
     */
    protected $dependencies;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    protected function configure() {
        $this->setName('mardraze:base');
    }
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->dependencies = $this->getApplication()->getKernel()->getContainer()->get('mardraze_core.dependencies');
        $context = $this->dependencies->get('router')->getContext();

        $mainHost = $this->dependencies->getParameter('mardraze_http_host');
        $host = preg_replace('/http(s)?:\/\//', '', $mainHost);
        $scheme = strpos($mainHost, 'https:') === false ? 'http' : 'https';
        $context->setHost($host);
        $context->setScheme($scheme);
        $this->input = $input;
        $this->output = $output;
        return parent::run($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
