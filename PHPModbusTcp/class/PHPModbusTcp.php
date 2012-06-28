<?php
/**
 * PHPModbusTcp
 *
 * @author Kohntark <kohntark@kohntark.fr> <kohntark@kohntark.fr>
 *
 * @link http://www.modbus.org/docs/Modbus_Messaging_Implementation_Guide_V1_0b.pdf MODBUS MESSAGING ON TCP/IP IMPLEMENTATION GUIDE V1.0b
 * @link http://www.modbus.org/docs/Modbus_Application_Protocol_V1_1b.pdf MODBUS APPLICATION PROTOCOL SPECIFICATION V1.1b
 * @link http://www.modbus.org/docs/MBConformanceTestSpec_v3.0.pdf Modbus Conformance Test Specifications v3.0
 *
 */
/**
 * 
 */
class PHPModbusTcp {

// mode debug
    const DEBUG = false;

    /**#@+
     *
     * @access public
     */


    /**
     * @var int $iStartAddress adresse de départ
     */
    public $iStartAddress = 0;

    /**
     * @var int $iQuantity quantité d'adresses succesives à lire
     */
    public $iQuantity = 16;


    /**#@-*/

    /**#@+
     *
     * @access protected
     */


    /**
     * @var string $sDeviceIp adresse IP de l'équipement
     */
    protected $sDeviceIp;

    /**
     *
     * @var int $iDevicePort port de l'équipement
     */
    protected $iDevicePort;




    /**
     *
     * @var int $iRcvTimeout timeout pour la reception
     */
    protected $iRcvTimeout;

    /**
     *
     * @var int $iSndTimeout timeout pour l'envoi
     */
    protected $iSndTimeout;

    /**
     *
     * (for Modbus Serial gateway or for reding registers)
     * The host client uses 255 as the default value for Unit Identifier
     * The range of acceptable addresses
     * for Modbus Serial devices is 1 to 247.
     *
     * @var $iDeviceId ID équipement ou pour accès registres
     * @todo à implémenter
     */
    protected $iDeviceId = 255;

    protected $aReceivedFrame = array();

    /**
     *
     * @var array $aModbusException tableau des exceptions Modbus
     *
     * @todo
     */
    protected $aMBException = array (
            1 => 'Illegal Function Code. The function code is unknown by the server',
            2 => 'Illegal Data Address. Dependant on the request',
            3 => 'Illegal Data Value. Dependant on the request',
            4 => 'Server Failure. The server failed during the execution',
            5 => 'Acknowledge. The server accepted the service invocation but the service requires a relatively long time to execute. The server therefore returns only an acknowledgement of the service invocation receipt.',
            6 => 'Server Busy. The server was unable to accept the MB Request PDU. The client application has the responsibility of deciding if and when to re-send the request.',
            10 => 'Gateway problem. Gateway paths not available.',
            11 => 'Gateway problem. The targeted device failed to respond. The gateway generates this exception'
    );

    /**#@-*/

    /**#@+
     *
     * @access private
     */
    /**
     *
     * @var resource socket
     */
    private $rSocket;



    /**#@-*/


    /**
     *
     * @param string $sDeviceIp IP de l'équipement modbus
     * @param int $iDevicePort port de l'équipement modbus
     * @param int optional $iSndTimeout timeout envoi
     * @param int optional $iRcvTimeout timeout reception
     *
     *
     * @todo
     * - gestion IPV6
     * - gestion modbus / Jbus
     * - gestion routage
     */

