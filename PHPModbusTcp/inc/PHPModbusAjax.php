<?php
header("Content-Type: text/xml");


function __autoload($sClass_name) {
    require_once '../class/'.$sClass_name.'.php';
}


//if (false === $aParams = @parse_ini_file('../conf/config.ini', true))
//    die(__FILE__.':'.__LINE__.' : Lecture du fichier de configuration impossible');

//print_r($_POST);
//exit;



$tcp = new PHPModbusTcpDebug($_POST['deviceAddr'], $_POST['devicePort'], $_POST['deviceId'], $_POST['iSndTimeout'], $_POST['iRcvTimeout']);

//$tcp->setDeviceProperties($_POST['deviceAddr'], $_POST['devicePort']);
//echo $tcp->readDiscreteInputs(0, 4);
//$tcp->connect();

/**
 * tableau des fonctions Modbus implémentées
 * sous la forme :
 * k=>v
 * k : code de la fonction (dec)
 * v : nom de la méthode PHPModbusTcp correspondante
 */
$aMBFunctions = array(
        '02' => 'readDiscreteInputs',
        '01' => 'readCoils',
        '05' => 'writeSingleCoil',
        '15' => 'writeMultipleCoils',
        '04' => 'readInputRegisters',
        '03' => 'readHoldingRegisters',
        '06' => 'writeSingleRegister',
        '16' => 'writeMultipleRegisters',
        '23' => 'readWriteMultipleRegisters',
        '22' => 'maskWriteRegister',
        '24' => 'readFifoQueue',
        '20' => 'readFileRecord',
        '21' => 'writeFileRecord',
        '43' => 'readDeviceIdentification'
);

// retrait du nom de la méthode en fonction du code fonction Modbus reçu
$funct = $aMBFunctions[$_POST['MBfunction']];
$e = '';

try {
    //$aRcv = $tcp->$funct($_POST['startAddress'], $_POST['addressQuantity']);
    $fTimeStart = microtime(true);
    $aRcv = $tcp->$funct($_POST['arg0'], $_POST['arg1']);
    $fTime = round(microtime(true) - $fTimeStart, 5);

} catch (Exception $e) {
    $e = nl2br($e, true);
}



$xml = new DOMDocument('1.0', 'utf-8');
$xml->formatOutput = false;
$root = $xml->createElement('PHPModbusTcp');
$root = $xml->appendChild($root);

$xmlFunct = $xml->createElement('functionCode', $_POST['MBfunction']);
$root->appendChild($xmlFunct);

$xmlError = $xml->createElement('exception', $e);
$root->appendChild($xmlError);

/**
 * inclusion dans le XML de la trame envoyée
 */
foreach ($tcp->getFrameToSend() as $k=>$value) {
    $sXmlFrames = $xml->createElement('frameSent', $value);
    $sXmlFrames->setAttribute('id', $k);
    $root->appendChild($sXmlFrames);
}

/**
 * inclusion dans le XML de la trame reçue
 */
foreach ($tcp->getReceivedFrame() as $k=>$value) {
    $sXmlFrames = $xml->createElement('receivedFrame', $value);
    $sXmlFrames->setAttribute('id', $k);
    $root->appendChild($sXmlFrames);
}


/**
 * inclusion dans le XML des données reçues
 */
if(empty($e)) {
    foreach($aRcv as $k=>$value) {
        $sXmlDatas = $xml->createElement('address', $value);
        $sXmlDatas->setAttribute('id', $k);
        $root->appendChild($sXmlDatas);

    }
}

$xmlExecutionTime = $xml->createElement('executionTime', $fTime);
$root->appendChild($xmlExecutionTime);

echo $xml->saveXML();


exit;

$js = 'rcv = new Array;';
foreach ($aRcv as $k=>$v) {
    $js.= 'rcv['.$k.']='.$v.';';
}

$jsFrames = 'frames = new Array();';
foreach ($tcp->getFrameToSend() as $k=>$v) {

    $jsFrames.= 'frames['.$k.']='.$v.';';
}


echo $js.$jsFrames;
?>
