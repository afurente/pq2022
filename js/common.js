
//**********************************************************************************************************************
// DEFINIZONE VARIABILI GLOBALI


// END DEFINIZONE VARIABILI GLOBALI
//**********************************************************************************************************************
let gRotelline = 0;
function getQueryString() {
    let key = false, res = {}, itm = null;
    // get the query string without the ?
    let qs = location.search.substring(1);
    // check for the key as an argument
    if (arguments.length > 0 && arguments[0].length > 1)
        key = arguments[0];
    // make a regex pattern to grab key/value
    let pattern = /([^&=]+)=([^&]*)/g;
    // loop the items in the query string, either
    // find a match to the argument, or build an object
    // with key/value pairs
    while (itm = pattern.exec(qs)) {
        if (key !== false && decodeURIComponent(itm[1]) === key)
            return decodeURIComponent(itm[2]);
        else if (key === false)
            res[decodeURIComponent(itm[1])] = decodeURIComponent(itm[2]);
    }

    return key === false ? res : null;
}

function muinutesToString(minutes) {
    /*
     Converte un intervallo temporale espresso medinate un numero diminuti in una stringa equivalente.
     Es: 2010 minuti = 1440 + 560 + 10 = 1g 9h 10m
     */
    let result = "";
    let gg = 0, hh = 0, resto = 0;

    if (minutes > 1440) {
        gg = Math.floor(minutes / 1440);
        result = gg + "g ";
    }

    resto = minutes - gg * 1440;
    if (resto > 60) {
        hh = Math.floor(resto / 60);
        result += hh + "h ";
    }
    resto = resto - hh * 60;
    result += resto + "m";
    return result;
}
//**********************************************************************************************************************


/*
 Scarica i dati dai webservice
 */
function getWSData(url, arrayData, elaboraResultOK, objName, tipo)
{
    if (!objName) {
        objName = "#loading,#darkLayer";
    }
    if (!tipo) {
        gRotelline = 0;
    }
    showLoading(objName);
    $.ajax({
        type: "POST",
        data: arrayData,
        url: url
    }).done(function (response) {
        let avarParsed;
        if (typeof response === 'object' && response !== null) {
            avarParsed = response;
        } else {
            try {
                avarParsed = JSON.parse(response);
            } catch (err) {
                DisplayMsg("Risposta dal server", response);
            }
        }
        try {
            if (avarParsed['result'] == 'OK') {
                elaboraResultOK(avarParsed);
            } else {
                let title = "Server KO";
                if ("answer_code" in avarParsed) {
                    title = "(" + avarParsed['answer_code'] + ") Server KO";
                }
                DisplayMsg(title, avarParsed['error_msg']);
            }
        } catch (err) {
            DisplayMsg("Errore!", "<b>" + err + "</b><br />Risposta dal server:<hr />" + response);
        }
    }
    ).fail(function (request, status, error) {
        DisplayMsg("AJAX call fails", request.responseText + "<br /><b>" + request.status + "</b>&nbsp;" + request.statusText);
    }
    ).always(function () {
        hideLoading(objName);
    }
    );
}
//**********************************************************************************************************************



/*
 Invia dati ai webservice (per ora è identica alla getWSData, magfari in futuro potrà servire differenziarle)
 */

function putWSData(arrayData, elaboraResultOK, objName) {
    if (!objName) {
        objName = "#loading,#darkLayer";
    }

    showLoading(objName);

    $.ajax
            (
                    {
                        type: "POST",
                        data: arrayData,
                        url: "index.php"
                    }
            )
            .done
            (
                    function (strReturned)
                    {
                        let avarParsed;
                        try
                        {
                            avarParsed = JSON.parse(strReturned);
                        } catch (err)
                        {
                            DisplayMsg("Risposta dal server", strReturned);
                        }

                        try
                        {

                            if (avarParsed['result'] == 'OK') {
                                elaboraResultOK(avarParsed);
                            } else
                                DisplayMsg("Server KO", avarParsed['error_msg']);
                        } catch (err)
                        {
                            DisplayMsg("Server Error", err);
                        }
                    }
            )
            .fail
            (
                    function (request, status, error)
                    {
                        DisplayMsg("AJAX call fails", request.responseText + "<br /><b>" + request.status + "</b>&nbsp;" + request.statusText);
                    }
            )
            .always
            (
                    function () {
                        hideLoading(objName);
                    }
            );

}

//**********************************************************************************************************************

function uploadWSFile(formData, elaboraResult) {

    $.ajax({
        url: 'index.php', //Server script to process data
        type: 'POST',
        xhr: function () {  // custom xhr
            myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) { // check if upload property exists
                // for handling the progress of the upload
                myXhr.upload.addEventListener('complete', function () {
                    hideLoading();
                }, false);
            }
            return myXhr;
        },
        //Ajax events
        beforeSend: function () {
            showLoading();
        },
        success: elaboraResult,
        error: function (request, status, error) {
            hideLoading();
            DisplayMsg("AJAX Fil upload fails: ", request.responseText + "<br /><b>" + request.status + "</b>&nbsp;" + request.statusText);
        },
        complete: function () {
            hideLoading();
        },
        // Form data
        data: formData,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    }).always(
            function () {
                hideLoading();
            }
    );
}

