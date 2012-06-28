/*
 */


$(document).ready(function() {

    /*
         * vide les champs lors de la prise de focus
         */
    $('#memMapAddr').focus(function(){
        //alert('ok');
        $('.mapAdress input, td').val('').html('');
    })

    $('#memMapAddr').keyup(function () {
        //alert('ok');
        $('#memMapAddr').val($('#memMapAddr').val().toUpperCase());
        //alert($('#memMapAddr').val());

        aHalfByte = new Array(8);

        memMapAddrVal = $(this).val();


        c = '';
        i = 0;

        /*
             *  validité des caractères entrés et longueur min/max
             */
        $(this).val($(this).val().replace(/[^0-9A-F]/i, '')); // supprime les caractères invalides en temps réel

        if (!$(this).val().match(/^([0-9A-F]){1,8}$/i)) {
            $(this).val($(this).val().substr(0, ($(this).val().length-1)));

            return false;
        }
        //alert(memMapAddrVal);
        //
        memMapAddrVal = memMapAddrVal.split('');


        for(c in memMapAddrVal) { // pour chaque caractère hexa

            //alert("bk");

            a = parseInt(memMapAddrVal[c], 16);
            b = a.toString(2);

            // complète la chaîne binaire à 4 bits
            while (b.length < 4) {
                b = '0'+b;
            }

            aHalfByte[i] = b; // ajout au tableau (éléments de 4 bits)

            i++;
        }

        //alert(aHalfByte);

        // rempli les champs binaires
        for (r in aHalfByte) {
            $('#memMapAddrRegisterAddrBin_'+parseInt((r*4))).html(aHalfByte[r][0]);
            $('#memMapAddrRegisterAddrBin_'+parseInt((r*4)+1)).html(aHalfByte[r][1]);
            $('#memMapAddrRegisterAddrBin_'+parseInt((r*4)+2)).html(aHalfByte[r][2]);
            $('#memMapAddrRegisterAddrBin_'+parseInt((r*4)+3)).html(aHalfByte[r][3]);
        }

        _8bits = '';
        _16bits = '';
        for(i = 7; i <= 14; i++) {
            _8bits+= $('#memMapAddrRegisterAddrBin_'+i).html();
        }
        for(i = 14; i <= 30; i++) {
            _16bits+= $('#memMapAddrRegisterAddrBin_'+i).html();
        }

        $('#mapAddrUnitId8Bits').val(parseInt(_8bits,2));
        $('#mapAddrUnitId16Bits').val(parseInt(_16bits,2));

        $('input[name=mapAddrUnitId8BitsPlus2]').val(parseInt(_8bits,2)+2);
        $('#mapAddrUnitId16BitsPlus1').val(parseInt(_16bits,2)+1);
        $('#mapAddrUnitId16BitsPlus2').val(parseInt(_16bits,2)+2);


    });


    $('#memMapAddr').keyup();

});



