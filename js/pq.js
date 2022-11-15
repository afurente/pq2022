/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function carica(tipo) {
    $("#tipoAlimento").html(tipo);
    $("#fldTipo").val(tipo);
    $.mobile.changePage('#insertData', 'pop', true, true);    
}

function caricaConsegna(tipo) {
    
    var id_consegna = $("#selectConsegna").val();
    var nome = $( "#selectConsegna option:selected" ).text();
    
    $("#idConsegna").html(nome);
    $("#fldIDConsegna").val(id_consegna);
    
    $("#tipoAlimento").html(tipo);
    $("#fldTipo").val(tipo);
    $.mobile.changePage('#insertData', 'pop', true, true);    
}

