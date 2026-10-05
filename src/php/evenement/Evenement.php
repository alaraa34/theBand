<?php
declare(strict_types=1);
namespace theBand\src\php\evenement;
/*******************************************************************************
 * Classe de pilotage des deux types d'évènements, concerts et répétition
 *
 * @author Alara
 *******************************************************************************/

use shared\php\database\Model                      as Model;
use shared\php\toolbox\Toolbox_classe              as TbClasse;
use shared\php\classes\socle\Mere                  as Mere;
use shared\php\classes\socle\Commentaire           as Commentaire;
use shared\php\classes\socle\Coordonnee            as Coordonnee;
use theBand\src\php\etablissement\Etablissement    as Etablissement;
use theBand\src\php\song\Setlist                   as Setlist;
use theBand\src\php\socle\UserTheBand              as User;

abstract class Evenement extends Mere
{
    // Attributs
    public string $dateHeure="";
    public string $dateHeureFin="";
    public bool $confirme = false;
    public Etablissement $etablissement;
    public Commentaire $commentaire;
    public Setlist $setlist;
    // Constantes
    public const TABLE  =  PREFIXE_BDD . "evenement";
    public const SAUF =['id','confirme'];  
    public const INCLUDE_CRUD =["commentaire","setlist"];
    
    //Constantes liste
    public CONST TOUS = 1;
    public CONST PROCHAIN = 2;
    public CONST A_SUPPRIMER = 3;
    public CONST PRECEDENT = 4;

    // Méthodes
    public function __construct(int $id=0) {

        $this->etablissement = new Etablissement();
        $this->commentaire = new Commentaire();
        $this->setlist = new Setlist();
        if ($id>0){
            $this->id = $id;
            $this->loadFromId();
        }
    }
    //------------------------------------------------------------------------------------------------
    //STATIC
    //-----------------------------------------------------------------------------------------------
    public static function getIdProchain():int{
        $ids =  self::mdEvenementGetListeID(self::PROCHAIN);
        if (count($ids)===0){
            return 0;
        }
        else{
            return $ids[0];
        }
    }
    public static function evenementEtablissementGetListeID(int $idEtablissement) :array{
        return self::mdEvenementEtablissementGetListeID($idEtablissement);
    }
    
    public static function getIdPrecedent():int{
    //dernier évènement de la nature appelante antérieur à la date du jour
        $ids =  static::mdEvenementGetListeID(self::PRECEDENT);
        return count($ids)===0 ? 0 : (int)$ids[0];
    }

    public static function getIdSetListProchainEvt() :int{
        return static::getIdSetListEvt(static::getIdProchain());
    }

    public static function getIdSetListPrecedentEvt() :int{
        return static::getIdSetListEvt(static::getIdPrecedent());
    }

    protected static function getIdSetListEvt(int $idevt) :int{
    //setlist d'un évènement de la nature appelante, 0 si pas d'évènement
        if ($idevt==0){
            return 0;
        }
        $evt = new static($idevt);
        return (int)$evt->getIdSetList();
    }
    public static function getListe(int $type){
    //retourne une liste d'instance évènements
        $ids =  self::mdEvenementGetListeID($type);
        $evts=[];
        foreach ($ids as $id){
            //nom de la classe actuelle
            $classe = get_called_class();
            $evts[] = new $classe($id);
        }
        return $evts;
    }
    //------------------------------------------------------------------------------------------------
    //CRUD
    //-----------------------------------------------------------------------------------------------
        
    #[\Override]
    protected function classeValeurProprietes():array{
    //pour mise à jour classe sans confirme car mis à jour ailleurs
        $info = $this::SAUF;
        $tableau = TbClasse::classeValeurProprietes($this);
        $tableau['idNature'] = static::NATURE;
        return $tableau;
    }
    
