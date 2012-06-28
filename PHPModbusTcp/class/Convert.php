<?php
/**
 * Convert
 *
 * @author Kohntark <kohntark@kohntark.fr> <kohntark@kohntark.fr>
 */

class Convert {

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



    /**
     * converti un décimal de 16 bits max en 2 Bytes
     *
     * @param int $iWord
     * @return array
     */
    public static function WordToBytes($iWord = 0) {
        if ($iWord > 65535) $iWord = 65535; // limite à la valeur max de 16 bits
        return (array(chr($iWord % 256), chr(($iWord - $iWord % 256) / 256)));
    }

    /**
     * converti un Byte en une string binaire inverse
     *
     * @param int $iByte1
     * @return string
     */
    public static function ByteToBits($iByte1 = 0) {
        return(strrev(sprintf("%08d", decbin(ord($iByte1)))));
    }


    /**
     * converti 2 Bytes en un mot décimal
     */
    public static function BytesToWord($byte1 = 0, $byte2 = 0 ) {
        return(ord($byte1) * 256 + ord($byte2));
    }


    /**
     * converti une chaîne de format binaire en Bytes
     *
     * @param string $sBits
     * @return array
     */
    public static function bitsToBytes($sBits) {
        // complète la string pour qu'elle ne constitue que des octets "complets" (8, 16, 32, ...)
        $sBits = $sBits.str_repeat(0, (16 - (strlen($sBits)%16)));

        /* scinde la chaîne en octets
         * 
         * les bits sont inversés pour permettre une entrée "humainement logique", c'est à 
         * dire une lecture de gauche à droite sans se préoccuper de la notion d'octet ou de mot, par ex :
         * (en supposant un début d'adresse à 10)
         * entrer 1101 signifie que l'on souhaite mettre à 1 le bit 10, à 1 le 11, à 0 le 12 et à 1 le 13
         * ce qui se traduit pour le protocole MB par :
         * 0000 1101 0000 0000
         * (les espaces ne sont là que pour la compréhension)
         * 
        */
        $aBinaryBytes = str_split(strrev($sBits), 8);

        array_walk($aBinaryBytes, function (&$v) {
                    $v = chr(base_convert($v, 2, 10));
                }
        );

        // ordonne le tableau en big endian
        return array_reverse($aBinaryBytes);
    }



    public static function BytesToFloat( $byte1, $byte2, $byte3, $byte4 ) {
        // Conversion selon presentation Standard IEEE 754

        define ("DBL_MAX", 99999999999999999);

        $src = ( ($byte1 & 0x000000FF) << 24) + (($byte2 & 0x000000FF) << 16) + (($byte3 & 0x000000FF) << 8) + (($byte4 & 0x000000FF) );

        $s = (bool)($src >> 31);
        $e = ($src & 0x7F800000) >> 23;
        $f = ($src & 0x007FFFFF);

        //var_dump($s);
        //echo "<br>";
        //var_dump($e);
        //echo "<br>";
        //var_dump($f);
        //echo "<br>";

        if ($e == 255 && $f != 0) {
            /* NaN - Not a number */
            $value = DBL_MAX;
        } elseif ($e == 255 && $f == 0 && $s) {
            /* Negative infinity */
            $value = -DBL_MAX;
        } elseif ($e == 255 && $f == 0 && !$s) {
            /* Positive infinity */
            $value = DBL_MAX;
        } elseif ($e > 0 && $e < 255) {
            /* Normal number */
            $f += 0x00800000;
            if ($s) $f = -$f;
            $value = $f * pow(2, $e - 127 - 23);
        } elseif ($e == 0 && $f != 0) {
            /* Denormal number */
            if ($s) $f = -$f;
            $value = $f * pow(2, $e - 126 - 23);
        } elseif ($e == 0 && $f == 0 && $s) {
            /* Negative zero */
            $value = 0;
        } elseif ($e == 0 && $f == 0 && !$s) {
            /* Positive zero */
            $value = 0;
        } else {
            /* Never happens */
        }

        return $value;
    }


}
?>
