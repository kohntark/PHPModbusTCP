<?php
/**
 * 
 */

set_time_limit(10);
ob_implicit_flush(true);

ini_set('display_errors', 1);
error_reporting(-1);


?>


<script type="text/javascript">

    $(document).ready(function() {
        $('#B_submitReq').click(function(){

            /**
             * contrôle validité de l'IP
             */
            octet = '(?:25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]|[0-9])';
            ip    = '(?:' + octet + '\\.){3}' + octet;
            ipReg = new RegExp('^'+ip+'$');

            if (false == ipReg.test($('#I_deviceAddr').val())) {
                $('#I_deviceAddr').focus();
                return false;
            }

            /**
             * contrôle port de l'équipement
             */
            portReg = new RegExp('^\\d{3,5}$');
            if (false == portReg.test($('#I_devicePort').val())) {
                $('#I_devicePort').focus();
                return false;
            }

            /**
             * contrôle ID équipement
             */
            idReg = new RegExp('^\\d{1,3}$');
            if (false == idReg.test($('#I_deviceId').val())) {
                $('#I_deviceId').focus();
                return false;
            }

            //alert(isNaN($('#I_devicePort').val()));

            datas = "deviceAddr="+$('#I_deviceAddr').val()+"&devicePort="+$('#I_devicePort').val()+"&MBfunction="+$('#S_MBfunction').val();
            datas+= "&deviceId="+$('#I_deviceId').val();
            datas+= "&arg0="+$('#I_arg0').val()+"&arg1="+$('#I_arg1').val();
            datas+= "&iSndTimeout="+$('#iSndTimeout').val()+"&iRcvTimeout="+$('#iRcvTimeout').val();

            $.ajax({
                type: "POST",
                url: "inc/PHPModbusAjax.php",
                data: datas,
                dataType: "xml",
                success: function(xml){

                    // contrôle si une exception n'a pas été levée
                    if ($(xml).find('exception').text() !== '') {

                        $('#D_error').html('<span style="text-decoration:underline">Erreur</span> :<br /><p>'+$(xml).find('exception').text()+'</p>').addClass('error');
                        return;
                    } else $('#D_error').html('');

                    // temps d'exécution total
                    executionTime = $(xml).find('executionTime').text();

                    // nb total de Bytes de la trame
                    totalFrameLength = $(xml).find('frameSent').length;

                    // nb de Bytes de données
                    dataFrameLength = $(xml).find('frameSent').length - 7;

                    frameSentTable = '<p>(little endian, décimal)</p>';
                    frameSentTable+= '<table><thead><tr><th colspan="'+totalFrameLength+'">Modbus TCP/IP ADU</th></tr>';
                    frameSentTable+= '<tr class="th_1"><td colspan="7">MBAP Header</td>';
                    frameSentTable+= '<td>Function Code</td>';
                    frameSentTable+= '<td colspan="'+dataFrameLength+'">Data</td></tr>';


                    //alert(typeof($(xml).find('frameSent')));
                    //alert($(xml).find('frameSent').length);

                    frameSentTableValue = '<tr>';
                    frameSentTableAddr = '<tr>';

                    frameSentTable+= '<tr><td colspan="2" style="width:80px;">Transaction Identifier</td>';
                    frameSentTable+= '<td colspan="2" style="width:75px;">Protocol Identifier</td>';
                    frameSentTable+= '<td colspan="2" style="width:55px;">Length</td>';
                    frameSentTable+= '<td style="width:60px;">Unit Identifier</td>';
                    frameSentTable+= '<td style="width:30px;">function code</td>';


                    $(xml).find('frameSent').each(function(index) {
                        if(index > 7) {
                            frameSentTable+= '<td style="width:50px">'+this.getAttribute('id')+'</td>';
                        }
                    });

                    frameSentTable+= '</tr></thead>';


                    frameSentTable+= '<tfoot><tr><td colspan="'+totalFrameLength+'"></td></tr></tfoot>';

                    $(xml).find('frameSent').each(function(index) {
                        //  alert(index+"=>"+$(this).text());

                        if (index <= totalFrameLength - dataFrameLength) { // MBAP
                            //frameSentTableAddr+= '<td>'+index+'</td>';
                            frameSentTable+= '<td>'+$(this).text()+'</td>';

                        } else { // data
                            frameSentTable+= '<td>'+$(this).text()+'</td>';
                        }

                    });

                    $('#D_frameSent').html(frameSentTable);





                    /******************************************************************/

                    // nb total de Bytes de la trame
                    totalFrameLength = $(xml).find('receivedFrame').length;

                    // nb de Bytes de données
                    dataFrameLength = $(xml).find('receivedFrame').length - 7;

                    receivedFrameTable = '<p>(little endian, décimal)</p>';
                    receivedFrameTable+= '<table><thead><tr><th colspan="'+totalFrameLength+'">Modbus TCP/IP ADU</th></tr>';
                    receivedFrameTable+= '<tr class="th_1"><td colspan="7">MBAP Header</td>';
                    receivedFrameTable+= '<td>Function Code</td>';
                    receivedFrameTable+= '<td colspan="'+dataFrameLength+'">Data</td></tr>';


                    //alert(typeof($(xml).find('frameSent')));
                    //alert($(xml).find('frameSent').length);

                    receivedFrameTableValue = '<tr>';
                    receivedFrameTableAddr = '<tr>';

                    receivedFrameTable+= '<tr><td colspan="2">Transaction Identifier</td>';
                    receivedFrameTable+= '<td colspan="2">Protocol Identifier</td>';
                    receivedFrameTable+= '<td colspan="2">Length</td>';
                    receivedFrameTable+= '<td>Unit Identifier</td>';
                    receivedFrameTable+= '<td>function code</td>';


                    $(xml).find('receivedFrame').each(function(index) {
                        if(index > 7) {
                            receivedFrameTable+= '<td>'+this.getAttribute('id')+'</td>';
                        }
                    });

                    receivedFrameTable+= '</tr></thead>';

                    receivedFrameTable+= '<tfoot><tr><td colspan="'+totalFrameLength+'"></td></tr></tfoot>';

                    $(xml).find('receivedFrame').each(function(index) {
                        //  alert(index+"=>"+$(this).text());

                        if (index <= totalFrameLength - dataFrameLength) { // MBAP
                            //frameSentTableAddr+= '<td>'+index+'</td>';
                            receivedFrameTable+= '<td>'+$(this).text()+'</td>';

                        } else { // data
                            receivedFrameTable+= '<td>'+$(this).text()+'</td>';
                        }

                    });


                    $('#D_frameReceived').html(receivedFrameTable);




                    /************************************************************/


                    data = '<table><thead><tr><th>adress</th><th>value</th></tr></thead>';
                    data+= '<tbody>';

                    $(xml).find('address').each(function() {
                        data+= '<tr><td>'+this.getAttribute('id')+'</td>';
                        data+= '<td>'+$(this).text()+'</td></tr>';

                    });

                    data+= '</tbody>';
                    data+= '<tfoot><tr><td colspan="'+totalFrameLength+'">temps d\'exécution : '+executionTime+' sec</td></tr></tfoot>';
                    data+= '</table>';
                    $('#D_rcvDatas').html(data);


                }

            });
        });


        $('#S_MBfunction').change(function(){
            //            alert($('#S_MBfunction').val());
            $('#D_arg0').show();
            $('#D_arg1').show();

            aArgsDef = new Array();
            aArgsDef['01'] = [['Adresse de début', 'Nombre d\'adresses à lire'], 'text'];
            aArgsDef['02'] = [['Adresse de début', 'Nombre d\'adresses à lire'], 'text'];
            aArgsDef['03'] = [['Adresse de début', 'Nombre de registres à lire'], 'text'];
            aArgsDef['04'] = [['Adresse de début', 'Nombre de registres à lire'], 'text'];
            aArgsDef['05'] = [['Adresse', 'on/off'], 'select', [[0, 'off'], [1, 'on']]];
            aArgsDef['06'] = [['non implémenté', 'non implémenté'], 'text'];
            aArgsDef['15'] = [['Adresse de début', 'Nombre d\'adresses à écrire'], 'text'];;
            aArgsDef['16'] = [['non implémenté', 'non implémenté'], 'text'];
            aArgsDef['20'] = [['non implémenté', 'non implémenté'], 'text'];
            aArgsDef['21'] = [['non implémenté', 'non implémenté'], 'text'];
            aArgsDef['22'] = [['non implémenté', 'non implémenté'], 'text'];
            aArgsDef['23'] = [['non implémenté', 'non implémenté'], 'text'];
            aArgsDef['24'] = [['non implémenté', 'non implémenté'], 'text'];
            aArgsDef['43'] = [['non implémenté', 'non implémenté'], 'text'];

            //aArgsDef['01']['arg1'] = 'Nombre d\'adresses à lire';
            //            aArgsDef['02']['arg0'] = 'Adresse de début';
            //            aArgsDef['02']['arg1'] = 'Nombre d\'adresses à lire';

            $('#L_iarg0').text(aArgsDef[$('#S_MBfunction').val()][0][0]);
            $('#L_iarg1').text(aArgsDef[$('#S_MBfunction').val()][0][1]);

            //alert(aArgsDef[$('#S_MBfunction').val()][3]);
            args = aArgsDef[$('#S_MBfunction').val()];

            if (args[1] == 'text') {
                txt = '<input type="text" id="I_arg1" value="4" />';
                $('#I_arg1').replaceWith(txt);

            } else if (args[1] == 'select') {
                options = args[2];
                sel = '<select id="I_arg1">';
                for(a in options) {
                    sel+= '<option value="'+options[a][0]+'">'+options[a][1]+'</option>';
                }
                sel+= '</select>';
                //$(sel).appendTo('#D_arg1');
                $('#I_arg1').replaceWith(sel);
            }

            //            z = aArgsDef[$('#S_MBfunction').val()];
            //            z = aArgsDef['05'][2];
            //            for (k in z) {
            //                alert('____'+k+'=>'+z[k][1]);
            //            }

            /*
             * for (key in haystack) {
                if (haystack[key] == needle) {
                    return true;
                }
            }
             */
            //alert(aArgsDef[$('#S_MBfunction').val()][0]);
        });



        $('#S_MBfunction').change();
    });

