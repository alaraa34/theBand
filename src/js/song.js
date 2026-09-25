//===============================================================================================
function jsVoteModif(idElement){
//indique dans la première colonne qu'un vote a été fait; idElement est par exemple vote19
//Attention piège n'accède pas au deuxième contraole si deux dans un même TD
    $('#' + idElement).html("<img src='images/vote.jpg' alt='image' class='centrer' width='20' height='20'>");
    //Indication que vote sur cette ligne
    $('#change' + idElement).val("M");
    //affiche les boutons d'enregistrement des votes
    // show ne marche que sur style=\"display:none\" 
    $("#btnvote1").show();
    $("#btnvote2").show();
}
function jsActiverSetlist(){
//active coche set list défaut saur saisie de concetr
    if ($("#specifique").prop("checked")){
        //spécifique coché activation de initialiser liste
        $("#initList").prop('disabled', false);
    }
    else{
        //si spécifique pas coché, désactivé et décoché
        $("#initList").prop('checked',false);
        $("#initList").prop('disabled', true);
    }
}
//-------------------------------------------------------------------------------------------
function jsListerChoixRepet(){
//Constitue une liste des morceaux choisis
//alimente aussi idSongsSelected qui est une liste des id sélectionnés
    compteur=0;
    listeID ="";
    $('#morceauxChoisis').empty();
    $('#choixSong option').each(function(){
        //Si choisi modifie le texte
        if (this.selected){
            $('#morceauxChoisis').append("<li>" + this.text  + "</li>");
            compteur += 1;
            if (compteur > 1){
                //ajout "- à partir de la deuxième occurence
                listeID += "-";
            }
            listeID += this.value;
        }
    });
    $('#nbChoisis').text(compteur);
    $('#idSongsSelected').val(listeID);
}