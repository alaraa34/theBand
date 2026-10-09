/*******************************************************************************
 * Edition d'une setlist en glisser-déposer (tpConcertBuildSetlist.php)
 * Souris sur PC ; doigt sur tablette grâce à jquery.ui.touch-punch (chargé avant)
 * Chaque titre : <div id="song<id>" class="portlet"> contenant <p hidden><id></p>
 * et la flèche d'enchaînement <span id="arrow<id>">
 *******************************************************************************/
(function($) {
	"use strict";
    $( ".column" ).sortable({
        connectWith: ".column",
        handle: ".portlet-header",
        cancel: ".portlet-toggle, .bouton-enchainer",
        items: ".portlet",
        distance: 5,                    //un simple toucher ne déclenche pas de déplacement
        //un titre déplacé perd son enchaînement, puis on recalcule compteurs et listes
        stop: function (event, ui) {
            jsEnleverTagEnchainement(ui.item);
            calculerCompteurs();
            listerIdParties();
        }
    });
    $( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
      .find( ".portlet-header" ).addClass( "ui-widget-header ui-corner-all" );

    $( ".portlet-toggle" ).on("click", function() {
        var icon = $( this );
        icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
        icon.closest( ".portlet" ).find( ".portlet-content" ).toggle();
    });
    //état initial : les champs cachés du formulaire sont remplis dès l'affichage
    calculerCompteurs();
    listerIdParties();
})(jQuery);

function calculerCompteurs(){
//nombre de titres de chaque colonne dans son badge
    for (var i = 0; i < 4; i++){
        $("#compteur" + i).text($("#liste" + i + " .portlet").length);
    }
}

function listerIdParties(){
//remplit les champs cachés listePartie1..3 : ids séparés par des -, suffixe E si enchaînement
    for (var i = 1; i < 4; i++){
        var tableau = $("#liste" + i + " .portlet p");
        var texte = "";
        for (var j = 0; j < tableau.length; j++){
            var id = tableau[j].textContent.trim();
            if (j > 0){ texte += "-"; }
            texte += id;
            if ($("#arrow" + id).css("display") !== "none"){ texte += "E"; }
        }
        $('#listePartie' + i).val(texte);
    }
}

function jsEnchainer(id){
//bascule l'enchaînement d'un titre (double-clic sur PC, bouton sur tablette)
    $("#arrow" + id).toggle();
    listerIdParties();
}

function jsEnleverTagEnchainement(objet){
    var id = objet.prop("id").substring(4);
    $("#arrow" + id).hide();
}
