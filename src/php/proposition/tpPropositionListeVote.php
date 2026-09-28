<?php 
    declare(strict_types=1);
    namespace theBand\src\php\proposition;
    /*******************************************************************************
     * 
     ******************************************************************************/
    
    use shared\php\bricks\Brick_table           as BkTable;
    use theBand\src\php\socle\UserTheBand       as User;
    use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
  
    ob_start(); 
    echo('<form method="post" action="' . TbAdressage::getURLstatic("proposition","propositionMajVotes") . '">');
    $matable = new BkTable(tableClass:'table table-hover table-sm');
    //pas de numérotation sur première et dernière ligne
    $matable->addHeaders(["Vote","#;1","Titre","Interprète","Lien","Vote<br>Préférence","Vote<br>Maitrise"],rang:1);
    $matable->addCell(voteligneBouton(1), "barreTitre", colspan:20,nouveau:true);
    foreach ($propositions as $numero=>$proposition) {
        $texte = '<input type="text" id="changevote'. $numero .'" name="vote'.$proposition->id .'" value=""/>';
        $matable->addCell($texte,nouveau:true, hidden:true);
        
        $texte = '<p id="vote'. $numero .'"</p><input hidden type="text" id="changevote'. $numero . '" name="vote'. $proposition->id . '" value=""/>';
        $matable->addCell($texte);
        
        $matable->addCell(htmlspecialchars($proposition->titre));
        $matable->addCell(htmlspecialchars($proposition->interprete));
        $matable->addCell($proposition->mettreEnFormeLiens());
        //<!--Affichage des votes APP et APT-->
        $matable->addCell(listerVotesProposition($numero,Vote::PREFERENCE,$proposition));
        $matable->addCell(listerVotesProposition($numero,Vote::MAITRISE,$proposition));
    }
    $matable->addCell(voteligneBouton(2), "barreTitre", colspan:20,nouveau:true);
  
    echo $matable->render();
    echo "</form>";

    $content = ob_get_clean(); 
    require(TbAdressage::projetGetLayout(__NAMESPACE__));

function listerVotesProposition(int $numero, int $objet ,proposition $proposition) : string{
    //Affiche la liste des votes de chaque utilisateur
    //objet = préférence 1 ou maitrise 2
    //affichage saisie unique ou tous votes
    $vote = $proposition->getVoteUserObject($objet,User::connectUserGetInfo('abrev'));
    //si abrev correspond au connecté alors saisie possible
    $texte =  "<input style=\"text-align:center;\" type=\"number\"  name=\"cvote_" . $proposition->id . "_". $objet .  "\"
      oninput=\"jsVoteModif('vote" . $numero . "')\")\" width=\"15\"  min=\"0\" max=\"". votePlafond($objet) . "\" value=\"". $vote . "\"".
          "data-bs-toggle=\"tooltip\" data-bs-placement=\"top\" title=\"" . voteLibelle($objet) . "\">";
    
    return $texte ;
}

function voteLibelle(int $objet) :string {
//Retourne le liellé du tag en fonction de l'objet
    switch ($objet){
        case Vote::PREFERENCE :
            return Vote::LIBELLE_PREFERENCE ;
        case Vote::MAITRISE :
            return Vote::LIBELLE_MAITRISE ;
        default :
            return "Erreur code objet vote";
    }
}
function votePlafond(int $objet) :string {
//Retourne le libellé du tag en fonction de l'objet
    switch ($objet){
        case Vote::PREFERENCE :
            return '5' ;
        case Vote::MAITRISE :
            return '5' ;
        default :
            return "Erreur code objet vote";
    }
}

function voteligneBouton(int $ligne):string{
    return '<input id="btnvote' . $ligne . '" type="submit" value="Enregistrer les votes de '. User::connectUserGetInfo("prenom"). '"/>'
            . '<span class="badge bg-secondary">Préférence : '  . Vote::LIBELLE_PREFERENCE. '</span> '
            . '<span class="badge bg-secondary"> Maitrise : ' . Vote::LIBELLE_MAITRISE . '</span>' 
            . '<span class="badge bg-info">1 en préférence est éliminatoire</span>';
}   
