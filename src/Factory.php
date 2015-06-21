<?php
/**
 * Created by PhpStorm.
 * User: ilyas
 * Date: 21.06.2015
 * Time: 10:52
 */

namespace WebSocketBridge;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

/**
 * Class Factory
 * @package WebSocketBridge
 */
class Factory {

    /**
     * @param $localSocket
     * @param $remoteSocket
     * @return Bridge
     */
    public static function create($localSocket,$remoteSocket)
    {
        $logger = new Logger();
        $writer = new Stream("php://output");
        $logger->addWriter($writer);
        return new Bridge($logger,$localSocket,$remoteSocket);
    }
}