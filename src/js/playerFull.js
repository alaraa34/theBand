// Player complet (theBand/src/php/player/ctPlayerAll.php) : clic sur un titre de la liste de gauche
// le titre est marqué comme sélectionné et son détail est chargé en ajax dans la colonne de droite
function jsPlayerFullDetail(element, url, zone) {
    if (typeof metronomeStopIfActive === "function") { metronomeStopIfActive(); }
    if (typeof window.lecteurMultipisteToutArreter === "function") { window.lecteurMultipisteToutArreter(); }
    document.querySelectorAll("#liste-choix-song .list-item.actif").forEach(e => e.classList.remove("actif"));
    element.classList.add("actif");
    Get_Form_zone(url, "detail-choix", zone);
}

// à l'ouverture : le premier titre (affiché à droite) est marqué comme sélectionné
document.addEventListener("DOMContentLoaded", function () {
    const premier = document.querySelector("#liste-choix-song .list-item");
    if (premier && document.querySelector("#detail-choix #detail-song")) { premier.classList.add("actif"); }
});
