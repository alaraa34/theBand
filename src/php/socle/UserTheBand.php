<?php
declare(strict_types=1);
/*******************************************************************************
 * Classe qui spécifie une extension de la classe user
 * pas d'héritage car c'est une autre table
 ******************************************************************************/
namespace theBand\src\php\socle;

use shared\php\classes\socle\User       as User;
use shared\php\toolbox\Toolbox_classe   as TbClasse;
use shared\php\database\Model           as Model;

class UserTheBand extends User{
    public float $coeff=0;
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
       
    //************************************************************************************************
    //CHARGEMENT
    //************************************************************************************************            
    
    //************************************************************************************************
    //METIER
    //************************************************************************************************    
    public static function clauseSelect(){
        return "SELECT us.*, ustb.coeff,ustb.role, ustb.idSetlist FROM " . self::JOINTURE ;
    }
    
    //************************************************************************************************
    //MODELE 
    //************************************************************************************************   
    #[\Override]
    public function mdInfosDetail():array{
    //infos de la table de la classe
        $requete = self::clauseSelect()." WHERE us.id=?";
        return Model::mdRequeteListerUnique($requete, [$this->id]);
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

