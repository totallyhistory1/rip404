<?php
// (A) COMMAND LINE ONLY!
if (isset($_SERVER["REMOTE_ADDR"]) || isset($_SERVER["HTTP_USER_AGENT"]) || !isset($_SERVER["argv"])) {
  exit("Please run this in the command line");
}

// (B) LOAD RATCHET
// https://github.com/ratchetphp/Ratchet
// composer require cboden/ratchet
require "vendor/autoload.php";
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

// (C) CHAT CLASS
class Chat implements MessageComponentInterface {
  // (C1) PROPERTIES
  private $debug = true; // Debug mode
  protected $clients; // Connected clients

  // (C2) CONSTRUCTOR - INIT LIST OF CLIENTS
  public function __construct () {
    $this->clients = new \SplObjectStorage;
    if ($this->debug) { echo "Chat server started.\r\n"; }
  }

  // (C3) ON CLIENT CONNECT - STORE INTO $THIS->CLIENTS
  public function onOpen (ConnectionInterface $conn) {
    $this->clients->attach($conn);
    if ($this->debug) { echo "Client connected: {$conn->resourceId}\r\n";  }
  }

  // (C4) ON CLIENT DISCONNECT - REMOVE FROM $THIS->CLIENTS
  public function onClose (ConnectionInterface $conn) {
    $this->clients->detach($conn);
    if ($this->debug) { echo "Client disconnected: {$conn->resourceId}\r\n";  }
  }

  // (C5) ON ERROR
  public function onError (ConnectionInterface $conn, \Exception $e) {
    $conn->close();
    if ($this->debug) { echo "Client error: {$conn->resourceId} | {$e->getMessage()}\r\n";  }
  }

  // (C6) ON RECEIVING MESSAGE FROM CLIENT - SEND TO EVERYONE
  public function onMessage (ConnectionInterface $from, $msg) {
    if ($this->debug) { echo "Received message from {$from->resourceId}: {$msg}\r\n"; }
    foreach ($this->clients as $client) { $client->send($msg); }
  }
}

// (D) WEBSOCKET SERVER START!
$server = IoServer::factory(new HttpServer(new WsServer(new Chat())), 8080); // @CHANGE if not port 8080
$server->run();
