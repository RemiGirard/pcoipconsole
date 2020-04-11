<?php

namespace App\Service;

use App\Entity\Terminal;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Config\Definition\Exception\Exception;

// all the operations that can be done to a terminal with curl commands and one with ping
// every operations use an object Terminal with verified ipaddress and hostname to avoid injection
class operateTerminal
{
    private $connectionInitiated;
    private $cookie;

    public function __construct(Filesystem $filesystem)
    {
        // at the construction of this operation class it will define a temporary file to store the cookie to keep the identified  with password connection active
        $this->connectionInitiated = false;
        $this->cookie = $filesystem->tempnam('/tmp', 'pcoipconsole_');
    }

    public function __destruct()
    {
        // when the object is not used anymore it will remove the cookie temp file
        $filesystem = new Filesystem();
        $filesystem->remove($this->cookie);
    }

    public function ping($terminal)
    {
        // use bash ping command and return true or false
        exec(sprintf('ping -c 1 -W 5 %s', escapeshellarg($terminal->getIp())), $res, $rval);

        if ($rval === 0) {
            return 'true';
        } else {
            return 'false' ;
        }
    }

    public function initConnection($terminal)
    {
        // all operation start with initConnection to have access to the web interface, the initialisation happens just once per operation thanks to the $connectionInitiated variable
        // the password is written clear below is the same for every terminal, if the terminal doesn't have password the initialisation will not work
        exec('timeout 5 curl https://'.$terminal->getIp().'/cgi-bin/login --cookie-jar '.$this->cookie.' --insecure --data password_value=mik*tera! --data idle_timeout=0', $output, $status);

        if ($status != 0) {
            throw new Exception('curl timout: can\'t access terminal');
        } else {
            // this second curl command makes sure the initialisation worked
            exec('timeout 5 curl https://'.$terminal->getIp().'/home.html --cookie '.$this->cookie.' --insecure', $html, $status);
            $output = implode($html);
            $crawler = new Crawler($output);
            $output = $crawler->filter('#logout');
    
            if (!$output->count()) {
                return 'Can\'t login in terminal';
            } else {
                $this->connectionInitiated = true;
                return 'logged';
            }
        }
    }