//**********************************************************************************************************************
function DisplayMsg(title, message) {
    /*
     $("#dialog_message").dialog("option", "title", title);
     $("#dialog_message").html(message);
     $("#dialog_message").dialog("open");
     */
    alert(message);
}

//**********************************************************************************************************************
function utf8_encode(argString) {
    //  discuss at: http://phpjs.org/functions/utf8_encode/
    // original by: Webtoolkit.info (http://www.webtoolkit.info/)
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: sowberry
    // improved by: Jack
    // improved by: Yves Sucaet
    // improved by: kirilloid
    // bugfixed by: Onno Marsman
    // bugfixed by: Onno Marsman
    // bugfixed by: Ulrich
    // bugfixed by: Rafal Kukawski
    // bugfixed by: kirilloid
    //   example 1: utf8_encode('Kevin van Zonneveld');
    //   returns 1: 'Kevin van Zonneveld'

    if (argString === null || typeof argString === 'undefined') {
        return '';
    }

    let string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
    let utftext = '',
            start, end, stringl = 0;

    start = end = 0;
    stringl = string.length;
    for (let n = 0; n < stringl; n++) {
        let c1 = string.charCodeAt(n);
        let enc = null;

        if (c1 < 128) {
            end++;
        } else if (c1 > 127 && c1 < 2048) {
            enc = String.fromCharCode(
                    (c1 >> 6) | 192, (c1 & 63) | 128
                    );
        } else if ((c1 & 0xF800) != 0xD800) {
            enc = String.fromCharCode(
                    (c1 >> 12) | 224, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
                    );
        } else { // surrogate pairs
            if ((c1 & 0xFC00) != 0xD800) {
                throw new RangeError('Unmatched trail surrogate at ' + n);
            }
            let c2 = string.charCodeAt(++n);
            if ((c2 & 0xFC00) != 0xDC00) {
                throw new RangeError('Unmatched lead surrogate at ' + (n - 1));
            }
            c1 = ((c1 & 0x3FF) << 10) + (c2 & 0x3FF) + 0x10000;
            enc = String.fromCharCode(
                    (c1 >> 18) | 240, ((c1 >> 12) & 63) | 128, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
                    );
        }
        if (enc !== null) {
            if (end > start) {
                utftext += string.slice(start, end);
            }
            utftext += enc;
            start = end = n + 1;
        }
    }

    if (end > start) {
        utftext += string.slice(start, stringl);
    }

    return utftext;
}

//**********************************************************************************************************************



function supports_html5_storage() {
    try {
        return 'localStorage' in window && window['localStorage'] !== null;
    } catch (e) {
        return false;
    }
}
//**********************************************************************************************************************




function ObjEquals(x, y) {
    if (x === y)
        return true;
    // if both x and y are null or undefined and exactly the same

    if (!(x instanceof Object) || !(y instanceof Object))
        return false;
    // if they are not strictly equal, they both need to be Objects

    if (x.constructor !== y.constructor)
        return false;
    // they must have the exact same prototype chain, the closest we can do is
    // test there constructor.

    for (let p in x) {
        if (!x.hasOwnProperty(p))
            continue;
        // other properties were tested using x.constructor === y.constructor

        if (!y.hasOwnProperty(p))
            return false;
        // allows to compare x[ p ] and y[ p ] when set to undefined

        if (x[ p ] === y[ p ])
            continue;
        // if they have the same strict value or identity then they are equal

        if (typeof (x[ p ]) !== "object")
            return false;
        // Numbers, Strings, Functions, Booleans must be strictly equal

        if (!ObjEquals(x[ p ], y[ p ]))
            return false;
        // Objects and Arrays must be tested recursively
    }

    for (p in y) {
        if (y.hasOwnProperty(p) && !x.hasOwnProperty(p))
            return false;
        // allows x[ p ] to be set to undefined
    }
    return true;
}
//**********************************************************************************************************************
// FUNZIONE PER LA CRITTOGRAFIA RSA



function codifica(strPublickey, stringa, callback) {
    $.jCryption.crypt.setKey(strPublickey);

    $.jCryption.encryptKey(stringa, function (encryptedKey) {
        cyph_user = encryptedKey;
    });

}
;
//**********************************************************************************************************************
/** Mostra icona di caricamento */
function showLoading(object) {
    return;
    gRotelline += 1;
    $(object).removeClass("hide");
}
//**********************************************************************************************************************
/** Nasconde icona di caricamento */
function hideLoading(object) {
    return;
    gRotelline -= 1;
    if (gRotelline < 0)
        gRotelline = 0;
    if (gRotelline == 0)
        $(object).addClass("hide");

}

/*$(document).ready(function () {
 $(".modal-dialog").on('mouseleave', function () {            
 $(this).parent("div").modal('hide');
 });
 });*/
//**********************************************************************************************************************
$(document).ready(function () {

});