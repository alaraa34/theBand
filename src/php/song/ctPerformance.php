<?php
namespace theBand\src\php\song;
/*******************************************************************************
 * Controleur des performances : notation par les musiciens et moyennes
 ******************************************************************************/
use shared\php\classes\socle\User                      as User;
use shared\php\toolbox\Toolbox                         as Tbx;
use shared\php\classes\socle\Login                     as Login;
use shared\php\toolbox\Toolbox_date                    as TbDate;
use shared\php\toolbox\Toolbox_liste                   as TbListe;
use shared\php\bricks\Brick_table                      as BkTable;
use shared\php\toolbox\Toolbox_adressage               as TbAdressage;

/*******************************************************************************
 * Performances
 ******************************************************************************/
function ctRepertoireNotation(){
//notation de la performance du groupe
     $performances= Performance::listePourVotePerformance();
     $script =  Tbx::includeCSS('song','theBand/src');
     if(Login::loginControl(__NAMESPACE__)){require('tpSongListeVote.php');}
}

function ctRepertoireMajVotes() {
    //Enregistre les votes suite à une saisie
    $messageBarreMenu = "";
    //extrait les lignes qui ont bougé
    $mouvements = array_filter($_POST, function($v, $k) {
                                    return str_starts_with($k,"changevote") & $v == "M";
                                }, ARRAY_FILTER_USE_BOTH);
    //Balayage de toutes les propositions éligibles au vote 
    $idUser = User::connectUserGetInfo('id');
    foreach ($mouvements as $key=>$value) {
        // un mouvement est sous la forme vote + id proposition en clé
        $performance = new Performance();
        $performance->song->id = intval(substr($key,10));
        $performance->user->id = $idUser;
        $performance->rechercheID(); //recherche de l'ID de la performance pour ne pas dupliquer
        //cvotes sous la forme cvote_" . $proposition->id . "_". $objet . 
        $performance->groupe = TbAdressage::getPost("I","cvote_" . $performance->song->id );
        $performance->date = TbDate::dateDuJour();
        //Mise à jour avec identifiant externe
        $performance->update();
        unset($performance);
    }
        
    //affichage 
    $content = "<div id=\"pageAffichee\" align=\"center\"  class = \"d-block mx-auto img-fluid\">>
        <br>
        <img src=\"https://www.gifimili.com/gif/2019/05/urne-vote.gif\"  alt=\"A vote\">
        <p> A VOTE ! </p>
        </div>";
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
}

function ctRepertoireAfficherPerformanceGroupe() {
    $infos= Performance::tableauMoyennePerformanceGroupe();
    ctRepertoireAfficherPerformance($infos,"sommeGroupe");
}

function ctRepertoireAfficherPerformancePerso() {
    $infos= Performance::tableauMoyennePerformancePerso();
    ctRepertoireAfficherPerformance($infos,"perso");
}

function ctRepertoireAfficherPerformance(array $infos,string $zone) {
//afficha la liste des performances pour les morceaux en cours ou test
          
    //traitement
    if (count($infos)==0){
       $content = Tbx::messageColorer(false,"","Aucune performance n'a été estimée");
       require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
    else{
        //Aucune Action possibles
        $actions = [];
        $messageBarreMenu = "Performance par ordre croissant";
  
        //zone = nom de la zone dans infos,
        //titre = titre à mettre , si rien c'est le nom de la zone 
        //fonction à appliquer
        $infosColonnes[] = ['zone'=>BkTable::COLONNE_NUMERO]; //Colonne numérotation
        $infosColonnes[] = ['zone'=>'titre','fonction'=> TbListe::FONCTION_VASN];
        $infosColonnes[] = ['zone'=>'interprete','titre'=>'Interprète'];
        $infosColonnes[] = ['zone'=>'typeSong','titre'=>'Concert<br>Test','fonction'=>TbListe::FONCTION_VASN,'align'=>'C'];
        $infosColonnes[] = ['zone'=> $zone,'titre'=>'Moyenne <br>groupe','align'=>'C',TbListe::FONCTION_VASN];
        $script = Tbx::includeJS('utils');  //traitement des actions de la liste
        $content = TbListe::constituerListe( $infos, $infosColonnes, $actions);
        require(TbAdressage::projetGetLayout(__NAMESPACE__));
    }
}
