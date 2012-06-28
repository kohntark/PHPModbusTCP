<?php
/**
 * PHPModbusTcpDebug
 *
 * @author Kohntark <kohntark@kohntark.fr> <kohntark@kohntark.fr>
 */
class PHPModbusTcpDebug extends PHPModbusTcp {

    /**
     * mode pour le debuggage
     */
    const DEBUG = true;
    const MAX_LOG_FILE_SIZE = 1024; // taille maxi du fichier de logs
    const LOGS_FILE = '../logs/PHPModbusTcp.log'; // chemin du fichier de logs

    /**#@+
     *
     * @access public
     */


    /**#@-*/

    /**#@+
     *
     * @access protected
     */

    /**#@-*/

    /**#@+
     *
     * @access private
     */

    /**#@-*/


    public function  __construct($sDeviceIp, $iDevicePort, $iSndTimeout, $iRcvTimeout) {
        self::writeLogs(array('IP'=>$sDeviceIp, 'Port'=>$iDevicePort, 'timeout send'=>$iSndTimeout, 'timeout receive'=>$iRcvTimeout), 1);
        parent::__construct($sDeviceIp, $iDevicePort, $iSndTimeout, $iRcvTimeout);
    }

    public function getFrameToSend() {
        $aFrameToSend = $this->aFrameToSend;
        array_walk($aFrameToSend, function(&$v) {
                    $v = ord($v).'<br />0x'.bin2hex($v);
                });

        return $aFrameToSend;
    }


    public function getReceivedFrame() {
        $aReceivedFrame = $this->aReceivedFrame;
        //echo $aReceivedFrame.'_____'$this->aReceivedFrame;
        array_walk($aReceivedFrame, function(&$v) {
                    $v = ord($v).'<br />0x'.bin2hex($v);
                });

        return $aReceivedFrame;
    }


    public static function writeLogs($mMessage, $bWriteKeys = false) {
        if (false === self::DEBUG) return false;
        $sLog = '';
        //if (is_array($message)) $message = implode("\n", $message);


        $aMessage = (!is_array($mMessage))? explode("\n", $mMessage) : $mMessage;

        foreach($aMessage as $k=>$v) {
            $sLog.= date('d/m/Y H:i:s').' : ';
            $sLog.= ($bWriteKeys)? $k.' => ': '';
            $sLog.= trim($v)."\r\n";
        }


        if (@filesize(self::LOGS_FILE) < self::MAX_LOG_FILE_SIZE) { // supprime les logs si taille fichier supérieure à MAX_LOG_FILE_SIZE
            file_put_contents(self::LOGS_FILE, $sLog, FILE_APPEND);
        } else {
            file_put_contents(self::LOGS_FILE, $sLog);
        }
        clearstatcache();
    }





}
?>
