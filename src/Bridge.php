<?php
/**
 * Created by PhpStorm.
 * User: ilyas
 * Date: 21.06.2015
 * Time: 10:51
 */

namespace WebSocketBridge;


use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;
use Devristo\Phpws\Server\WebSocketServer;
use WebSocket\Client as WebSocketClient;
use React\EventLoop\Factory as EventLoop;
use WebSocket\Exception;

/**
 * Class Bridge
 * @package WebSocketBridge
 */
class Bridge extends WebSocketUriHandler {

    protected $localSocketServer;
    protected $remoteSocket;
    protected $loop;

    /**
     * @param $logger
     * @param $localSocket
     * @param $remoteSocket
     */
    public function __construct($logger,$localSocket, $remoteSocket) {

        parent::__construct($logger);

        $this->loop = EventLoop::create();

        $this->remoteSocket = new WebSocketClient($remoteSocket);

        // Create a WebSocket server
        $this->localSocketServer = new WebSocketServer($localSocket, $this->loop, $logger);

    }

    /**
     * @param $localCertificate
     * @param bool $allowSelfSigned
     * @param bool $verifyPeer
     * @throws \Exception
     */
    public function setUpSSL($localCertificate,$allowSelfSigned = true,$verifyPeer = false) {

        if(!file_exists($localCertificate)) {
            throw new \Exception('Local certificate file cannot be found');
        }

        $context = stream_context_create();
        stream_context_set_option($context, 'ssl', 'local_cert', $localCertificate);
        stream_context_set_option($context, 'ssl', 'allow_self_signed', $allowSelfSigned);
        stream_context_set_option($context, 'ssl', 'verify_peer', $verifyPeer);
        $this->localSocketServer->setStreamContext($context);
        $this->sslReady = true;
    }

    /**
     * @param string $uri
     * @throws Exception
     */
    public function run($uri = '/bridge') {

        if(($this->localSocketServer->uri->getScheme() == 'ssl') && !isset($this->sslReady)) {
            throw new Exception('You must set up SSL options to start a secure socket');
        }

        $router = new \Devristo\Phpws\Server\UriHandler\ClientRouter($this->localSocketServer, $this->logger);
        $router->addRoute('#^'.$uri.'#i', $this);

        $this->localSocketServer->bind();

        $this->loop->run();
    }

    /**
     *
     * @param WebSocketTransportInterface $user
     */
    public function onConnect(WebSocketTransportInterface $user){
        $this->logger->notice($user->getIp() . ' has been connected as ' . $user->getId());
    }

    /**
     *
     * @param WebSocketTransportInterface $user
     * @param WebSocketMessageInterface $msg
     */
    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg) {
        $this->remoteSocket->send($msg->getData());
        $response = $this->remoteSocket->receive();
        $user->sendString($response);
    }


}