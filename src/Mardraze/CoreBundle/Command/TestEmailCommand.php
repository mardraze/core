<?php
namespace Mardraze\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestEmailCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('mardraze:test:email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getKernel()->getContainer();

        $transport = \Swift_SmtpTransport::newInstance($container->getParameter('mailer_host'), $container->getParameter('mailer_port'), $container->getParameter('mailer_encryption'))
            ->setUsername($container->getParameter('mailer_user'))
            ->setPassword($container->getParameter('mailer_password'))
        ;
        $mailer = \Swift_Mailer::newInstance($transport);
        $message = \Swift_Message::newInstance('Mardraze CMS Mailer test')
            ->setFrom(array($container->getParameter('mailer_user') => 'Mardraze CMS Mailer test'))
            ->setTo('marcindr1@gmail.com')
            ->setBody('Mardraze CMS Mailer test')
        ;
        $result = $mailer->send($message);
        var_dump($result);
    }
}
