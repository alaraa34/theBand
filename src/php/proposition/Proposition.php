<?php
declare(strict_types=1);
namespace theBand\src\php\proposition;
/*******************************************************************************
 * 
 ******************************************************************************/
use shared\php\classes\personalisation\Parametre    as Parametre;
use shared\php\toolbox\Toolbox                      as Tbx;
use shared\php\toolbox\Toolbox_classe               as TbClasse;
use shared\php\database\Model                       as Model;
use shared\php\classes\socle\Commentaire            as Commentaire;
use theBand\src\php\song\Song                       as Song;
use theBand\src\php\song\Repertoire                 as Repertoire;
use shared\php\classes\socle\User                   as User;
use theBand\src\php\socle\UserTheBand               as UserTB;


class Proposition extends Song
{
    // Attributs
    public int $statut=0;
    public array $votes =[];
    public User $user;
    public Commentaire $commentaire;

    // Constantes
    
    public const TABLE  =  PREFIXE_BDD . "proposition";
    //types de lien admis : table <prefixe>lien_type_usage, colonne sujet
    public const string SUJET_LIEN = "PROPOSITION";
    
    public const array INCLUDE_CRUD =["commentaire"];    //Classes à inclure lors d'une mise à jour ajout/suppression/modif
    public const array RESTREINT =[];                                 //Classes à ne pas charger lors d'un load by id
    public const array SAUF =['id'];                                  //A implémenter zone à exclure pour les mises à jour, minima Id
    //Statuts
    public const int ENCOURS = 17;
    public const int VALIDE = 18;
    public const int ABANDON = 19;
    //etat saisie
    private const int TOUT_OUVERT =4; //Saisie proposition et votes ouverts
   
    // Méthodes
    public function __construct(int $idProp = 0) {
        //pas besoin d'instancier la classe parent, les infos se chargent dans la load
        $this->id = $idProp;
        $this->commentaire = new Commentaire();
        $this->user = new User();
        $this->idTypeSong = Song::TYPE_PROPOSITION; //par défaut , écrasé si pas çà
        if ($this->id > 0){
            $this->loadFromId();
        }
    }
    //------------------------------------------------------------------------------------------------
    //STATIC
    //-----------------------------------------------------------------------------------------------
    public static function listeParVotesElimines(){
    //en cours avec une note préférence 
        return self::mdPropositionGetListeElimines();
    }
    public static function listeParVotes():array{
    //la liste des propositions par votes ne peut se faire que pour les encours
        return self::mdPropositionGetListe(self::ENCOURS," total DESC" );
    }
    public static function listeParTitres(int $statutProp):array{
        return self::mdPropositionGetListe($statutProp," titre ASC" );
    }
    public static function listePourVote(int $idUser , int $mode=Vote::TOUS_VOTES) {
        if ($mode=== Vote::TOUS_VOTES){
            return self::mdPropositionGetListePourVoteTousVotes($idUser);
        }
        else{
            return  self::mdPropositionGetListePourVoteNonVote($idUser);
        }
    }
    public static function saisieOuvert(int $identifiant = 0) {
    //si l'identifiant n'est pas nul renvoie true car modif toujours possible sur une proposition existante
    //paramètre identifiant proposition
        if ($identifiant === 0){
            return self::testFonction(2);
        }
        else{return true;}
    }
    public static function voteOuvert() {
        return self::testFonction(1);
    }
    private static function testFonction(int $fonction) : bool{
    //paramètre voter 4 tout ouvert, 1 vote ouvert, 2 saisie ouverte, 3 tout fermé
        $statut = (int)Parametre::mdParametreGetDetail("PROPOSITIO", "VOTER", "valeurN","P");
        //si tout est ouvert ou la fonction testée est ouverte
        if ($statut == self::TOUT_OUVERT or $statut ===$fonction){
            return true;
        }
        else{return false;}
    }
    
