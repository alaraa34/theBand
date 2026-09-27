<?php
declare(strict_types=1);
/* **************************************************************************************
 * cette classe décrit un détail de setlist
 * 
 ****************************************************************************/
namespace theBand\src\php\song;


use shared\php\classes\socle\Mere_instance    as Mere_instance;

class Detail extends Mere_instance
{
    // Attributs
    public int $partie=0;
    public int $ordre=0;
    public bool $enchainement=false;
    public Repertoire $song;
 
    // Constantes
    public const ICONE_ENCHAINEMENT = "fa fa-link text-success fs-6";
    public const RAPPEL = 3;
    public const NON_AFFECTE = 0;
    
    public const TABLE  =  PREFIXE_BDD . "setlist_detail";
    public const CLE_EXTERNE = "idSetlist" ; //cle externe dans la table
    
    // Méthodes
    public function __construct(int $idDetail=0) {
        $this->song = new repertoire();
         if ($idDetail>0) {
            $this->id=$idDetail;
            $this->loadFromId();
        }
        
    }
    
    //------------------------------------------------------------------------------------------------
    //CRUD
    //-----------------------------------------------------------------------------------------------
         
    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------
    
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    
    //------------------------------------------------------------------------------------------------
    //MODELE
    //-----------------------------------------------------------------------------------------------
 
}



