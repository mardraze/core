<?php
/**
 * This source is under Mardraze License
 * http://mardraze.pl/license
 *
 * User: mardraze
 * Date: 09.03.15
 */

namespace Mardraze\CoreBundle\Service;

use Symfony\Component\Security\Core\User\User;

class Dependencies {


    protected $container;

    public function __construct($container){
        $this->container = $container;
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    public function getDoctrine(){
        return $this->get('doctrine');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getManager(){
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection(){
        return $this->getDoctrine()->getConnection();
    }

    /**
     * @return \Swift_Mailer
     */
    public function getMailer(){
        return $this->get('swiftmailer.mailer.default');
    }

    public function getParameter($str){
        return $this->container->getParameter($str);
    }

    public function get($str){
        return $this->container->get($str);
    }

    public function getWebDir(){
        return $this->getParameter('kernel.root_dir').'/../web';
    }

    /**
     * @return \Symfony\Bridge\Monolog\Logger
     */
    public function getLogger(){
        return $this->get('logger');
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    public function getTranslator(){
        return $this->get('translator');
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher(){
        return $this->get('event_dispatcher');
    }

    /**
     * @return  \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    public function getSecurityTokenStorage(){
        return $this->container->get('security.token_storage');
    }
    /**
     * @return  \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser(){
        $user = $this->getSecurityTokenStorage()->getToken();
        if($user){
            return $user->getUser();
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSession(){
        return $this->container->get('session');
    }


    /**
     * @return \Doctrine\Common\Cache\FilesystemCache
     */
    public function getCache(){
        return $this->container->get('mardraze_core.cache');
    }

    public function setFlash($key, $value){
        $this->getSession()->getFlashBag()->set($key, $value);
    }

    public function getFlash($key){
        $this->getSession()->getFlashBag()->get($key);
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig(){
        return $this->get('twig');
    }

    /**
     * @param $template
     * @param array $parameters
     * @param null $from
     * @return \Swift_Message
     */
    public function getEmailBody($templateStr, $parameters = array(), $from = null) {
        $template = $this->getTwig()->loadTemplate($templateStr);
        $subject = $template->renderBlock('subject', $parameters);
        if(!$subject){
            throw new \Exception('Subject is empty');
        }
        $bodyHtml = $template->renderBlock('message_html', $parameters);
        $bodyText = '';
        try{
            $bodyText = trim($template->renderBlock('message_text', $parameters));
        }catch(\Exception $ex){
            $bodyText = strip_tags($bodyHtml);
        }
        if(!$from){
            if($this->container->hasParameter('delivery_address')){
                $from = $this->getParameter('delivery_address');
            }else{
                $from = $this->getParameter('mailer_user');
            }
        }

        $msg = $this->getMailer()->createMessage()
            ->setSubject($subject)
            ->setBody($bodyHtml, 'text/html')
            ->setFrom($from)
        ;
        return $msg;
    }

    public function sendEmail($addresses, $template, $parameters = array(), $from = null, $attachments = array(), $options = array()) {
        $msg = $this->getEmailBody($template, $parameters, $from);
        if(!is_array($addresses)){
            $addresses = array($addresses => $addresses);
        }
        if(array_key_exists('reply_to', $options)){
            $msg->setReplyTo($options['reply_to']);
        }else{
            if($this->container->hasParameter('mailer_replyto')){
                $msg->setReplyTo($this->getParameter('mailer_replyto'));
            }
        }
        $msg->setTo($addresses);
        foreach ($attachments as $attachment) {
            $att = null;
            if(is_array($attachment)){
                $att = \Swift_Attachment::fromPath($attachment['path']);
                $att->setFilename($attachment['name']);
            }else{
                $att = \Swift_Attachment::fromPath($attachment);
            }
            $msg->attach($att);
        }
        $failed = $this->getParameter('error_report_emails');
        return $this->getMailer()->send($msg, $failed);
    }

    public function touch($file){
        $dir = dirname($file);
        if(!file_exists($dir)){
            mkdir(dirname($file), 0777, true);
        }
        if(!file_exists($file)){
            touch($file);
        }
        return $file;
    }

    public function touchCache($file){
        return $this->touch($this->getParameter('kernel.cache_dir').'/'.$file, true);
    }


    public function isDebug(){
        return $this->getParameter('kernel.debug');
    }

    public function isProd(){
        return $this->getParameter('kernel.environment') == 'prod';
    }

    public function getAllRoutes(){
        $router = $this->container->get('router');
        $collection = $router->getRouteCollection();
        $allRoutes = $collection->all();
        return array_keys($allRoutes);
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    public function getRouter(){
        return $this->container->get('router');
    }

    public function httpAuth($login, $password){
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header("WWW-Authenticate: Basic realm=\"Private Area\"");
            header("HTTP/1.0 401 Unauthorized");
            print "Sorry - you need valid credentials to be granted access!\n";
            exit;
        } else {
            if (($_SERVER['PHP_AUTH_USER'] == $login) && ($_SERVER['PHP_AUTH_PW'] == $password)) {
            } else {
                header("WWW-Authenticate: Basic realm=\"Private Area\"");
                header("HTTP/1.0 401 Unauthorized");
                print "Sorry - you need valid credentials to be granted access!\n";
                exit;
            }
        }
    }

    public function setupRouter(){
        $context = $this->get('router')->getContext();
        $mainHost = $this->getParameter('mardraze_http_host');
        $host = preg_replace('/http(s)?:\/\//', '', $mainHost);
        $scheme = strpos($mainHost, 'https:') === false ? 'http' : 'https';
        $context->setHost($host);
        $context->setScheme($scheme);
    }

    /**
     * @param $name
     * @param $email
     * @param $password
     * @param array $roles
     * @return \Mardraze\CoreBundle\Entity\User
     */
    public function createUser($name, $email, $password, $roles = array(), $enabled = true){
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setUsername($name);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled($enabled);
        $roles = array_filter($roles);
        $user->setRoles($roles);
        $userManager->updateUser($user, true);
        return $user;
    }

    /**
     * @return \FOS\UserBundle\Model\UserManagerInterface
     */
    public function getUserManager(){
        return $this->get('fos_user.user_manager');
    }

    /**
     * @param $user \Mardraze\CoreBundle\Entity\User
     * @param $newPassword
     */
    public function changeUserPassword($user, $newPassword){
        $user->setPlainPassword($newPassword);
        $this->get('fos_user.user_manager')->updateUser($user, true);
    }

    public function fileExt($file){
        $basenameFile = basename($file);
        $ext = substr($basenameFile, strrpos($basenameFile, '.') + 1);
        return $ext;
    }

    public function arrayToXml($data, $root = 'root'){
        $xmlEncoder = new \Symfony\Component\Serializer\Encoder\XmlEncoder($root);
        $encoders = array($xmlEncoder);
        $serializer = new \Symfony\Component\Serializer\Serializer(array(), $encoders);
        return $serializer->serialize($data, 'xml');
    }

    public function authUser($user){
        $this->getSession()->clear();
        if($user){
            $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
                $user,
                null,
                'main',
                $user->getRoles());
            $this->get('security.context')->setToken($token);
        }else{
            $token = new \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken('', new User());

            $this->get('security.context')->setToken($token);
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function makeQueryBuilder(){
        return new \Doctrine\ORM\QueryBuilder($this->getManager());
    }

    public function getRepositoryTableName($repository){
        return $this->getManager()->getClassMetadata($repository)->getTableName();
    }

    public function callTwigFunction($functionName, $params){
        $function = $this->getTwig()->getFunction($functionName);
        if($function){
            return call_user_func_array($function->getCallable(), $params);
        }
    }

    private function geocodeRequest($q, $key, $googleParamName = null){
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?'.($googleParamName ? $googleParamName : 'address').'='.urlencode($q).'&language=pl&key='.$key;
        $json = $this->getCache()->fetch($url);
        if(!$json){
            $json = file_get_contents($url);
        }
        $resp = json_decode($json, true);
        if($resp['status'] == 'OK'){
            $this->getCache()->save($url, $json);
        }else{
            $this->getLogger()->error(var_export(array(
                'geocodeRequest', $q, $key, $googleParamName, $resp
            ), true));
        }
        return $resp;
    }

    public function getUrlMimeType($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        $content = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        return $contentType;
    }

}