    public function majConfirme(){
    //passe le top confirme à true
        return Model::mdUpdate(self::TABLE, ["confirme"=>true], "id=".$this->id);
    }
    
    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------
    
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    function getDate():string {
        //met en forme la date
         if (strlen($this->dateHeure)==0){
             return date("Y-m-d");
         }
        else{
           return date_format(date_create($this->dateHeure),"Y-m-d");
        }
    }
    function getHeure():string {
        //met en forme la date
         if (is_null($this->dateHeure) || strlen($this->dateHeure)==0){
            return "19:00";
         }
        else{
           return date_format(date_create($this->dateHeure),"G:i");
        }
    }
    function getTagConfirme(){
        if ($this->confirme){
            $couleur = "bg-success";
            $texte = "Confirmé";
        }
        else{
            $couleur = "bg-danger";
            $texte = "Non confirmé";
        }
        return "<span class=\"position-absolute top-0 start-100 translate-middle badge rounded-pill " . $couleur . "\">" .
               $texte.  "<span class=\"visually-hidden\">Confirmation réservation salle ou concert</span> </span>";
        
    }
    public static function supprimerEvtsAnciens(){
    //supprime les evts anciens si il y a un user con,necté
        if (User::userConnecte()){
            $evts = static::mdEvenementGetListeID(static::A_SUPPRIMER);
            foreach ($evts as $evt){
                //nom de la classe actuelle
                $classe = get_called_class();
                $evtobj = new $classe($evt);
                $evtobj->delete();
                unset($evtobj);
            }
        }
    }
    public function getIdSetList(){
    //retourne la setlist d'un evt.Si pa chargée dék) on va ma chercher
        if ($this->setlist->id===0){
            $evt = $this->mdEvenementDetailSimple();
            return (int)($evt['idSetlist'] ?? 0);
        }
        else{
            return $this->setlist->id;
        }
    }
    //------------------------------------------------------------------------------------------------
    //MODELE
    //-----------------------------------------------------------------------------------------------
    protected static function mdEvenementGetListeID(int $typeDemande=0):array{
    //restition de tous les concerts ou seulement du prochain
    //type de demande constantes
        $requete = "SELECT eve.id FROM ". self::TABLE ." as eve
            WHERE eve.idNature =  " . static::NATURE ;
        switch ($typeDemande){
            case self::TOUS :
                $requete .= " ORDER BY dateHeure DESC;";
                break;
            case self::PROCHAIN :
                //Première occurences des répétition à venir par ordre croissant
                $requete .= " AND dateHeure >= DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY)  ORDER BY dateHeure limit 1";    
                break;
            case self::A_SUPPRIMER :
                //occurences de plus d'une certaine période pour suppression auto
                $requete .= " AND dateHeure < DATE_ADD(CURRENT_DATE(), INTERVAL -" . static::DELAI_SUPPRESSION_MOIS  ." MONTH)";
                break;
            case self::PRECEDENT :
                //dernière occurence antérieure à la date du jour
                $requete .= " AND dateHeure < CURRENT_DATE() ORDER BY dateHeure DESC limit 1";
                break;
        }
             
        //retour résultat
        return Model::mdRequeteListerZoneUnique($requete,"id");
    }
    protected static function mdEvenementEtablissementGetListeID(int $idEtablissement):array{
    //Liste de tus les évènements liés à un établissement
        $requete = "SELECT eve.id FROM ". self::TABLE ." as eve
            WHERE eve.idNature =  ? AND idEtablissement = ?";
        //retour résultat
        return Model::mdRequeteLister($requete,[static::NATURE,$idEtablissement]);
    }
    protected function mdEvenementDetail():array{
    //restition d'un évènement
        $requete = "SELECT eve.id as id, dateHeure,dateHeureFin,confirme,
                        idEtablissement, eta.nom as nomEtablissement,
                        idSetlist, stl.nomListe as nomSetlist,
                        com.id as idCommentaire, texte as texteCommentaire,
                        coo.id as idCoordonneeEtablissement, adresse as adresseCoordonneeEtablissement,ville as villeCoordonneeEtablissement
            FROM ". self::TABLE ." as eve INNER JOIN (". Etablissement::TABLE ." as eta LEFT JOIN ". Coordonnee::TABLE ." as coo ON eta.idCoordonnee = coo.id) ON eve.idEtablissement = eta.id
                           INNER JOIN ". Setlist::TABLE ." as stl ON eve.idSetlist = stl.id
                           LEFT JOIN ". Commentaire::TABLE ." as com ON eve.idCommentaire = com.id
            WHERE eve.id=?;";
        //retour résultat
        return Model::mdRequeteListerUnique($requete ,[$this->id]);
    }
    protected function mdEvenementDetailSimple():array{
    //restition d'un évènement
        $requete = "SELECT eve.id as id, dateHeure,dateHeureFin,confirme,idEtablissement,
                        idSetlist, stl.nomListe as nomSetlist,
                        com.id as idCommentaire, com.texte as texteCommentaire
            FROM ". self::TABLE ." as eve INNER JOIN ". Setlist::TABLE ." as stl ON eve.idSetlist = stl.id
                           LEFT JOIN ". Commentaire::TABLE ." as com ON eve.idCommentaire = com.id
            WHERE eve.id=?;";
        //retour résultat
        return Model::mdRequeteListerUnique($requete ,[$this->id]);
    }
}



