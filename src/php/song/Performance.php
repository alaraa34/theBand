<?php
declare(strict_types=1);
/* **************************************************************************************
 * cette classe décrit un titre qui n'est pas une proposition
 *
 ****************************************************************************/
namespace theBand\src\php\song;

use shared\php\database\Model                          as Model;
use shared\php\classes\socle\User                      as User;
use theBand\src\php\socle\UserTheBand                  as UserTB;
use shared\php\classes\socle\Mere                      as Mere;
use shared\php\toolbox\Toolbox_date                    as TbDate;
use shared\php\toolbox\Toolbox_classe                  as TbClasse;
use theBand\src\php\song\Song                          as Song;
use theBand\src\php\proposition\Vote                   as Vote;
use shared\php\classes\personalisation\Nomenclature    as Nomenclature;

class Performance extends Mere{
    //put your code here
      // Attributs
   // Attributs
    public int $groupe = 3;
    public int $perso = 3;
    public string $date = "";
    public UserTB $user;
    public Song $song;

    // Constantes techniques
    public const TABLE  =  PREFIXE_BDD . "vote";

    // Méthodes
    public function __construct() {
        $this->date = TbDate::dateDuJour();
        $this->user = new UserTB();
        $this->song = new Song();
    }
    //------------------------------------------------------------------------------------------------
    //STATIC
    //-----------------------------------------------------------------------------------------------
    public static function listePourVotePerformance() {
    // porte sur les titres Test et Concert
        //recherche des ID
        $infos = self::mdSongGetListeVotePerformance(array(Song::TYPE_CONCERT,Song::TYPE_TEST));
        $liste = [];
        foreach ($infos as $info){
            $performance = new Performance();
            TbClasse::classeLoadFromArray($performance, $info,"Performance");
            TbClasse::classeLoadFromArray($performance->song, $info,"Song");
            $liste[]= $performance;
            unset($performance);
        }
        return $liste;
    }
    public static function tableauMoyennePerformanceGroupe(){
        return self::mdSongGetListeTousVotesPerformance(array(Song::TYPE_CONCERT,Song::TYPE_TEST));
    }
    public static function tableauMoyennePerformancePerso(){
        return self::mdSongGetListeVotePerformancePerso(array(Song::TYPE_CONCERT,Song::TYPE_TEST));
    }
    //------------------------------------------------------------------------------------------------
    //CRUD
    //-----------------------------------------------------------------------------------------------

    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------

    //------------------------------------------------------------------------------------------------
    //MODELE
    //-----------------------------------------------------------------------------------------------

    public function rechercheID() :void{
    //Recherche de l'Id du vote si il existe car pas connu forcément à la saisie des votes
        if ($this->id ===0){
            $requete = "SELECT vot.ID
                    FROM ". Vote::TABLE ." as vot
                    WHERE idUser= ? AND idSong = ? ";

            $tableau = Model::mdRequeteLister($requete ,[$this->user->id, $this->song->id]);
            if (count($tableau)> 0){$this->id = $tableau[0]['ID'];}
        }
    }

    private static function mdSongGetListeVotePerformance(array $typesSong ) {
    // Retourne la liste des morceaux sur les type song avec les votes liés
        $requete = "SELECT son.ID as idSong, son.idTypeSong as idTypeSongSong,nom.valeurA AS typeSong,
                    titre as titreSong, interprete as interpreteSong, idUser,
                    vot.ID as idPerformance,  vot.groupe as groupePerformance,vot.date as datePerformance
                FROM ". Song::TABLE ." as son   LEFT JOIN ". Vote::TABLE ." as vot ON son.id= vot.idSong
                            INNER JOIN ". Nomenclature::TABLE ." as nom ON son.idTypeSong = nom.ID
                WHERE  son.idTypeSong ". Model::mdClauseIn($typesSong) . " AND (idUser=? or idUser is null)" .
                " ORDER BY son.Titre;";
        return Model::mdRequeteLister($requete,[(User::connectUserGetInfo("id",0))]);
    }

    private static function mdSongGetListeTousVotesPerformance(array $typesSong ) {
    // Retourne la liste des morceaux sur les type song avec les votes liés
        $requete = "SELECT titre , interprete, nom.valeurA AS typeSong,
                 avg(vot.groupe) as sommeGroupe
            FROM ". Song::TABLE ." as son   LEFT JOIN ". Vote::TABLE ." as vot ON son.id= vot.idSong
                        INNER JOIN ". Nomenclature::TABLE ." as nom ON son.idTypeSong = nom.ID
            WHERE son.idTypeSong ". Model::mdClauseIn($typesSong) . "
            GROUP BY son.titre
            having  sum(vot.groupe)>0
            ORDER BY avg(vot.groupe);";
        return Model::mdRequeteLister($requete);
    }

    private static function mdSongGetListeVotePerformancePerso(array $typesSong ) {
    // Retourne la liste des morceaux sur les type song avec les votes liés
        $requete = "SELECT titre , interprete, nom.valeurA AS typeSong, vot.perso
            FROM ". Song::TABLE ." as son   LEFT JOIN ". Vote::TABLE ." as vot ON son.id= vot.idSong
                        INNER JOIN ". Nomenclature::TABLE ." as nom ON son.idTypeSong = nom.ID
            WHERE son.idTypeSong ". Model::mdClauseIn($typesSong) . "
                    AND idUser = " . User::connectUserGetInfo("id") . "
            ORDER BY vot.groupe;";
        return Model::mdRequeteLister($requete);
    }

    public static function mdSongGetListeID(int $nb ) {
    // Retourne la liste des id pour nb moins bonnes perforamnces
        $typesSong = array(Song::TYPE_CONCERT,Song::TYPE_TEST);
        $requete = "SELECT  son.id  as idSong
            FROM ". Song::TABLE ." as son   LEFT JOIN ". Vote::TABLE ." as vot ON son.id= vot.idSong
            WHERE son.idTypeSong ". Model::mdClauseIn($typesSong) . "
            GROUP BY son.id
            having  sum(vot.groupe)>0
            ORDER BY avg(vot.groupe)
            LIMIT " . $nb . ";";
        return Model::mdRequeteListerZoneUnique($requete,"idSong");
    }

}
