<?php
namespace theBand\src\php\song;
/*******************************************************************************
 * Controleur des listes du répertoire par statut (concert, test, réserve, abandon)
 * les actions des listes appellent le controleur song (song;editer, song;valider...)
 ******************************************************************************/
use shared\php\classes\socle\User                      as User;
use shared\php\toolbox\Toolbox                         as Tbx;
use shared\php\toolbox\Toolbox_liste                   as TbListe;
use shared\php\bricks\Brick_table                      as BkTable;
use shared\php\toolbox\Toolbox_adressage               as TbAdressage;

function ctRepertoireRestreint(array $types, array $actions, string $messageBarreMenu){
//Liste d'une partie des morceaux test, concert, réserve, abandonné
//parametres    $types types de repertoire à afficher (convert, test....)
//              $actions : actions possibles
//              
    //Liste des titres 
    $infos = Repertoire::getListe($types,true);
          
    //Actions possibles
    $actionsListe = User::userConnecte() ? composerListeActions($actions) : [];
    $script = Tbx::includeJS('utils');
    //Paramétrage liste générique
     //zone = nom de la zone dans infos,
    //titre = titre à mettre , si rien c'est le nom de la zone 
    //fonction à appliquer
    $infosColonnes[] = ['zone'=>BkTable::COLONNE_NUMERO]; //Colonne numérotation
    $infosColonnes[] = ['zone'=>'titre','fonction'=> TbListe::FONCTION_VASN];
    $infosColonnes[] = ['zone'=>'interprete','titre'=>'Interprète','fonction'=>TbListe::FONCTION_VASN];
    $infosColonnes[] = ['zone'=> 'tonaOrigine','titre'=>'Tona<br>origine','fonction'=> TbListe::FONCTION_VASN,'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'tonaScene','titre'=>'Tona<br>concert','fonction'=>TbListe::FONCTION_VASN,'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'tempo','titre'=>'Tempo<br>(bpm)','fonction'=>TbListe::FONCTION_VASN,'align'=>'MC'];
    if (count($types)>1){$infosColonnes[] = ['zone'=> 'typeSong','titre'=>'type','fonction'=>TbListe::FONCTION_VASN];}
    $infosColonnes[] = ['zone'=> 'leadChant','titre'=>'chant','fonction'=>TbListe::FONCTION_VASN,'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'liens','fonction'=> TbListe::FONCTION_FALC,'parametres'=>array('$valeur',false),'align'=>'MC'];
    $infosColonnes[] = ['zone'=> 'texteCommentaire','titre'=>'Commentaire','fonction'=>TbListe::FONCTION_VASN];
    $content = TbListe::constituerListe($infos, $infosColonnes, $actionsListe);
    require(TbAdressage::projetGetLayout(__NAMESPACE__));
    
}

function ctRepertoire() {
//Liste des chansons jouées par le groupe, pas d'action possible
// Paramètre la liste des types de songs à afficher
    ctRepertoireRestreint([Repertoire::TYPE_CONCERT, Repertoire::TYPE_TEST],[],"Titres joués en concert ou testés");
}
function ctRepertoireConcert() {
//Liste des chansons jouées par le groupe avec actions
    ctRepertoireRestreint([Repertoire::TYPE_CONCERT],['modifier','reserver'],"Titres joués en concert");
}
function ctRepertoireTest() {
//Liste des chansons jouées par le group
    ctRepertoireRestreint([Repertoire::TYPE_TEST],['modifier','valider','abandonner'],"Titres en cours de test");
}
function ctRepertoireReserve() {
//Liste des chansons jouées naguère par le group
    ctRepertoireRestreint([Repertoire::TYPE_RESERVE],['modifier','reprendre','abandonner'],"Titres déjà joué mais sortis de la playlist");
}
function ctRepertoireAbandon() {
//Liste des chansons jouées par le group
    ctRepertoireRestreint([Repertoire::TYPE_ABANDON],['supprimer','retester'],"Titres abandonnés (test négatif ou plus souhaité)");
}

function composerListeActions(array $actions){
    //Compose la liste des actions selon codes
    $listeA=[];
    foreach($actions as $action){
        switch ($action) {
            case 'modifier':
                $actionT = ['texte'=> 'Modifier le titre','logoClass' =>'fa-regular fa-pen-to-square','href'=>'song;editer'];
                break;
            case 'supprimer':
                $actionT = ['texte' => 'Supprimer le titre','logoClass' => 'bi bi-trash',
                    'modale'=>'song;supprimer','message' => 'Supprimer définitivement'];
                break;
            Case 'valider' :
                $actionT = ['texte' => 'Valider pour concert','logoClass' => 'fa-solid fa-microphone',
                    'modale'=>'song;valider','message' => 'Intégrer ce titre dans la playlist'];
                 break;
            Case 'retester' :
                $actionT = ['texte' => 'Retester', 'logoClass'=>'fa-solid fa-vial-circle-check',
                    'modale'=>'song;tester','message' => 'Remettre en test'];
                break;
            Case 'reprendre' :
                $actionT = ['texte' => 'Reprendre en concert','logoClass' => 'fa-solid fa-microphone',
                    'modale'=>'song;valider','message' => 'Remettre ce titre dans la playlist'];
                break;
            case 'abandonner' :
                $actionT= ['texte' => 'Abandonner', 'logoClass'=>'fa-solid fa-microphone-slash',
                    'modale'=>'song;abandonner','message' => 'Ce titre ne sera plus joué par le groupe ?'];
                break;
            case 'reserver':
                $actionT = ['texte' => 'Mettre en réserve', 'logoClass'=>'fa-solid fa-snowflake',
                    'modale'=>'song;reserver','message'=>'Sortir de la liste des titres joués en concert? <br>Attention ce titre sera supprimé de toutes les playlists des concerts à venir!'];
                break;
        }
 
        //ajout général
        $listeA[]= $actionT;
    }
    return $listeA;
}
