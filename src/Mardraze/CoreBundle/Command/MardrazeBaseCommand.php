<?php

namespace Mardraze\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BaseController extends Controller
{
    /**
     * @var \Mardraze\CoreBundle\Service\Dependencies
     */
    protected $dependencies;

    public function setContainer(ContainerInterface $container = null) {
        parent::setContainer($container);

        $this->dependencies = $this->get('mardraze_core.dependencies');
    }

    public function redirect404($msg = ''){
        throw $this->createNotFoundException($msg);
    }

    /**
     * @param bool $condition
     * @param string $msg
     */
    public function redirect404Unless($condition, $msg = ''){
        if(!$condition){
            throw $this->createNotFoundException($msg);
        }
    }

    /**
     * @param string $msg
     */
    public function setNotice($msg){
        $this->dependencies->getRequest()->getSession()->getFlashBag()->set('notice', $msg);
    }

    public function getParameter($str){
        return $this->container->getParameter($str);
    }

    public function get($str){
        return $this->container->get($str);
    }

    public function setFlash($key, $value){
        $this->dependencies->getRequest()->getSession()->getFlashBag()->set($key, $value);
    }

    public function getFlash($key){
        $this->dependencies->getRequest()->getSession()->getFlashBag()->get($key);
    }

}