</script>


<div style="float:left;width:190px;height: 1500px;padding: 5px 0px 0px 0px;">
    <fieldset style="height: 350px;">
        <legend>Equipement distant</legend>
        <div style="margin-top: 15px;">
            <label for="I_deviceAddr">Adresse IP</label>
            <input type="text" id="I_deviceAddr" style="width: 125px;" />
        </div>
        <div>
            <label for="I_devicePort">Port</label>
            <input type="text" id="I_devicePort"  style="width:45px;"/>
        </div>
        <div>
            <label for="I_deviceId">ID</label>
            <input type="text" id="I_deviceId" name="I_deviceId" style="width: 30px;" value="255"/>
        </div>
        <div>
            <label for="iSndTimeout">Timeout envoi (ms)</label>
            <input type="text" id="iSndTimeout" value="300" />

            <label for="iRcvTimeout">Timeout reception (ms)</label>
            <input type="text" id="iRcvTimeout" value="300" />

        </div>
    </fieldset>
</div>

<div style="padding: 5px 0px 0px 0px;" >
    <fieldset>
        <legend>Paramètres</legend>
        <div style="float:left;">


            <div style="float:left;">
                <label for="S_MBfunction">Fonction :</label>
                <select name="S_MBfunction" id="S_MBfunction">
                    <!--<option value="" selected="selected"> --- </option>-->
                    <option value="02">02 - lire des entrées TOR</option>
                    <option value="01">01 - lire des bits internes ou des sorties TOR</option>
                    <option value="05">05 - écrire un bit interne ou une sortie TOR</option>
                    <option value="15">15 - écrire plusieurs bits internes ou plusieurs sorties TOR</option>
                    <option value="04">04 - lire un registre d'entrées</option>
                    <option value="03">03 - lire un registre de sorties</option>
                    <option value="06">06 - écrire un registre</option>
                    <option value="16">16 - écrire des registres</option>
                    <option value="23">23 - lecture / écriture dans les registres</option>
                    <option value="22">22 - écrire dans un registre en appliquant un masque</option>
                    <option value="24">24 - lire une pile FIFO</option>
                    <option value="20">20 - lire un fichier</option>
                    <option value="21">21 - écrire un fichier</option>
                    <option value="43">43 - lire les informations d'un équipement</option>
                </select>
            </div>


            <div id="D_arg0" style="float:left;display:none;">
                <label id="L_iarg0" for="I_arg0">Adresse de début</label>
                <input type="text" id="I_arg0" value="0" />
            </div>

            <div id="D_arg1"  style="float:left;display:none;">
                <label id="L_iarg1" for="I_arg1">Nombre d'adresses à lire</label>
                <input type="text" id="I_arg1" value="4" />
            </div>
        </div>
    </fieldset>

    <div>
        <input type="button" id="B_submitReq" value="Go !!" />
    </div>
</div>



<div id="D_" >
    <div id="D_error"></div>

    <h2>Trame envoyée</h2>
    <div id="D_frameSent">waiting ...</div>

    <h2>Trame reçue</h2>
    <div id="D_frameReceived">waiting ...</div>

    <h2>Données reçues</h2>
    <div id="D_rcvDatas">waiting ...</div>
</div>