    public function  __construct($sDeviceIp, $iDevicePort = 502, $iDeviceId = 255, $iSndTimeout = 400, $iRcvTimeout = 300) {
        /*
         * crée le tableau à passer à la fonction filter_var_array()
        */
        $vars = compact('sDeviceIp', 'iDevicePort', 'iDeviceId', 'iSndTimeout', 'iRcvTimeout');

        /*
         * filtres à appliquer aux arguments passés
        */
        $filters = array(
                'sDeviceIp' => array('filter' => FILTER_VALIDATE_IP, 'flags' => FILTER_FLAG_IPV4),
                'iDevicePort' => FILTER_VALIDATE_INT,
                'iDeviceId' => FILTER_VALIDATE_INT,
                'iSndTimeout' => FILTER_VALIDATE_INT,
                'iRcvTimeout' => FILTER_VALIDATE_INT
        );

        /*
         * contrôle que les arguments passés à l'objet son corrects
        */
        if (in_array(FALSE, filter_var_array($vars, $filters))) throw new Exception("arguments passés incorrects : ".implode(', ', func_get_args()));


        // ID de l'équipement
        $this->iDeviceId = $iDeviceId;

        /*
         * création de la socket
        */
        if (false === $this->rSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
            throw new Exception(socket_strerror(socket_last_error()));
        }



        /*
         * option de la socket
        */
        //socket_set_option($this->rSocket, SOL_SOCKET, SO_KEEPALIVE, 300);
        socket_set_option($this->rSocket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => (int)($iSndTimeout / 1000), 'usec' => (int)($iSndTimeout%1000)*1000));
        socket_set_option($this->rSocket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => (int)($iRcvTimeout / 1000), 'usec' => (int)($iRcvTimeout%1000)*1000));
        //socket_set_option($this->rSocket, SOL_SOCKET, SO_REUSEADDR, 1);


        if (false === socket_connect($this->rSocket, $sDeviceIp, $iDevicePort)) {
            $this->socketClose();
            throw new Exception(socket_strerror(socket_last_error()));
        }

        $linger = array('l_linger' => 0, 'l_onoff' => 0);
        socket_set_option($this->rSocket, SOL_SOCKET, SO_LINGER, $linger);

        // contrôle d'éventuelles erreurs
        if (socket_last_error($this->rSocket) != 0)
            throw  new Exception(socket_strerror(socket_last_error($this->rSocket)));
    }




    /**
     * connexion à l'équipement distant
     */
    public function connect() {
        return true;

        if (false === socket_connect($this->rSocket, $this->sDeviceIp, $this->iDevicePort)) {
            $this->socketClose();
            throw new Exception(socket_strerror(socket_last_error()));
        }

    }


    /**
     * écrit les données $aOutBuf sur la socket
     *
     * @param array $sOutBuf tableau de la trame à écrire
     * return mixed false si erreur, nombre d'octets écrits sur la socket si réussi
     */
    public function writeSocket($aFrameToSend) {
        return socket_write($this->rSocket, implode("", $aFrameToSend ));
    }


    /**
     * lir les données de la socket
     *
     * @return string
     */
    function readSocket () {
        return $sRecBuf = socket_read($this->rSocket, 2048);
    }



    /**
     * Lis l'état d'une plage de sorties TOR
     *
     * @param int $iStartAddress adresse de début de lecture
     * @param int $iQuantity nombre d'entrées à lire
     * @return array résultat de la lecture sous la forme (dec)adresse=>(bool)valeur
     */
    public function readCoils($iStartAddress, $iQuantity) {

        if (!is_numeric($iStartAddress) || !is_numeric($iQuantity))
            throw new Exception('paramètres incorrects : l\'adresse de début ou le nombre de mots à lire ne sont pas des entiers');

        if ($iQuantity > 2000) $iQuantity = 2000; // nb d'inputs max lisible dans le standard modbus
        if ($iQuantity == 0) $iQuantity = 1; // quantité d'adresses min à lire

        /*
         * trame à envoyer
        */
        $this->aFrameToSend = array (0=>chr(0), chr(0), chr(0), chr(0), chr(0), 5=>chr(6), 6=>chr($this->iDeviceId), 7=>chr(1)) ;

        /*
         * adding datas (big endian) :
         * $obuf[8], $obuf[9] : start address
         * $obuf[10], $obuf[11] : quantity of address to read
         *
        */
        list($this->aFrameToSend[9], $this->aFrameToSend[8]) = Convert::WordToBytes((int)$iStartAddress);
        list($this->aFrameToSend[11], $this->aFrameToSend[10]) = Convert::WordToBytes((int)$iQuantity);

        $this->writeSocket($this->aFrameToSend);

        $sBuf = $this->readSocket();

        /**
         * contrôle réponse
         * si le 8eme bit reçu (code fonction) n'est pas égal au 8eme envoyé il s'agit d'une exception MB (function code + 0x80)
         */
        if ($sBuf[7] != $this->aFrameToSend[7]) {
            // code de l'exception renvoyée
            throw new Exception($this->aMBException[(ord($sBuf[7]) - 128)]." Start Adress : ".$iStartAddress." Quantity of outputs : ".$iQuantity);
        }

        //debug
        // echo 'nb de Bytes reçus : '.ord($abuf[8])."\r\n";

        // explose la chaîne reçue en un tableau de Bytes
        $this->aReceivedFrame = preg_split('``', $sBuf, NULL, PREG_SPLIT_NO_EMPTY);

        // ne conserve que les datas (== Byte 9 et suivants)
        $aDatasBytes = array_slice($this->aReceivedFrame, 9, ord($sBuf[8]));

        // converti chaque valeur (Byte) du tableau en format binaire
        $aDatasBits = array_map("Convert::ByteToBits", $aDatasBytes);

        // explose chaque bit de chaque octet dans un tableau unique
        $aDatasBits = preg_split('``', implode('', $aDatasBits), NULL, PREG_SPLIT_NO_EMPTY);

        // limite le nombre de bits demandés (le protocole MB n'envoyant que des octets complets)
        $aDatasBits = array_splice($aDatasBits, 0, $iQuantity);

        // crée le tableau des adresses
        $aAddress = range($iStartAddress, $iStartAddress + $iQuantity - 1);

        // retourne un tableau avec comme clés les adresses
        return array_combine($aAddress, $aDatasBits);
    }


    /**
     * Lis l'état d'entrées TOR
     *
     * @param int $iStartAddress adresse de début de lecture
     * @param int $iQuantity nombre d'entrées à lire
     * @return array résultat de la lecture sous la forme (dec)adresse=>(bool)valeur
     */
    public function readDiscreteInputs($iStartAddress, $iQuantity) {

        if (!is_numeric($iStartAddress) || !is_numeric($iQuantity))
            throw new Exception('paramètres incorrects : l\'adresse de début ou le nombre de mots à lire ne sont pas des entiers');

        if ($iQuantity > 2000) $iQuantity = 2000; // nb d'inputs max lisible dans le standard modbus
        if ($iQuantity == 0) $iQuantity = 1; // quantité d'adresses min à lire

        /*
         * Définition de la trame Modbus à envoyer sous forme d'un array qui sera écrit ensuite sur la socket
         *
         * MBAP (MODBUS Application Protocol) :
         * (!! Big Endian !!)
         *
         * Transaction Identifier (2 Bytes) :
         *  Identification of a MODBUS Request / Response transaction.
         *  Initialized by the client, Recopied by the server from the received request
         *
         * Protocol Identifier (2 Bytes) :
         *  MODBUS protocol = 0
         *  Initialized by the client, Recopied by the server from the received request
         *
         * Length (2 Bytes) :
         * Number of following bytes
         * Initialized by the client (request) and by the server (response)
         *
         * Unit Identifier (1 Byte) :
         * Identification of the remote slave (for sub-network on Modbus+ or Modbus serial line)
         *
         * ModBus Function (1 Byte)
         *
         *
         * $abuf = array (      // define an array for the MBAP header + the function (Byte 8)
         * 0=>chr(0),           // Transaction Identifier, first Byte
         * chr(0),              // Transaction Identifier, second Byte
         * chr(0),              // Protocol Identifier, first Byte
         * chr(0),              // Protocol Identifier, second Byte
         * chr(0),              // Length, first Byte
         * 5=>chr(6),           // Length, second Byte
         * 6=>chr($this->Unit), // Unit Identifier
         * 7=>chr(2) ) ;        // Modbus Function (here : Read Discrete Inputs (2))
         *
        */
        $this->aFrameToSend = array (0=>chr(0), chr(0), chr(0), chr(0), chr(0), 5=>chr(6), 6=>chr($this->iDeviceId), 7=>chr(2)) ;

        /*
         * adding datas (big endian) :
         * $obuf[8], $obuf[9] : start address
         * $obuf[10], $obuf[11] : quantity of address to read
         *
        */
        list($this->aFrameToSend[9], $this->aFrameToSend[8]) = Convert::WordToBytes((int)$iStartAddress);
        list($this->aFrameToSend[11], $this->aFrameToSend[10]) = Convert::WordToBytes((int)$iQuantity);

        $this->writeSocket($this->aFrameToSend);

        $sBuf = $this->readSocket();


        //debug
        //        echo $abuf.'<br />';
        //
        //        for ($i=0; $i < strlen($abuf); $i++) {
        //            echo $i.'=>'.ord($abuf[$i]).'<br />';
        //        }

        /**
         * contrôle réponse
         * si le 8eme bit reçu (code fonction) n'est pas égal au 8eme envoyé il s'agit d'une exception MB (function code + 0x80)
         */
        if ($sBuf[7] != $this->aFrameToSend[7]) {
            // code de l'exception renvoyée
            throw new Exception($this->aMBException[(ord($sBuf[7]) - 128)]." Start Adress : ".$iStartAddress." Quantity of inputs : ".$iQuantity);
        }


        //debug
        // echo 'nb de Bytes reçus : '.ord($abuf[8])."\r\n";

        // explose la chaîne reçue en un tableau de Bytes
        $this->aReceivedFrame = preg_split('``', $sBuf, NULL, PREG_SPLIT_NO_EMPTY);

        //        print_r($this->aReceivedFrame);
        //

        // ne conserve que les datas (== Byte 9 et suivants)
        $aDatasBytes = array_slice($this->aReceivedFrame, 9, ord($sBuf[8]));

        // converti chaque valeur (Byte) du tableau en format binaire
        $aDatasBits = array_map("Convert::ByteToBits", $aDatasBytes);

        // explose chaque bit de chaque octet dans un tableau unique
        $aDatasBits = preg_split('``', implode('', $aDatasBits), NULL, PREG_SPLIT_NO_EMPTY);

        // limite le nombre de bits demandés (le protocole MB n'envoyant que des octets complets)
        $aDatasBits = array_splice($aDatasBits, 0, $iQuantity);

        // crée le tableau des adresses
        $aAddress = range($iStartAddress, $iStartAddress + $iQuantity - 1);

        // retourne un tableau avec comme clés les adresses
        return array_combine($aAddress, $aDatasBits);
    }


    /**
     * écrit une plage de bits ou de sorties TOR
     *
     * @param int $iAddress adresse du bit ou de la sortie physique
     * @param bool $bState false pour désactiver, true pour activer
     */
    public function WriteSingleCoil($iAddress, $bState) {
        PHPModbusTcpDebug::writeLogs(func_get_args(), 1);

        $state = (false == $bState)? 0 : 0xFF00; // 0xFF00 pour mettre un bit ou une sortie à 0

        if (!ctype_digit($iAddress))
            throw new Exception('paramètre incorrect : l\'adresse n\'est pas un entier');

        $this->aFrameToSend = array (0=>chr(0), chr(0), chr(0), chr(0), chr(0), chr(6), 6=>chr($this->iDeviceId), chr(5));

        list($this->aFrameToSend[9], $this->aFrameToSend[8]) = Convert::WordToBytes((int)$iAddress);
        list($this->aFrameToSend[11], $this->aFrameToSend[10]) = Convert::WordToBytes($state);

        $this->writeSocket($this->aFrameToSend);

        $sBuf = $this->readSocket();

        /**
         * contrôle réponse
         * la trame renvoyée doit être identique à la trame émise
         */
        if ($sBuf != implode('', $this->aFrameToSend)) {
            // code de l'exception renvoyée
            echo $sBuf."\r\n".implode('',$this->aFrameToSend)."\r\n";
            throw new Exception($this->aMBException[(ord($sBuf[7]) - 128)]." Address : ".$iStartAddress." State : ".$bState);
        }


        // explose la chaîne reçue en un tableau de Bytes
        $this->aReceivedFrame = preg_split('``', $sBuf, NULL, PREG_SPLIT_NO_EMPTY);

        return array(true);
    }



    public function writeMultipleCoils($iStartAddress, $sStates) {
        if (!is_numeric($iStartAddress) || !is_string($sStates))
            throw new Exception('paramètres incorrects : l\'adresse de début ou le nombre de bits à écrire sont incorrects');

        if (strlen($sStates) > 1968) $sStates = 1968; // nb d'écritures max dans le standard modbus

        if (0 == preg_match('`^[0|1]*$`', $sStates)) throw new Exception('"'.$sStates.'" n\'est pas une chaîne binaire');


        $this->aFrameToSend = array (0=>chr(0), chr(0), chr(0), chr(0), chr(0), chr(9), 6=>chr($this->iDeviceId), chr(15));


        $aBytesStates = Convert::bitsToBytes($sStates);
        $aBytesStatesLength = count($aBytesStates); // nombre de Bytes envoyés

        // adresse de début
        //list($this->aFrameToSend[9], $this->aFrameToSend[8]) = Convert::WordToBytes((int)$iStartAddress);
        list($this->aFrameToSend[9], $this->aFrameToSend[8]) = Convert::WordToBytes($iStartAddress);


        // quantité de bits à écrire
        list($this->aFrameToSend[11], $this->aFrameToSend[10]) = Convert::WordToBytes(strlen($sStates));


        /**
         * nombre de Bytes
         */
        $this->aFrameToSend[12] = chr($aBytesStatesLength);


        /**
         * ajoute les données de la chaîne binaire
         *
         */
        for ($i = 0; $i < $aBytesStatesLength; $i++) {
            $this->aFrameToSend[$i+13] = $aBytesStates[$i];
        }

        $this->writeSocket($this->aFrameToSend);

        $sBuf = $this->readSocket();

        /**
         * contrôle réponse
         * la trame renvoyée doit être identique à la trame émise
         */
        if ($sBuf[7] != $this->aFrameToSend[7]) {
            // code de l'exception renvoyée
            echo $sBuf."\r\n".implode('',$this->aFrameToSend)."\r\n";
            throw new Exception($this->aMBException[(ord($sBuf[7]) - 128)]." Address : ".$iStartAddress." State : ".$bState);
        }


        // explose la chaîne reçue en un tableau de Bytes
        $this->aReceivedFrame = preg_split('``', $sBuf, NULL, PREG_SPLIT_NO_EMPTY);

        return array(true);

    }


    /**
     *
     * MB function 03
     */
    public function readHoldingRegisters($iStartAddress, $iQuantity) {

        if (!is_numeric($iStartAddress) || !is_numeric($iQuantity))
            throw new Exception('paramètres incorrects : l\'adresse de début ou le nombre de registres à lire ne sont pas des entiers');

        if ($iQuantity > 125) $iQuantity = 125; // nb de registres max lisible dans le standard modbus
        if ($iQuantity == 0) $iQuantity = 1; // quantité d'adresses min à lire

        /*
         * Définition de la trame Modbus à envoyer
        */
        $this->aFrameToSend = array (0=>chr(0), chr(0), chr(0), chr(0), chr(0), 5=>chr(6), 6=>chr($this->iDeviceId), 7=>chr(3)) ;

        /*
         * adding datas (big endian) :
         * $obuf[8], $obuf[9] : starting address
         * $obuf[10], $obuf[11] : quantity of registers to read
         *
        */
        list($this->aFrameToSend[9], $this->aFrameToSend[8]) = Convert::WordToBytes((int)$iStartAddress);
        list($this->aFrameToSend[11], $this->aFrameToSend[10]) = Convert::WordToBytes((int)$iQuantity);

        $this->writeSocket($this->aFrameToSend);

        $sBuf = $this->readSocket();


        //debug
        //        echo $abuf.'<br />';
        //
        //        for ($i=0; $i < strlen($abuf); $i++) {
        //            echo $i.'=>'.ord($abuf[$i]).'<br />';
        //        }

        /**
         * contrôle réponse
         * si le 8eme bit reçu (code fonction) n'est pas égal au 8eme envoyé il s'agit d'une exception MB (function code + 0x80)
         */
        if ($sBuf[7] != $this->aFrameToSend[7]) {
            // code de l'exception renvoyée
            throw new Exception($this->aMBException[(ord($sBuf[7]) - 128)]." Start Adress : ".$iStartAddress." Quantity of registers : ".$iQuantity);
        }


        //debug
        // echo 'nb de Bytes reçus : '.ord($abuf[8])."\r\n";

        // explose la chaîne reçue en un tableau de Bytes
        $this->aReceivedFrame = preg_split('``', $sBuf, NULL, PREG_SPLIT_NO_EMPTY);

        //        print_r($this->aReceivedFrame);
        //

        // ne conserve que les datas (== Byte 9 et suivants)
        $aDatasBytes = array_slice($this->aReceivedFrame, 9, ord($sBuf[8]));

        // converti chaque valeur (Byte) du tableau en format binaire
        $aDatasBits = array_map("Convert::ByteToBits", $aDatasBytes);

        // explose chaque bit de chaque octet dans un tableau unique
        $aDatasBits = preg_split('``', implode('', $aDatasBits), NULL, PREG_SPLIT_NO_EMPTY);

        // limite le nombre de bits demandés (le protocole MB n'envoyant que des octets complets)
        $aDatasBits = array_splice($aDatasBits, 0, $iQuantity);

        // crée le tableau des adresses
        $aAddress = range($iStartAddress, $iStartAddress + $iQuantity - 1);

        // retourne un tableau avec comme clés les adresses
        return array_combine($aAddress, $aDatasBits);

    }


    /**
     *
     * MB function 04
     */
    public function readInputRegisters($iStartAddress, $iQuantity) {

        if (!is_numeric($iStartAddress) || !is_numeric($iQuantity))
            throw new Exception('paramètres incorrects : l\'adresse de début ou le nombre de registres à lire ne sont pas des entiers');

        if ($iQuantity > 125) $iQuantity = 125; // nb de registres max lisible dans le standard modbus
        if ($iQuantity == 0) $iQuantity = 1; // quantité d'adresses min à lire

        /*
         * Définition de la trame Modbus à envoyer
        */
        //$this->aFrameToSend = array (0=>chr(0), chr(0), chr(0), chr(0), chr(0), 5=>chr(6), 6=>chr($this->iUnitIdentifier), 7=>chr(3)) ;

        $this->aFrameToSend = array (0=>chr(0), chr(0), chr(0), chr(0), chr(0), 5=>chr(6), 6=>chr($this->iDeviceId), 7=>chr(4)) ;

        /*
         * adding datas (big endian) :
         * $obuf[8], $obuf[9] : starting address
         * $obuf[10], $obuf[11] : quantity of registers to read
         *
        */
        list($this->aFrameToSend[9], $this->aFrameToSend[8]) = Convert::WordToBytes((int)$iStartAddress);
        list($this->aFrameToSend[11], $this->aFrameToSend[10]) = Convert::WordToBytes((int)$iQuantity);

        $this->writeSocket($this->aFrameToSend);

        $sBuf = $this->readSocket();


        /**
         * contrôle réponse
         * si le 8eme bit reçu (code fonction) n'est pas égal au 8eme envoyé il s'agit d'une exception MB (function code + 0x80)
         */
        if ($sBuf[7] != $this->aFrameToSend[7]) {
            // code de l'exception renvoyée
            throw new Exception($this->aMBException[(ord($sBuf[7]) - 128)]." Start Adress : ".$iStartAddress." Quantity of registers : ".$iQuantity);
        }


        //debug
        // echo 'nb de Bytes reçus : '.ord($abuf[8])."\r\n";

        // explose la chaîne reçue en un tableau de Bytes
        $this->aReceivedFrame = preg_split('``', $sBuf, NULL, PREG_SPLIT_NO_EMPTY);

        //        print_r($this->aReceivedFrame);
        //

        // ne conserve que les datas (== Byte 9 et suivants)
        $aDatasBytes = array_slice($this->aReceivedFrame, 9, ord($sBuf[8]));

        // converti chaque valeur (Byte) du tableau en format binaire
        $aDatasBits = array_map("Convert::ByteToBits", $aDatasBytes);

        // explose chaque bit de chaque octet dans un tableau unique
        $aDatasBits = preg_split('``', implode('', $aDatasBits), NULL, PREG_SPLIT_NO_EMPTY);

        // limite le nombre de bits demandés (le protocole MB n'envoyant que des octets complets)
        $aDatasBits = array_splice($aDatasBits, 0, $iQuantity);

        // crée le tableau des adresses
        $aAddress = range($iStartAddress, $iStartAddress + $iQuantity - 1);

        // retourne un tableau avec comme clés les adresses
        return array_combine($aAddress, $aDatasBits);
    }


    /**
     *
     * MB function 06
     */
    public function writeSingleRegister() {


    }

    /**
     *
     * MB function 16
     */
    public function writeMultipleRegisters() {

    }

    /**
     *
     * MB function 23
     */
    public function readWriteMultipleRegisters() {


    }

    /**
     *
     * MB function 22
     */
    public function maskWriteRegister() {

    }

    /**
     *
     * MB function 24
     */
    public function readFIFOQueue() {

    }


    /**
     *
     * MB function 20
     */
    public function readFileRecord() {


    }


    /**
     *
     * MB function 21
     */
    public function writeFileRecord() {

    }

    /**
     *
     * MB function 43
     */
    public function readDeviceIdentification() {


    }


    /**
     * ferme la socket
     *
     * @return bool
     */
    public function socketClose() {
        //return true;

        if($this->rSocket) {
            socket_shutdown($this->rSocket, 1); // ferme l'écriture sur la socket
            usleep(400);// attends éventuellement une réponse de l'équipement
            socket_shutdown($this->rSocket, 0);// ferme la lecture de la socket
            socket_close($this->rSocket); // ferme la connexion
            //echo 'socket fermée<hr />';
        }
    }




    /**
     * destructeur
     */
    public function  __destruct() {
        $this->socketClose();
    }

}
?>
