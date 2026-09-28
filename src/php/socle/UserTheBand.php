<?php
declare(strict_types=1);
/*******************************************************************************
 * Classe qui spécifie une extension de la classe user
 * pas d'héritage car c'est une autre table
 ******************************************************************************/
namespace theBand\src\php\socle;

use shared\php\classes\socle\User        as User;
use shared\php\toolbox\Toolbox_classe    as TbClasse;
use shared\php\database\Model            as Model;

class UserTheBand extends User{
    public float $coeff=1;
    public int $role=0;
    public int $idSetlist=0;
   
 
    //Rôles
    public const MUSICIEN = 25;
    public const AUTRE = 26;
    
     //Constantes
    public CONST TABLE = PREFIXE_BDD . "user_theband";
    private const string JOINTURE = User::TABLE . " as us INNER JOIN " . self::TABLE . " as ustb ON us.id=ustb.id";
    
    //Méthodes
    public function __construct($id = 0) {
        parent::__construct($id);
        $infos = $this->mdInfosDetail();
        TbClasse::classeLoadFromArray($this,$infos);
    }
    //------------------------------------------------------------------------------------------------
    //Méthodes statiques publiques
    //-----------------------------------------------------------------------------------------------
   
    public static function listeMusiciens(){
        return self::liste(self::MUSICIEN);
    }

    #[\Override]
    public static function listeTous():array{
    //tous les utilisateurs qui ont des données groupe (planning collectif)
        return self::liste();
    }

    public static function listeRoles():array{
    //rôles possibles dans le groupe, pour une liste de choix
        return [['identifiant'=>self::MUSICIEN, 'zone'=>'Musicien'],
                ['identifiant'=>self::AUTRE,    'zone'=>'Autre'],
                ['identifiant'=>0,              'zone'=>'Aucun']];
    }

    public static function libelleRole(int $role):string{
        foreach (self::listeRoles() as $item){
            if ($item['identifiant'] === $role){return $item['zone'];}
        }
        return "Aucun";
    }
       
    //************************************************************************************************
    //CHARGEMENT
    //************************************************************************************************            
    
    //************************************************************************************************
    //METIER
    //************************************************************************************************    
    public function majDonneesGroupe():bool{
    //met à jour (ou crée) uniquement la ligne de tb_user_theband de l'utilisateur
        $requete = "INSERT INTO " . self::TABLE . " (id, role, coeff, idSetlist) VALUES (:id, :role, :coeff, :idSetlist)
                    ON DUPLICATE KEY UPDATE role = VALUES(role), coeff = VALUES(coeff), idSetlist = VALUES(idSetlist)";
        return Model::mdRequeteExecuter($requete, ['id'        => $this->id,
                                                   'role'      => $this->role,
                                                   'coeff'     => $this->coeff,
                                                   'idSetlist' => $this->idSetlist]);
    }

    public static function supprimerDonneesGroupe(int $idUser):bool{
    //supprime la ligne de tb_user_theband d'un utilisateur (lors de la suppression de l'utilisateur)
        return Model::mdDelete(self::TABLE, ['id' => $idUser]);
    }

    public static function clauseSelect(){
        return "SELECT us.*, ustb.coeff,ustb.role, ustb.idSetlist FROM " . self::JOINTURE ;
    }
    
    //************************************************************************************************
    //MODELE 
    //************************************************************************************************   
    #[\Override]
    public function mdInfosDetail():array{
    //infos de la table de la classe (LEFT JOIN : un utilisateur sans données groupe est quand même chargé)
        $requete = "SELECT us.*, ustb.coeff, ustb.role, ustb.idSetlist
                    FROM " . User::TABLE . " as us LEFT JOIN " . self::TABLE . " as ustb ON us.id=ustb.id
                    WHERE us.id=?";
        return Model::mdRequeteListerUnique($requete, [$this->id]);
    }
    
    public static function listePourAdministration():array{
    //tous les utilisateurs avec leurs données groupe (vides si pas encore renseignées)
        $requete = "SELECT us.id, us.nom, us.prenom, us.abrev, us.actif,
                           ustb.role, ustb.coeff, ustb.idSetlist
                    FROM " . User::TABLE . " as us LEFT JOIN " . self::TABLE . " as ustb ON us.id=ustb.id
                    ORDER BY us.nom, us.prenom;";
        return Model::mdRequeteLister($requete);
    }

    private static function liste(int $role = 0) :array{
    //renvoie la liste des users ayant le role cité
         //Retourne un tableau des users du role concernée
        //25 les zicos seulement
        $requete =  self::clauseSelect();
        if ($role > 0){$requete .= " WHERE ustb.role = " . $role;}
        $requete .= " ORDER BY us.abrev;" ;
        return Model::mdRequeteLister($requete);
    }
}