    public function initConnectionWithHostname($terminal)
    {
        // only used by getIpFromHostname
        exec('timeout 5 curl https://'.$terminal->getName().'/cgi-bin/login --cookie-jar '.$this->cookie.' --insecure --data password_value=mik*tera! --data idle_timeout=0', $output, $status);
        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            $this->connectionInitiated = true;
            return implode($output);
        }
    }
            
    public function reboot($terminal)
    {
        // reboot a client (host can't be reboot yet)
        if (!$this->connectionInitiated) {
            $this->initConnection($terminal);
        }
        exec('timeout 5 curl https://'.$terminal->getIp().'/cgi-bin/diagnostics/pcoip --cookie '.$this->cookie.' --insecure --data reset=1', $output, $status);
        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            return 'Reboot done';
        }
    }
                                          
    public function connect($terminal)
    {
        // equivalent to click on "Connect" on the terminal
        if (!$this->connectionInitiated) {
            $this->initConnection($terminal);
        }
        exec('timeout 5 curl https://'.$terminal->getIp().'/cgi-bin/ajax/diagnostics/session_control?connect=0 --cookie '.$this->cookie.' --insecure -X POST', $output, $status);
        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            return 'Connect done';
        }
    }

    public function disconnectTerminal($terminal)
    {
        // equivalent to pressing the button of a client to disconnect from active connection (works on host as well)
        if (!$this->connectionInitiated) {
            $shelloutput = $this->initConnection($terminal);
        }
        exec('timeout 5 curl https://'.$terminal->getIp().'/cgi-bin/ajax/diagnostics/session_control?disconnect=0 --cookie '.$this->cookie.' --insecure -X POST', $output, $status);
        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            return 'Disconnect done '.$shelloutput;
        }
    }

    public function changeHost($terminalClient, $terminalHost)
    {
        // this command change the host configuration of a client, it needs to be configured in directToHost
        $shelloutput = $this->disconnectTerminal($terminalHost);
        $this->connectionInitiated = false;
        $shelloutput .= $this->disconnectTerminal($terminalClient);
        exec('timeout 5 curl https://'.$terminalClient->getIp().'/cgi-bin/configuration/session --cookie '.$this->cookie.' --insecure --data peer_address='.$terminalHost->getIp(), $output, $status);
        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            $shelloutput .= $this->connect($terminalClient);
            $shelloutput .= 'change host done';
            return $shelloutput;
        }
    }

    public function getConnectedTo($terminal)
    {
        // get the information of host configuration for a client and get active connection information for a host
        $output='';
        $status='';
        if (!$this->connectionInitiated) {
            $this->initConnection($terminal);
        }
        if ($terminal->getRole() == 'client') {
            exec('timeout 5 curl https://'.$terminal->getIp().'/configuration/session.html --cookie '.$this->cookie.' --insecure', $html, $status);
            $output = implode($html);
            $crawler = new Crawler($output);

            //$output = $crawler->filter('')->text();
            $output = $crawler->filter('input[name="peer_address"]')->extract(['value']);
            $output = implode($output);
        } else {
            if ($terminal->getConnectionState() == 'connected') {
                exec('timeout 5 curl https://'.$terminal->getIp().'/cgi-bin/ajax/diagnostics/session_state?session_state= --cookie '.$this->cookie.' --insecure', $html, $status);
                $output = implode($html);
                $crawler = new Crawler($output);
                $output = $crawler->filter('a')->text();
            } else {
                $output = '';
            }
        }

        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            return $output;
        }
    }

    public function getIp($terminal)
    {
        // connect to the interface with hostname and get the ip (could be simplified with a ping command)
        if (!$this->connectionInitiated) {
            $shelloutput = $this->initConnectionWithHostname($terminal);
        }
        exec('timeout 5 curl https://'.$terminal->getName().'/configuration/network.html --cookie '.$this->cookie.' --insecure', $html, $status);
        $output = implode($html);
        $crawler = new Crawler($output);

        $output = $crawler->filter('input[name="ip_address"]')->extract(['value']);
        $output = implode($output);

        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            return $output;
        }
    }

    public function getName($terminal)
    {
        // get the hostname of a terminal with webinterface
        if (!$this->connectionInitiated) {
            $shelloutput = $this->initConnection($terminal);
        }
        exec('timeout 5 curl https://'.$terminal->getIp().'/configuration/network.html --cookie '.$this->cookie.' --insecure', $html, $status);
    
        $output = implode($html);
        $crawler = new Crawler($output);
        $output = $crawler->filter('input[name="fqdn"]')->extract(['value']);
        $output = implode($output);
    
        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            return $output;
        }
    }

    public function getLabel($terminal)
    {
        // get label description information
        if (!$this->connectionInitiated) {
            $shelloutput = $this->initConnection($terminal);
        }
        exec('timeout 5 curl https://'.$terminal->getIp().'/configuration/label.html --cookie '.$this->cookie.' --insecure', $html, $status);

        $output = implode($html);
        $crawler = new Crawler($output);
        $output = $crawler->filter('input[name="pcoip_device_description"]')->extract(['value']);
        $output = implode($output);

        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            return $output;
        }
    }

    public function getRole($terminal)
    {
        // get the role of the terminal
        if (!$this->connectionInitiated) {
            $shelloutput = $this->initConnection($terminal);
        }
        exec('timeout 5 curl https://'.$terminal->getIp().'/login.html --cookie '.$this->cookie.' --insecure', $html, $status);

        $output = implode($html);
        $crawler = new Crawler($output);
        $output = $crawler->filter('h4')->text();

        if (strpos($output, 'Host') !== false) {
            $output = 'host';
        } elseif (strpos($output, 'Client') !== false) {
            $output = 'client';
        }

        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            return $output;
        }
    }

    public function getConnectionState($terminal)
    {
        // look if the terminal is currently in a active pcoip connection
        if (!$this->connectionInitiated) {
            $shelloutput = $this->initConnection($terminal);
        }
        exec('timeout 5 curl https://'.$terminal->getIp().'/cgi-bin/ajax/diagnostics/session_state?session_state= --cookie '.$this->cookie.' --insecure', $html, $status);
        $output = implode($html);
        //$crawler = new Crawler($output);
        //$output = $crawler->filter('#session_state')->text();
                                                                              
        if (strpos($output, 'Connected') !== false) {
            $output = 'connected';
        } else {
            $output = 'disconnected';
        }
                                                                              
        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            return $output;
        }
    }

    public function getBitRate($terminal)
    {
        // get the reception and transmission bitrate (could be improved by getting the packets count and calculate bitrate with difference other time)
        if (!$this->connectionInitiated) {
            $shelloutput = $this->initConnection($terminal);
        }
        exec('timeout 5 curl https://'.$terminal->getIp().'/cgi-bin/ajax/diagnostics/session_statistics?pcoip_statistics= --cookie '.$this->cookie.' --insecure', $html, $status);
        $output = implode($html);
        $crawler = new Crawler($output);
        $tx = $crawler->filter('tx_bw')->text();
        $tx = explode(" / ", $tx);
        $tx = $tx[1];

        $rx = $crawler->filter('rx_bw')->text();
        $rx = explode(" / ", $rx);
        $rx = $rx[1];

        $output = ["rx" => $rx, "tx" => $tx];

        if ($status != 0) {
            throw new Exception('curl timeout: can\'t access terminal');
        } else {
            return $output;
        }
    }
}
