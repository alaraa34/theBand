<!DOCTYPE html>  
<html lang="fr">  
<head>  
<title><?= tpLayoutGetNomGroupe()?></title>  
<meta charset="utf-8">  
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- application installable (PWA) : actif uniquement en https (production) -->
<link rel="manifest" href="index.php?ctr=accueil&amp;fct=manifest">
<meta name="theme-color" content="#b76533">
<link rel="apple-touch-icon" href="icons/icon-192.png">
<script>
    if ('serviceWorker' in navigator && location.protocol === 'https:') {
        navigator.serviceWorker.register('sw.js');
    }
</script>
<!-- chargement des feuilles de style -->
<link rel="stylesheet" href="src/css/theBand.css" type="text/css" media="screen" />
<link rel="stylesheet" href="src/css/style.css" type="text/css" media="screen" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css"/>
<?php if (isset($css)){echo $css;}?>
</head>  
<body>  

    <!--<nav class="navbar navbar-expand-lg bg-body-tertiary"> -->
    <!-- Blue background with white text -->
    <nav class="navbar navbar-expand-md navbar-dark navbar-custom sticky-top" >
        <div class="container-fluid">
            <img src="<?=tpLayoutUserImage();?>" alt="Avatar" style="width:40px;" class="rounded-pill"> 
            <p><?= tpLayoutGetNomGroupe(); ?></p>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <?=tpLayoutAfficherMenu();?>
                </ul>
                <div class=pull-right>
                    <a class="btn btn-dark btn-sm"  role="button" href="<?=tpLayoutHrefBoutonConnect();?>">
                        <?=tpLayoutConnect();?>
                    </a>
                </div>
            </div>
            <span class="navbar-text">
                <?php if (isset($messageBarreMenu)){echo ($messageBarreMenu);} ?>
            </span>
        </div>
    </nav>
    <!-- AFFICHAGE PAGE -->
    <main class="container">      
         <?= $content ?>
    </main>
    <!-- chargement des scripts -->	
    <?php
        include_once (ROOT_PATH .'shared/includes/bootstrap_scripts.php');
        if (isset($script)){echo $script;}
    
        use shared\php\classes\personalisation\Parametre    as Parametre;
        use shared\php\classes\socle\Menu                   as Menu;
        use shared\php\toolbox\Toolbox_adressage            as TbAdressage;
        use shared\php\classes\socle\User                   as User;
        
        function tpLayoutGetNomGroupe(){
            //recherche paramètre qui sera stocké dans le menu
            return Parametre::mdParametreGetDetail("GENERAL","NOMGROUPE","valeurA");
        }
        function tpLayoutUserImage(){
            return User::connectUserGetImage();
        }
        function tpLayoutAfficherMenu():string{
            $menu = new Menu();
            return $menu->myhtml;
        }
        function tpLayoutConnect(): string{
        //affiche le libellé sur le bouton de connexion/déconnexion
            if(User::userConnecte()){
                return "Se Déconnecter";
            } 
            else {
                return "Se Connecter";  
            }
        }
        function tpLayoutHrefBoutonConnect(): string{
            return TbAdressage::getURLstatic('accueil','loginFromToolbar');
        }
    ?>
</body>  
</html>  
