<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
    <head>
        <!-- version 0000 00/00/0000 00:00:00 -->
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="Content-Language" content="fr" />

        <meta name="Description" content="Utilitaire OptoMemoryMapAddress pour Opto22 SNAP-UP1-ADS" />
        <meta name="author" content="Kohntark" />
        <meta name="copyright" content="Kohntark" />

        <link type="text/css" href="css/main.css" rel="stylesheet" media="screen" />

        <script type="text/javascript" src="js/jquery-1.4.1.min.js"></script>
        <script type="text/javascript" src="js/opto22MemoryMapAddress.js"></script>
        <title>Utilitaire de résolution d'addresses pour OPTO 22 SNAP-UP1-ADS</title>
    </head>

    <body>


    <h1 id="opto22MemoryMapAddress" style="margin-top:40px;">opto22MemoryMapAddress</h1>

    <div style="" class="mapAdress">
        <img src="http://php.modbus.kohntark.fr/tools/img/leftBanner.png" style="float:left;border-style:none;margin:0px 4px 0px 0px;" alt="" />

        <div class="note" style="float:left;margin-top:3px;">
            Opto 22 Memory Map Address (Valid range F000 0000 to F1EB FFFE)
        </div>

        <div class="convertText" style="float:left;margin-top:3px;">
            &nbsp;Please enter a valid hexa address<br />
            <span style="margin-left:60px;">Address:</span>
            <input name="memMapAddr" id="memMapAddr" type="text" class="hexAddrInput" value="F0D81000" />
        </div>


        <div>
            <img src="http://php.modbus.kohntark.fr/tools/img/links_1.png" alt="" />
        </div>


        <div style="float:left;">
            <table >
                <tr>
                    <td class="unitBinAddr" id="memMapAddrRegisterAddrBin_0"></td>
                    <td class="unitBinAddr" id="memMapAddrRegisterAddrBin_1"></td>
                    <td class="unitBinAddr" id="memMapAddrRegisterAddrBin_2"></td>
                    <td class="unitBinAddr endByte" id="memMapAddrRegisterAddrBin_3"></td>
                    <td class="unitBinAddr" id="memMapAddrRegisterAddrBin_4"></td>
                    <td class="unitBinAddr" id="memMapAddrRegisterAddrBin_5"></td>
                    <td class="unitBinAddr" id="memMapAddrRegisterAddrBin_6"></td>
                    <td class="unitBinAddr endByte unitBinAddrSelected" id="memMapAddrRegisterAddrBin_7"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_8"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_9"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_10"></td>
                    <td class="unitBinAddr endByte unitBinAddrSelected" id="memMapAddrRegisterAddrBin_11"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_12"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_13"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_14"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_15"></td>
                </tr>
            </table>
        </div>

        <div>
            <table style="float:left;margin-left:11px;">
                <tr>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_16"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_17"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_18"></td>
                    <td class="unitBinAddr endByte unitBinAddrSelected" id="memMapAddrRegisterAddrBin_19"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_20"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_21"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_22"></td>
                    <td class="unitBinAddr endByte unitBinAddrSelected" id="memMapAddrRegisterAddrBin_23"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_24"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_25"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_26"></td>
                    <td class="unitBinAddr endByte unitBinAddrSelected" id="memMapAddrRegisterAddrBin_27"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_28"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_29"></td>
                    <td class="unitBinAddr unitBinAddrSelected" id="memMapAddrRegisterAddrBin_30"></td>
                    <td class="unitBinAddr" id="memMapAddrRegisterAddrBin_31"></td>
                </tr>
            </table>

        </div>

        <div style="margin-left:108px;">
            <img src="http://php.modbus.kohntark.fr/tools/img/links_2.png" alt="" />
        </div>

        <div style="margin-left:140px;">
            <input type="text" style="width:30px;" class="converted" id="mapAddrUnitId8Bits" disabled="disabled" />
            <input type="text" style="margin-left: 111px;" class="converted" id="mapAddrUnitId16Bits" disabled="disabled" />
        </div>

        <div style="margin:16px 0px 0px 101px">
            <img src="http://php.modbus.kohntark.fr/tools/img/adding.png" alt="" />
        </div>

        <div class="" style="margin-top:8px;">
            <span style="margin-right:37px">Modbus:</span>
            <span>Unit ID</span>
            <input type="text" style="width:30px;" class="converted" name="mapAddrUnitId8BitsPlus2" disabled="disabled" />
            <span style="margin-left: 23px;">Register Address</span>
            <input type="text" class="converted" id="mapAddrUnitId16BitsPlus1" disabled="disabled" />

        </div>

        <div style="margin:0px 0px 0px 3px">
            <img src="http://php.modbus.kohntark.fr/tools/img/adding_1.png" alt="" />
        </div>

        <div class="" style="margin-top:1px;">
            <span style="margin-right:37px">Modbus:</span>
            <span>Unit ID</span>
            <input type="text" style="width:30px;" class="converted" name="mapAddrUnitId8BitsPlus2" disabled="disabled" />
            <span style="margin-left: 23px;">Register Number</span>
            <input type="text" class="converted" id="mapAddrUnitId16BitsPlus2" disabled="disabled" />

        </div>


        <div style="clear:both;margin-top:0px;" class="note">
            Note:Modbus Register Numbers start at 1, but the corresponding Register Addresses start at 0.
            For example, Register Number 2050 is at Register Addresse 2049.
        </div>

    </div>

    </body>
</html>

