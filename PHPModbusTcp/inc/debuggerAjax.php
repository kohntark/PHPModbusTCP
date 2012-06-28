<?php


function __autoload($sClass_name) {
    require_once '../class/'.$sClass_name.'.php';
}


//if (false === $aParams = @parse_ini_file('../conf/config.ini', true))
//    die(__FILE__.':'.__LINE__.' : Lecture du fichier de configuration impossible');

//print_r($_POST);
//exit;



$tcp = new PHPModbusTcpDebug($_POST['deviceAddr'], $_POST['devicePort'], $_POST['iSndTimeout'], $_POST['iRcvTimeout']);

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
        '04' => 'readInputRegister',
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

$aRcv = $tcp->$funct((int)$_POST['startAddress'], (int)$_POST['addressQuantity']);


$js = 'rcv = new Array;';
foreach ($aRcv as $k=>$v) {
    $js.= 'rcv['.$k.']='.$v.';';
}


echo $js;
?>