    //------------------------------------------------------------------------------------------------
    //CRUD
    //-----------------------------------------------------------------------------------------------
    #[\Override]
    protected function add():bool {
        //ajout de la song avec parent update pour mettre les liens à jour
        $retour = parent::add();
        TbClasse::classeLoadFromArray($this->user,User::getUserConnecte());   //chargement du user connecté
        //Ajoute seulement la classe proposition, on veut l'id en retour zones, seulement statut et idCommentaire
        if($retour){$retour = Model::mdInsert( self::TABLE , TbClasse::classeFilleValeurProprietes($this));}
        //L'id est déjà connu
        return $retour;
    }

    #[\Override]
    public function update() : bool {
    //Mise à jour de la proposition
        //Commentaire
        TbClasse::classeUpdateClassesLiees($this);
        
        if ($this->id === 0) {
           //ajout de la proposition
           $retour = $this->add();
           //mise à jour des collections après car besoin de l'ID  et pas fait dans add classe parente
            if(! TbClasse::classeUpdateCollection($this)){$retour = false;}
        } 
        else{
            //id renseigné, mise à jour table mère (Song)
            $retour = parent::update();
            //mise à jour proposition pas d'id dans les zones 
            if($retour){$retour= Model::mdUpdate( self::TABLE, TbClasse::classeFilleValeurProprietes($this,[]), "id=" . $this->id);}
        }
        return $retour;
    }

    #[\Override]
    public function delete():bool {
    //suppresssion d'une proposition
        
        if ($this->idTypeSong != Song::TYPE_PROPOSITION){
            //Une proposition ne peut être supprimée que si c'est une proposition
            $retour = false;
        }
        elseif ($this->id > 0) {
            //suppression du commentaire
            $retour = $this->commentaire->delete();
            //suppression des votes
            if($retour){$retour = Model::mdDelete(Vote::TABLE, ['idSong' =>$this->id]);}
            //Suppression table proposition
            if($retour){$retour = Model::mdDelete(self::TABLE, ['ID' => $this->id]);}
            //suppression song et liens
            if($retour){$retour=parent::delete();}
        }
        return $retour;
    }
    public static function deleteFromId(Repertoire $song):bool {
    //suppression des infos propres aux propositions sur l'id sans instancier les classes
        $retour = true;
        //commentaire de la proposition
        $tableau = Model::mdRequeteListerUnique("select idCommentaire from " . self::TABLE . " where id=?",[$song->id]);
        if (count($tableau)> 0){
            $retour = Model::mdDelete(Commentaire::TABLE, ['ID' =>$tableau['idCommentaire']]);
        }
        //suppression des votes
        if($retour){$retour =Model::mdDelete(Vote::TABLE, ['idSong' =>$song->id]);}
        //Suppression table proposition
        if($retour){$retour = Model::mdDelete(self::TABLE, ['ID' =>$song->id]);}     
        return $retour;
    }
    
    //------------------------------------------------------------------------------------------------
    //CHARGEMENT
    //-----------------------------------------------------------------------------------------------
    
    public function loadFromArray(array $infos):void{
    //Charge l'instance via un tableau de données
        //Identifiant
        TbClasse::classeLoadFromArray($this,$infos);
    }
    private function loadFromId():void{
        $infos = $this->mdPropositionDetail();
        $this->loadFromArray($infos);
        //chargement texte car seul id chargé par procédure générique
        $this->commentaire->texte = Tbx::afficherData($infos,'texteCommentaire');
        //Chargement des liens
        $this->loadLiens();
        //chargement des votes
        $this->loadVotes();
    }
    public function loadFromArrayAlias(array $infos,$alias=""):void{
    //Charge l'instance via un tableau de données
        //Identifiant
        TbClasse::classeLoadFromArrayAndAlias($this,$infos,$alias);
        $this->commentaire->loadFromArrayAlias($infos); 
    }
    
