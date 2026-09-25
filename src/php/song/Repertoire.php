<?php
declare(strict_types=1);
/* **************************************************************************************
 * cette classe décrit un titre qui n'est pas une proposition
 * 
 ****************************************************************************/
namespace theBand\src\php\song;

use shared\php\database\Model                           as Model;
use shared\php\database\Model_utils                     as ModelU;
use shared\php\classes\socle\User                       as User;
use shared\php\classes\socle\Commentaire                as Commentaire;
use shared\php\classes\lien\Lienhtml                    as Lienhtml;
use shared\php\toolbox\Toolbox_classe                   as TbClasse;
use shared\php\classes\personalisation\Nomenclature     as Nomenclature;
use theBand\src\php\proposition\Proposition             as Proposition;


class Repertoire extends Song
{
    // Attributs
    public string $tonaOrigine="";
    public string $tonaScene="0";
    public int $tempo=0;
    public User $leadChant;
    public Commentaire $commentaire;
 
    // Constantes
     public const SUJET = 71; //song
   
     
    // Méthodes
    public function __construct(int $idSong = 0) {
        parent::__construct();
        $this->id = $idSong;
        $this->commentaire = new Commentaire();
        $this->leadChant = new User();
        $this->idTypeSong = self::TYPE_CONCERT;//par défaut
        if ($idSong > 0){$this->loadFromId();}
    }
 
    //------------------------------------------------------------------------------------------------
    //CRUD
    //-----------------------------------------------------------------------------------------------
    protected function add():bool {
    //Ajoute un enregistrement 
       
        //insertion 
        $retour = Model::mdInsert(self::TABLE , TbClasse::classeValeurProprietes($this));
        //récup Id
        if ($retour){$this->id = ModelU::mdGetMax(self::TABLE);}
     
        return $retour;
    }

    public function update() : bool {
    //met à jour  un repertoire et ses liens 
        //mise à jour commentaire
        $this->commentaire->update();
        //répertoire
        if ($this->id === 0) {
            //Id = 0 c'est un ajout
           $retour = $this->add();
        } 
        else{
            //id renseigné
            $retour = Model::mdUpdate(self::TABLE, TbClasse::classeValeurProprietes($this), "ID=" . $this->id);
        }
         //mise à jour des collections après car besoin de l'ID de la classe mère
        if($retour){$retour = TbClasse::classeUpdateCollection($this);}
          
        return $retour;
    }

    public function delete():bool {
        if ($this->id > 0) {
            $retour = $this->commentaire->delete();
            //suppression de la partie proposition si elle existe
            if ($retour){$retour = Proposition::deleteFromId($this);}
            //suppression song (repertoire)
            if($retour){$retour = parent::delete();}
            return $retour;
        }
    }

    //------------------------------------------------------------------------------------------------
    //STATIQUES
    //-----------------------------------------------------------------------------------------------
    public Static function getListe(array $types, bool $miseEnFormeLien = true){
    //utilisé pour liste des songs concert, test....
        $songs = self::mdSongGetListe($types);
        //tableau des liens avec ou sans mise  forme
        for($i=0;$i < count($songs); $i++){
            $songs[$i]['liens'] = SongLien_ass::getListe($songs[$i]['id']);
            if ($miseEnFormeLien) {
                //remplace les tableaux liens par leur mise en forme HTML
                $songs[$i]['liens'] = Lienhtml::mettreEnFormeCollection($songs[$i]['liens']);
            }
        }
        return $songs;
    }
    
    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------
    public function loadFromArray(array $infos):void{
    //Charge l'instance via un tableau de données
        //Identifiant
        tbClasse::classeLoadFromArray($this,$infos);
    }
    
    public function chargerLiensParMouvements(array $mvtsLiens):void{
    //Mets à jour la collection des liens sur la base des mouvements
    //ne traite pas la mise à jour physique sauf pour la suppression
        $this->liens = TbClasse::classeChargerCollectionParMouvements($this,$mvtsLiens, "shared\php\classes\lien\Lien");
    }
    
    private function loadFromId():void{
    // chargement par identifiant
    TbClasse::classeLoadFromId($this);
    }
    
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    
    //------------------------------------------------------------------------------------------------
    //MODELE
    //-----------------------------------------------------------------------------------------------
    private static function mdSongGetListe(array $typesSong) {
    // Retourne la liste des songs du répertoire en test ou concert. avec les liens mis en forme
        $requete = "SELECT so.ID as id, no.valeurA as typeSong, titre, interprete,tonaOrigine,tonaScene,tempo, 
                        co.texte as texteCommentaire ,us.abrev as leadChant,idLeadChant, co.id as idCommentaire
                    FROM ". Song::TABLE . " as so INNER JOIN ". Nomenclature::TABLE . " as no ON so.idTypeSong = no.id 
                            LEFT JOIN ". Commentaire::TABLE . " as co ON so.idCommentaire = co.id
                            LEFT JOIN ". User::TABLE . " as us ON so.idLeadChant = us.id
                    WHERE so.idTypeSong " . Model::mdClauseIn($typesSong). " ORDER BY so.idTypeSong,so.Titre;";
        return Model::mdRequeteLister($requete,[]);
    }
   
    //Autres fonctions
    function mdSongDetail() {
    //Retourne les infos sur une song
        $requete= "SELECT titre,interprete,tonaOrigine,tonaScene,tempo,idTypeSong,idLeadChant as idUser, 
                    idCommentaire, commentaire.texte as texteCommentaire 
                FROM ". Song::TABLE . " as so LEFT JOIN ". Commentaire::TABLE . " as co ON so.idCommentaire = co.ID WHERE so.ID= ?";
        return Model::mdRequeteListerUnique($requete,[$this->id]);
    }
    
    public function mdInfosDetail():array{
    //infos de la table de la classe
        $requete = "SELECT * FROM " . $this::TABLE. " WHERE id=?";
        return Model::mdRequeteListerUnique($requete, [$this->id]);
    }
    
}



