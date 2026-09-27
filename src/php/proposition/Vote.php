<?php
declare(strict_types=1);
namespace theBand\src\php\proposition;
/**
 * Classe permettant de gérer les listes de vote
 *
 * @author Alara
 */

use theBand\src\php\socle\UserTheBand         as UserTb;
use shared\php\database\Model                 as Model;
use shared\php\classes\socle\Mere_instance    as MereInstance;

class Vote extends MereInstance{
    //put your code here
      // Attributs
   // Attributs
    public int $preference=0;
    public int $maitrise=0;
    public UserTb $user;
    
    // Constantes techniques
    public const TABLE  =  PREFIXE_BDD . "vote";
    public const CLE_EXTERNE = "idSong" ; //cle externe dans la table
    //
    //Constantes métiers
    public const TOUS_VOTES = 1;
    public CONST NON_VOTES = 2;
    
    public const PREFERENCE = 1;
    public const MAITRISE = 2;
    
    public const LIBELLE_MAITRISE = "0=Pas concerné ,1=Compliqué à 5=Facile"; 
    public const LIBELLE_PREFERENCE = "0=Pas voté ,1=A éliminer ,2=Nul à 5=Topissime ";
    
    // Méthodes
    public function __construct() {
        $this->user = new UserTb();
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
    public function rechercheID(int $idSong) :void{
    //Recherche de l'Id du vote si il existe car pas connu forcément à la saisie des votes
        if ($this->id ===0){
            $requete = "SELECT vot.id
                    FROM ". self::TABLE ." as vot
                    WHERE idUser= ? AND idSong = ? ";
                
            $tableau = Model::mdRequeteLister($requete ,[$this->user->id,$idSong]);
            if (count($tableau)> 0){$this->id = $tableau[0]['id'];}
        }
    }
}
