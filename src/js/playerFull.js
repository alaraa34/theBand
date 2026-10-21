// Player complet (theBand/src/php/player/ctPlayerAll.php) : clic sur un titre de la liste de gauche
// le titre est marqué comme sélectionné et son détail est chargé en ajax dans la colonne de droite
function jsPlayerFullDetail(element, url, zone) {
    if (typeof metronomeStopIfActive === "function") { metronomeStopIfActive(); }
    if (typeof window.lecteurMultipisteToutArreter === "function") { window.lecteurMultipisteToutArreter(); }
    document.querySelectorAll("#liste-choix-song .list-item.actif").forEach(e => e.classList.remove("actif"));
    element.classList.add("actif");
    Get_Form_zone(url, "detail-choix", zone);
}

// choix d'une liste dans le combo : la liste est rechargée puis son premier titre est affiché à droite
// (liste vide : le détail affiché reste en place)
function jsPlayerFullListe(combo, url) {
    const donnees = new URLSearchParams();
    donnees.append(combo.name, combo.value);
    fetch(url, {method: "POST", body: donnees, headers: {"Content-Type": "application/x-www-form-urlencoded"}})
        .then(reponse => reponse.text())
        .then(html => {
            document.getElementById("liste-choix-song").innerHTML = html;
            const premier = document.querySelector("#liste-choix-song .list-item");
            if (premier) { premier.click(); }
        })
        .catch(erreur => console.log("Liste des titres non chargée : " + erreur));
}

// à l'ouverture : le premier titre (affiché à droite) est marqué comme sélectionné
document.addEventListener("DOMContentLoaded", function () {
    const premier = document.querySelector("#liste-choix-song .list-item");
    if (premier && document.querySelector("#detail-choix #detail-song")) { premier.classList.add("actif"); }
});