    public function loadVotes(int $idUser=0){
    //Charge les votes de la proposition
        $votes  = $this->mdPropositionGetVotes($idUser);
        foreach($votes as $vote){
            $objvote = new Vote();
            TbClasse::classeLoadFromArray($objvote,$vote);
            TbClasse::classeLoadFromArray($objvote->user, $vote,"User");
            $this->votes[] = $objvote;
            unset ($objvote);
        }
    }
    //------------------------------------------------------------------------------------------------
    //METIER
    //-----------------------------------------------------------------------------------------------
    public function total(int $preferenceMaitrise){
        if($preferenceMaitrise==Vote::MAITRISE){
            return $this->totalMaitrise(1);
        }
        else{
            return $this->totalPreference(1);
        }
    }
    private function totalPreference(int $decimalesArrondi) :float {
    //totalise les préférences 
    //rdg les notes non exprimées ne comptent pas
        (float)$total = 0;
        (float)$ctr = 0;
        foreach($this->votes as $vote){
            if ($vote->preference > 0){
                $total+= $vote->preference;
                $ctr+=1;
            }
        }
        //calcul de la moyenne arrondie ou pas
        if ($ctr==0){return 0;}else{
        return  round($total / $ctr,$decimalesArrondi,PHP_ROUND_HALF_UP);}
    }
  
    private function totalMaitrise(int $decimalesArrondi) :float {
    //totalise les préférences
        (float)$total = 0;
        (float)$ctr = 0;
        foreach($this->votes as $vote){
            //0 = vote no exprimé, ne compte pas
            if($vote->maitrise > 0) {
                $total+= $vote->maitrise * $vote->user->coeff;
                $ctr+=$vote->user->coeff;
            }
        }
        //calcul de la moyenne arrondie ou pas
        if ($ctr==0){return 0;}else{
        return  round($total / $ctr,$decimalesArrondi,PHP_ROUND_HALF_UP);}
    }
    public function totalGeneral() :float {
    //totalise les votes pref et maitrise
        return round($this->totalMaitrise(4) + $this->totalPreference(4),2,PHP_ROUND_HALF_UP);
    }
        
    public function getVoteUserObject(int $objet , string $abrev) : int{
    //recherche parmi les votes celui qui correspond à l'objet et le user
    //Objet = aptitude ou appétence
    //utilisé par les templates
    $retour = 0;
    foreach ($this->votes as $vote) {
        if ($vote->user->abrev == $abrev){
            switch ($objet){
                case Vote::PREFERENCE :
                    $retour = $vote->preference;
                    break;
                case Vote::MAITRISE :
                    $retour = $vote->maitrise;
            }
            break;
        }
    }
    return $retour ;
}
    //------------------------------------------------------------------------------------------------
    //MODELE STATIQUE
    //-----------------------------------------------------------------------------------------------
    private static function mdPropositionGetListeElimines() {
    // Retourne la liste des propositions avec seul le lien site .
    //calcul des votes coeff 1 pour préférence et coeff pour maitrise
       
    $requete = "SELECT distinct pro.id as id, titre
                    FROM ". self::TABLE ." as pro INNER JOIN ". Song::TABLE ." as son ON pro.id = son.id
                                    INNER JOIN ". Vote::TABLE ." as vot ON pro.id = vot.idSong
                    WHERE pro.statut=? AND vot.preference=1
                    ORDER BY titre";
        return Model::mdRequeteLister($requete ,[self::ENCOURS]);
    }
    private static function mdPropositionGetListe(int $statut, string $zoneTri) {
    // Retourne la liste des propositions avec seul le lien site .
    //calcul des votes coeff 1 pour préférence et coeff pour maitrise
       
    $requete = "SELECT pro.id as id, titre,
                        (select (sum(vot.preference)/count(usb.id)) + (sum(vot.maitrise * usb.coeff)/sum(usb.coeff) )
                        FROM ". Vote::TABLE ." as vot INNER JOIN ". UserTB::TABLE ." as usb ON usb.id = vot.idUser
                        WHERE vot.preference > 0 and vot.idSong = pro.id) as total
                    FROM ". self::TABLE ." as pro INNER JOIN ". Song::TABLE ." as son ON pro.id = son.id
                    WHERE pro.statut=?
                    ORDER BY " . $zoneTri;
                
        return Model::mdRequeteLister($requete ,[$statut]);
    }
    
