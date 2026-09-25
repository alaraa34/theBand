<?php 
declare(strict_types=1);
namespace theBand\src\php\player;
/*******************************************************************************
 * Controleur des l'affichage des players MP3 et document
 *
 * @author Alara
 *******************************************************************************/


?>

<html lang="fr">  
<head>  
    <title><?= mdGetNomGroupe()?></title>  
    <meta charset="utf-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <!-- chargement des feuilles de style -->
    <link rel="stylesheet" href="src/css/theBand.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="src/css/style.css" type="text/css" media="screen" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css"/>
</head>  
<body class="bg-dark bg-gradient">  
    <div class="container">
        <input type="hidden" id="indice" value="0" />
        <input type="hidden" id="songs" value="<?= htmlspecialchars(json_encode($songs), ENT_QUOTES, 'UTF-8') ?>" />
        <div class="row vh-100">
            <!--<!-- Boutons précédent et fermeture (pas de précédent au 1er affichage)-->
            <div class="col-1 align-self-center">
                <button type="button" onclick="jsPlayerPrecedent()" class="btn btn-primary btn-block" data-toggle="tooltip" data-placement="top" title="Titre précédent">
                    <i class="bi bi-arrow-left-circle"></i>
                </button>
                <br>
                <span class="text-white" id="titrePrecedent"></span>
                <br><br>
                <button type="text" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Revenir à la page d'accueil">
                    <a href=<?=$urlControleur?>><i class="bi bi-x-circle"></i></a>
                </button>
            </div>
            
            <!-- affichage PDF ou message -->
            <div class="col-10 embed-responsive embed-responsive-1by1 align-self-stretch">
                <div class="alert alert-danger text-center" role="alert" id="message" <?= displayInitial($songs[0])?> ><?= messageInitial($songs[0])?></div> 
                <iframe id="lecteur" class="d-md-flex embed-responsive-item" src="<?=$songs[0]['lienPDF'] ?>" <?= displayDocInitial($songs[0])?>
                        width="100%" height="100%" allowfullscreen > </iframe>
            </div>
            
            <!--<!-- Boutons suivant et fermeture -->
            <div class="col-1 align-self-center">
                <button type="button" onclick="jsPlayerSuivant()" class="btn btn-primary btn-block" data-toggle="tooltip" data-placement="top" title="Titre suivant">
                        <i class="bi bi-arrow-right-circle"></i>
                </button>
                <br>
                <span class="text-white" id="titreSuivant"><?= valeurAffichableSiNull($songs[1]['titre'])?></span>
                <br><br>
                <button type="text" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Revenir à la page d'accueil">
                    <a href=<?=$urlControleur?>><i class="bi bi-x-circle"></i></a>
                </button>
            </div?
        </div>
    </div>
   <!-- chargement des scripts -->		
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script type="text/javascript" src="src/js/player.js"></script>
    <script type="text/javascript" src="src/js/global.js"></script>
    <script type="text/javascript" src="src/js/ajax.js"></script>
</body>
</html>
<?php 
function messageInitial(array $song){
//affiche un message pour le premier message
    $message = "";
    if(is_null($song['lienPDF'])){
        $message = "<h4>Pas de document disponible pour le titre <br>" . $song['titre'] . "</h4>";
    }
    return $message;
}
function displayInitial(array $song){
//met display none ou pas pour le message
    $message ="style=\"display:none\"";
    if(is_null($song['lienPDF'])){
        $message = "";
    }
    return $message;
}
function displayDocInitial(array $song){
//met display none ou pas pour le message
    $message = "";
    if(is_null($song['lienPDF'])){
        $message = "style=\"display:none\"";
    }
    return $message;
}
?>

  