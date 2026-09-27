<?php
    declare(strict_types=1);
    namespace theBand\src\php\proposition;
    /*******************************************************************************
     * 
     ******************************************************************************/
    
    use shared\php\bricks\Brick_table           as BkTable;
    use shared\php\toolbox\Toolbox              as Tbx;
    use shared\php\modale\Toolbox_modal         as TbModal;
    use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
    
    ob_start(); 
     
    $nbColUsers = (2* count($listeUsers)) + 2 + 1 ;                             //nbusers + total /vote + total général
    $nbColonnes = 6 + $nbColUsers + (count($actions)=== 0 ? 0:1);               //Nombre de colonnes 4+ commentaire+1colonne à blanc
    
    //Init table
    $matable = new BkTable(tableClass:'table-striped');
     //entête ligne 1
    if (count($actions) > 0){$matable->addHeader("Actions"," ",rang:1);}
    $matable->addHeader(BkTable::COLONNE_NUMERO,"",rang:1);                     //Numérotation après action
    $matable->addHeaders(["Titre","Interprète","Lien"," "],1);
    $matable->addHeader("Vote<br>Préférence", colspan : count($listeUsers)+ 1, rang:1);
    $matable->addHeader("Vote<br>Maitrise", colspan : count($listeUsers)+ 1, rang:1);
    $matable->addHeader("Commentaire", "col-md-7",colspan:2, rang:1);
  
   //entête ligne2 
    $matable->addHeader("Préférence:". Vote::LIBELLE_PREFERENCE . "<br>" . "Maitrise:" . Vote::LIBELLE_MAITRISE ,
                            classe: " " ,alignement:"G", colspan:$nbColonnes - $nbColUsers - 1 ,rang:2 );
    listeUsers ($listeUsers,$matable);
    listeUsers ($listeUsers,$matable);
    $matable->addHeader("<strong>P+M</strong>",classe: " ",rang:2);
    //lignes 
    foreach($propositions as $index=>$proposition){
        if (count($actions) === 0){
            $nouveau = true;}
        else{
            $matable->addCell(TbModal::mettreEnFormeActions($actions,$proposition->id),nouveau: true);
            $nouveau = false;
        }
        $matable->addCell(htmlspecialchars($proposition->titre),nouveau: $nouveau);
        $matable->addCell(htmlspecialchars($proposition->interprete));
        $matable->addCell($proposition->mettreEnFormeLiens());
        $matable->addCell(" "); //colonne blanche
        listerVotesProposition(1,$proposition,$listeUsers,$matable);
        listerVotesProposition(2,$proposition,$listeUsers,$matable);
        $matable->addCell(number_format($proposition->totalGeneral(),2));
        $matable->addCell(Tbx::valeurAffichableSiNull($proposition->commentaire->texte));
    }
    
    //affichage table
    echo $matable->render();
    echo "</form>";
    unset ($matable);
    //Rajout des div modales 
    echo TbModal::afficherListeDivModales($actions);
    $content = ob_get_clean();
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
        
    function entetePropositionUsers(array $listeUsers, BkTable $matable):void{
        //Boucle sur la liste des users pour les entêtes de votre

        foreach ($listeUsers  as $user) {
            $matable->addcell($user["abrev"], alignement:"C");
        }
        $matable->addcell($user["abrev"], alignement:"C");
    }
    function listerVotesProposition(int $objet ,Proposition $proposition,array $listeUsers,BkTable $matable) : string{
        //Affiche la liste des votes de chaque utilisateur
        //objet = préférence 1 ou maitrise 2
        $texte = "";
        foreach ($listeUsers  as $user) {
            //affichage saisie unique ou tous votes pour le user concerné
            $valeur = $proposition->getVoteUserObject($objet,$user['abrev']);
            $classe =  ($valeur ==1 and $objet ==1) ? "table-dark" :"";
            $matable->addcell($valeur,classe:$classe,alignement:"MC");
            
        }
        $matable->addcell("<strong>" . $proposition->total($objet) . "</strong>",alignement:"CM");
    return $texte ;
    }
    function listeUsers(array $listeUsers , BkTable $matable):void {
        foreach ($listeUsers  as $user) {
            $matable->addHeader($user["abrev"],classe: " ",rang:2);
        }
        $matable->addHeader("MOY",rang: 2);
    }
