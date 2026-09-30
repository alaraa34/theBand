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
// Choix des titres d'une répétition (tpRepetitionDetail)
//  - #choixSong        : liste fixe de tous les titres test + concert, seule la sélection change
//  - #morceauxChoisis  : titres sélectionnés, dans l'ordre où ils ont été ajoutés
//  - #idSongsSelected  : ids sélectionnés "12-15-18", seul champ lu à l'enregistrement
//-------------------------------------------------------------------------------------------
function jsRepetInit(){
    //ordre initial = ordre enregistré de la répétition (déjà dans idSongsSelected)
    jsRepetSynchroniser();
}

function jsRepetSynchroniser(){
//met à jour Sélection actuelle d'après les titres sélectionnés dans Titres disponibles
//garde l'ordre des titres déjà présents, ajoute les nouveaux à la fin
    const precedents = ($('#idSongsSelected').val() || '').split('-').filter(id => id !== '');
    const selectionnes = $('#choixSong option:selected').map(function(){ return this.value; }).get();
    const choisis = precedents.filter(id => selectionnes.includes(id))
                        .concat(selectionnes.filter(id => !precedents.includes(id)));
    const droite = $('#morceauxChoisis').empty();
    choisis.forEach(id => {
        $('<li class="list-group-item"></li>')
            .attr('data-id', id)
            .text($('#choixSong option[value="' + id + '"]').text())
            .appendTo(droite);
    });
    $('#nbChoisis').text(choisis.length);
    $('#idSongsSelected').val(choisis.join('-'));
}

function jsRepetSelectionAuto(urlControleur){
//bouton Sélectionner : seuls les titres du critère deviennent sélectionnés, les autres sont désélectionnés,
//comme si l'utilisateur avait fait lui-même cette sélection
    $.post(urlControleur, $('#repetitionPostForm').serialize(), function(reponse){
        //le contrôleur renvoie les ids "12-15-18" (uniquement des titres test + concert)
        $('#choixSong option').prop('selected', false);
        reponse.trim().split('-').forEach(id => {
            $('#choixSong option[value="' + id + '"]').prop('selected', true);
        });
        jsRepetSynchroniser();
    }, 'text')
    .fail(function(erreur){
        console.log('jsRepetSelectionAuto : ' + erreur.status + ' ' + erreur.statusText + ' ' + erreur.responseText);
    });
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