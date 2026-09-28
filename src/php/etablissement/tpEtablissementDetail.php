<?php 
declare(strict_types=1);
namespace theBand\src\php\etablissement;
/*******************************************************************************
 * affichage saisie établissement
 *
 * @author Alara
 *******************************************************************************/
use shared\php\toolbox\Toolbox_liste        as TbListe;
use shared\php\classes\lien\Lien            as Lien;
use shared\php\classes\lien\Lienhtml        as Lienhtml;
use shared\php\toolbox\Toolbox_adressage    as TbAdressage;
use shared\php\toolbox\Toolbox_html         as TbHtml;
ob_start(); 

?>

<br><br<br>
<form method="post" enctype="multipart/form-data" action="index.php?action=etabMAJ">
    <section class="py-5">
        <div class="container">
            <!--Identifiants etab -->  
            <input type="hidden"  name="idEtab" id="idEtab" value="<?= $etab->id ?>" />
            <!-- Nom et coordonnées -->  
            <div class="row">
                <!-- Type de titre --> 
                <div class="col">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="titreEtab">Etablissement *</span>
                        <input type="text" name="nomEtab" class="form-control" placeholder="Nom " 
                               aria-label="titre" aria-describedby="titreEtab"
                               value="<?= $etab->nom ?>"/>
                    </div>
                </div>
                <div class="col-3">
                    <div class="input-group mb-3">
                        <label class="input-group-text" for="inputTypeEtab">Catégorie * </label>
                        <select name="typeEtab" class="form-select" id="inputTypeEtab" required>
                          <?= TbListe::valeursChoixListe($typesEtab,true, selected : $etab->idTypeEtab); ?>
                        </select>
                    </div>
                </div>
  
            </div>
            <div class="row">
                <div class="col-8">
                    <div class="input-group ">
                        <input type="hidden"  name="idCoordonnee" id="idCoordonnee" value="<?= $etab->coordonnee->id ?>" />
                        <span class="input-group-text" id="basic-addon1">Adresse et ville *</span>
                        <input type="text" name="adresseEtab" required class="form-control" 
                               placeholder="1 rue du quai" aria-label="titre" aria-describedby="basic-addon1"
                               value="<?= $etab->coordonnee->adresse ?>"/>
                        <input type="text" name="villeEtab" required class="form-control" 
                               placeholder="34000 Montpellier" aria-label="titre" 
                               aria-describedby="basic-addon1" 
                               value="<?= $etab->coordonnee->ville ?>"/>
                    </div>
                </div>
            </div>
            <br>
            <!---- Contacts ---->
            <div class="row">
                <div class="col-12">
                    <div class="row table-responsive">
                        <table  class="table table-striped table-bordered">
                            <thead class="table-light table align-middle">
                                <tr>
                                    <th class="text-center">
                                        <h5><i class="fa-regular fa-address-book" alt="Ajouter une ligne de contactss" onclick="jsListeAjout('contact')"></i>
                                        </h5>
                                    </th>
                                    <th class="col-md-3 text-center">Nom Prénom</th>
                                    <th class="col-md-2 text-center">Portable</th>
                                    <th class="col-md-3 text-center">Mail</th>
                                    <th class="col-md-3 text-center">Commentaire</th>
                                </tr>
                            </thead>
                            <tbody id="contacts">
                                <!-- Ligne modèle --> 
                                <?= genererLigneContact(Lien::MODELE_LIGNE,null); ?>
                                <!-- Lignes existantes --> 
                                <?= afficherContactsExistants($etab->contacts); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
              
            </div>
            <!-- Liens avec colonne description--> 
            <?= Lienhtml::afficherBlocLien ($typesLien,$etab->liens);?>
            <!-- Commentaires --> 
            <div class ="row">
                <div class="col">
                    <div class="input-group">
                        <input type="hidden"  name="idCommentaire" id="idCommentaire" value="<?= $etab->commentaire->id ?>" />
                        <span class="input-group-text">Commentaire</span>
                        <textarea class="form-control" name="texteCommentaire" id="commentaire" rows="4" 
                                  placeholder="Commentaire général sur l'établissement"><?= $etab->commentaire->texte ?></textarea>
                    </div>
               </div>
            </div>
        </div>
    </section>
     <!-- Soumission formulaire -->
    <?= TbHtml::htmlBoutonValidation()?>