    private static function mdPropositionGetListePourVoteTousVotes($idUser) {
    // Retourne la liste des propositions eligibles au vote avec les votes du user connecté
    // tous votes c'est la même liste pour tout le monde
       $requete = "SELECT pr.id as id, so.titre, so.interprete,
                    (select ifnull(maitrise + preference,0) FROM ". self::TABLE ." as pro LEFT JOIN
                            (". Vote::TABLE ." as vot INNER JOIN ". User::TABLE ." as usb ON vot.idUser = usb.id ) ON pro.id = vot.idSong
                            WHERE usb.id=? AND pro.id = pr.id GROUP BY pro.id) as totalVoteProposition

                    FROM ". self::TABLE ." as pr INNER JOIN ". Song::TABLE ." as so ON pr.id = so.id
                    WHERE pr.statut = ? ORDER BY so.titre";
        return Model::mdRequeteLister($requete ,[$idUser,self::ENCOURS]);
    }
    
    private static function mdPropositionGetListePourVoteNonVote(int $idUser ) {
    // Retourne la liste des propositions eligibles au vote avec les votes du user connecté
    // requete = toutes le propal éligibles sauf celles où il y au un vote
    // ifnull remplace la valeur si null
       $requete = "SELECT pro.id as id, titre,interprete
                    FROM ". self::TABLE ." as pro INNER JOIN ". Song::TABLE ." as son ON pro.id = son.id
                    WHERE pro.statut = ? and pro.id not in(select pr2.id as id
                    FROM ". self::TABLE ." as pr2 INNER JOIN (". Vote::TABLE ." as vot INNER JOIN ". UserTB::TABLE ." as usb ON vot.idUser = usb.id ) ON pr2.id = vot.idSong
                    WHERE pr2.statut = ? and usb.id=?
                    AND (ifnull(coeff * maitrise + preference,0) > 0 OR maitrise=0 OR preference=0))
                    ORDER BY titre ;";
        return Model::mdRequeteLister($requete ,[self::ENCOURS,self::ENCOURS,$idUser]);       
     }
    //------------------------------------------------------------------------------------------------
    //MODELE CLASSE
    //-----------------------------------------------------------------------------------------------
    public function mdPropositionDetail() {
    // Retourne la liste des propositions avec seul le lien site .
        $requete = "SELECT son.id as id, statut, son.Titre as titre, son.Interprete as interprete,
                pro.idCommentaire, com.texte as texteCommentaire
                FROM ". Song::TABLE ." as son INNER JOIN ". self::TABLE ." as pro ON son.id = pro.id
                        LEFT JOIN ". Commentaire::TABLE ." as com ON pro.idCommentaire = com.id";
        $requete .= " WHERE pro.id=? ;";
        return Model::mdRequeteListerUnique($requete,array($this->id));
    }
    public function mdPropositionGetVotes(int $idUser){
    // Retourne la liste des votes pour une proposition .
    	$requete = "SELECT vot.id as id,vot.idSong as idProposition, vot.preference, vot.maitrise,
                    usb.id AS idUser, usb.coeff as coeffUser, usr.abrev as abrevUser " .
                    " FROM ". UserTB::TABLE ." as usb INNER JOIN ". Vote::TABLE ." as vot ON usb.id = vot.idUser " . 
                                                    " INNER JOIN ". User::TABLE ." as usr ON usb.id = usr.id " .
                    " WHERE vot.idSong=? ";
        if ($idUser > 0){$requete .= " AND usb.id=" .$idUser;}
        return Model::mdRequeteLister($requete .";" ,[$this->id]);
    }
    public function mdPropositionMajStatut(int $nouveauStatut){
    // met à jour le statut d'une proposition
        $retour = Model::mdUpdate(self::TABLE,["statut" => $nouveauStatut],"ID=" . $this->id);
        return $retour ;
    }
}



