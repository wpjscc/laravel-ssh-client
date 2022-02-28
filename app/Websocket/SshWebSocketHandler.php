<?php namespace App\Websocket;

use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use phpseclib3\Net\SSH2;
use BeyondCode\LaravelWebSockets\Apps\App;
use phpseclib3\Crypt\PublicKeyLoader;

class SshWebSocketHandler implements MessageComponentInterface
{

    protected $clients;
    protected $connection = [];
    protected $conectado = [];
    protected $idConexion = [];

    const COLS = 80;
    const ROWS = 24;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $socketId = sprintf('%d.%d', random_int(1, 1000000000), random_int(1, 1000000000));
        $conn->socketId = $socketId;
        $conn->app = App::findById(env('PUSHER_APP_ID'));

        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        $this->connection[$conn->resourceId] = null;
        $this->conectado[$conn->resourceId] = null;
        $this->idConexion[$conn->resourceId] = null;
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        if(!is_array($data)){
            // $from->send(mb_convert_encoding("command error\r\n", "UTF-8"));
            return ;
        }
        switch (key($data)) {
        case 'data':
            echo $data['data']['data']."\n";
            if($this->connection[$from->resourceId]){
                $this->connection[$from->resourceId]->write($data['data']['data']);
                $this->connection[$from->resourceId]->setTimeout(0.01);
                if($line = $this->connection[$from->resourceId]->read()) {
                    $from->send(mb_convert_encoding($line, "UTF-8"));
                    $this->resend($line, $from);
                }
            }

            echo "5\n";

            break;
        case 'auth':
            echo "1\n";
            $from->send(mb_convert_encoding("Connecting to ".$data['auth']['server']."....\r\n", "UTF-8"));
            if ($this->connectSSH($data['auth']['idconnection'], $data['auth']['server'], $data['auth']['port'], $data['auth']['user'], $data['auth']['password'], $from, $data['auth']['type']??1, $data['auth']['certificate']??'')) {
                echo "3\n";

                $from->send(mb_convert_encoding("Connected....", "UTF-8"));
                $this->connection[$from->resourceId]->setTimeout(1);
                if($line = $this->connection[$from->resourceId]->read()) {
                    $from->send(mb_convert_encoding($line, "UTF-8"));
                    $this->resend($line, $from);
                }
                echo "4\n";
            }else{
                $from->send(mb_convert_encoding("Error, can not connect to the server. Check the credentials\r\n", "UTF-8"));
                $from->close();
            }
            break;
        case 'sharessh':
            //Only root user connection read the connection
            $this->conectado[$from->resourceId]=false;
            $this->idConexion[$from->resourceId]=$data['sharessh']['idconnection'];
            $from->send("You are now viewing the ssh connection id ".$data['sharessh']['idconnection']."\r\n", "UTF-8");
            break;
        default:
            if ($this->connection[$from->resourceId]) {
                $this->connection[$from->resourceId]->setTimeout(0.01);
                if($line = $this->connection[$from->resourceId]->read()) {
                      $from->send(mb_convert_encoding($line, "UTF-8"));
                      $this->resend($line, $from);
                }
            }
            break;
        }
    }

    protected function resend($line, $from)
    {
        foreach ($this->clients as $client) {
            if ($client->resourceId == $from->resourceId) {
                continue;
            }

            if ($this->idConexion[$client->resourceId] == $this->idConexion[$from->resourceId]) {
                $client->send(mb_convert_encoding($line, "UTF-8"));
            }
        }
    }

    public function connectSSH($idConnection, $server, $port, $user, $password, $from, $type = 1, $certificate = '')
    {
        $this->connection[$from->resourceId] = new SSH2($server, $port);

        if ($this->connection[$from->resourceId] === false) {
            $from->send("Error during connection to ".$server." at port ".$port."\r\n", "UTF-8");
            return false;
        }

        if ($type==1) {
            if ($this->connection[$from->resourceId]->login($user, $password)) {
                $from->send("Authentication Successful for server ".$server." at port ".$port."!\r\n", "UTF-8");
                $from->send("Your id connection is ".$idConnection."\r\n", "UTF-8");
                $this->conectado[$from->resourceId]=true;
                $this->idConexion[$from->resourceId]=$idConnection;
                return true;
            } else {
                $from->send("Wrong username ".$user." and password for server ".$server." at port ".$port."\r\n", "UTF-8");
                return false;
            }
        } else if ($type==2){
            try {
                $key = PublicKeyLoader::load($certificate);

            }catch(\Exception $e){
                $from->send($e->getMessage());
                $from->close();
            }

            if ($this->connection[$from->resourceId]->login($user, $key)) {
                $from->send("Authentication Successful for server ".$server." at port ".$port."!\r\n", "UTF-8");
                $from->send("Your id connection is ".$idConnection."\r\n", "UTF-8");
                $this->conectado[$from->resourceId]=true;
                $this->idConexion[$from->resourceId]=$idConnection;
                return true;
            } else {
                $from->send("Wrong username ".$user." and password for server ".$server." at port ".$port."\r\n", "UTF-8");
                return false;
            }
        } else {
            $from->send("Wrong type ".$type."\r\n", "UTF-8");
            return false;
        }


    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->conectado[$conn->resourceId]=false;
        $this->connection[$conn->resourceId]=false;

        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        var_dump($e->getMessage());
        $conn->close();
    }
}