</form>
<?php 
    $content = ob_get_clean();
    require(TbAdressage::projetGetLayout(__NAMESPACE__));?>

<?php 

function genererLigneContactEntete(int $indice, int $idContact, int $idCommentaireContact = 0, int $idCoordonneeContact=0 ): string{

    $myhtml = "<tr id=\"contact" . $indice ."\"" ;
    if ($indice==Lien::MODELE_LIGNE){$myhtml.= " hidden ";}
    $myhtml .= ">";
    // colonne caché avec Id du contact et action faite dessus apportée
    $myhtml .= "<td hidden>" .
                "<input type=\"text\" id=\"idContact" . $indice . "\"  name=\"idContact" . $indice . "\" value=\"" . $idContact . "\">" .
                "<input type=\"text\" id=\"idCommentaireContact" . $indice . "\"  name=\"idCommentaireContact" . $indice . "\" value=\"" . $idCommentaireContact . "\">" .
                "<input type=\"text\" id=\"idCoordonneeContact" . $indice . "\"  name=\"idCoordonneeContact" . $indice . "\" value=\"" . $idCoordonneeContact . "\">" .
                "<input type=\"text\" id=\"actionContact" . $indice . "\" name=\"actionContact" . $indice . "\" value=\"\">" .
                "</td>";
    //colonne suivante action suppresssion de la ligne ou téléchargement
    $myhtml .= "<td><div class=\"btn-group text-center\" role=\"group\" >"
            . "<button type=\"button\" onclick=\"jsListeSuppression(". $indice . ",'contact')\"  class=\"btn btn-sm mb-2\">"
            . "<span class=\"bi bi-trash3-fill fa-lg\" title=\"Supprimer\" alt=\"Supprimer\" />"
            . "</button></div></td>";
    return $myhtml;
}

function genererLigneContactGetValue($contact,array $zones, $defaut=""){
    if (is_null($contact))
        return $defaut;
    else {
        $obj= $contact;
        foreach ($zones as $zone){
            $obj = $obj->$zone;
        }
        return $obj;
    }
}
function genererLigneContact(int $indice, $contact ): string{
    //Génère une ligne de contact
    //En cas de modification du type de contact ou du contenu du contact, le js contactModification(ligne) est appelé 
    // en cas de suppression (clic sur poubelle) le contact contactSuppression  est appelé
    $myhtml = genererLigneContactEntete($indice,genererLigneContactGetValue($contact,["id"],0),
                                genererLigneContactGetValue($contact,["commentaire","id"],0),
                                genererLigneContactGetValue($contact,["coordonnee","id"],0));
    //Nom et prénom
    $myhtml .= genererLigneContact1($indice,"nomPrenomContact",genererLigneContactGetValue($contact,["nomPrenom"]),"Nom prénom ou accueil si général");
    //portable
    $myhtml .= genererLigneContact1($indice,"telCoordonneeContact",genererLigneContactGetValue($contact,["coordonnee","tel"]));     
    //Mail 
    $myhtml .= genererLigneContact1($indice,"mailCoordonneeContact",genererLigneContactGetValue($contact,["coordonnee","mail"]),"","email");
    //commentaire               
    $myhtml .= genererLigneContact1($indice,"texteCommentaireContact",genererLigneContactGetValue($contact,["commentaire","texte"])) . "</tr>";
     
     
     return $myhtml;
}
function genererLigneContact1(int $indice,string $zone, string $valeur, string $placeholder = "", string $typeInput = "text"){
    // nb utilisation input car se déclanche des que modif clavier ou copié collé
    return "<td><input type=\"" . $typeInput . "\" name=\"" . $zone . $indice . "\" class=\"form-control\" " .
                " id=\"". $zone . $indice. "\" value=\"" . $valeur . "\"" .
                "placeholder=\"" . $placeholder . "\"" .
                " oninput=\"jsListeModification(" . $indice . ",'contact')\">" . 
                "</td>";
}

function afficherContactsExistants(array $contacts = []) :string {
    // type lien succession de tableau igGenre, Genre, lien
    $myHtml="";
    $indice=0;
    foreach($contacts as $contact){
        $indice += 1;
        $myHtml .= genererLigneContact($indice,$contact);
    }
    return $myHtml ;
}

