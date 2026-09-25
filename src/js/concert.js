(function($) {
	"use strict";
    $( ".column" ).sortable({
      connectWith: ".column",
      handle: ".portlet-header",
      cancel: ".portlet-toggle",
    });
 
    $( ".portlet" )
      .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
      .find( ".portlet-header" )
        .addClass( "ui-widget-header ui-corner-all" )
       
 
    $( ".portlet-toggle" ).click(function() {
      var icon = $( this );
      icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
      icon.closest( ".portlet" ).find( ".portlet-content" ).toggle();
    });
    
    //détagage enchainement sur un drag/drop
    $('[id^=liste').droppable({
    drop: function (event, ui) {
        // id of the dropped element
        //var draggedId = ui.draggable.prop("id");
        //alert (draggedId);
        jsEnleverTagEnchainement(ui.draggable);}
    });
    
   
   //pour google chrome
   $( ".portlet-header" ).mouseleave(function() {
        calculerCompteurs(); 
        listerIdParties();
        //console.log("recalculs effectués mouseleave");
    });
   
    //pour affichage mozilla
    $( ".portlet-header" ).click(function() {
       calculerCompteurs(); 
       listerIdParties();
       //console.log("recalculs effectués click");
    });
    
  })(jQuery);
  
function calculerCompteurs(){
    $("#compteur0").text($("#liste0 .portlet").length);
    $("#compteur1").text($("#liste1 .portlet").length);
    $("#compteur2").text($("#liste2 .portlet").length);
    $("#compteur3").text($("#liste3 .portlet").length); 
}
function listerIdParties(){
//Alimente les input qui contiennnet les id de chaque partie
    var i = 0;
    var texte = "";
    var tableau = [];
    //uniquement à partir de 1 car la liste des titres restant n'est pas utile
    for (i=1;i<4;i++){
        //liste des tags concernés dans la partie
        tableau = $("#liste" + i + " .portlet p");
        texte = "";
        //liste des songs avec "-" comme separateur
        for (j=0;j<tableau.length;j++){
            //séparateur à partir de la deuxième position
            if (j>0){texte+= "-";}
            texte += tableau[j].outerText;
            //teste si enchainement
            if ($("#arrow" + tableau[j].outerText).is(":visible")){
                texte += "E";
            }
        }
        $('#listePartie'+i).val(texte);
    }
}
function jsEnchainer(id){
//appelé sur le doucble click pour indiquer qu'il y a enchainement
//affiche une flèche ou la désaffiche
    $("#arrow" + id).toggle();   
}
function jsEnleverTagEnchainement(objet){
    //objet est la div contenant <span id=\"arrow14". $titre['ID']
    id=objet.prop("id").substring(4); //ex song14 comme id
    $("#arrow"+id).hide();   
}