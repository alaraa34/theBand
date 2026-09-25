(function($) {

"use strict";
// Setup the calendar with the current date
$(document).ready(function(){
        var date = new Date();
        var today = date.getDate();
        // Set click handlers for DOM elements
        $(".right-button").click({date: date}, next_year);
        $(".left-button").click({date: date}, prev_year);
        $(".month").click({date: date}, month_click);
        $("#add-buttonDisponible").click({date: date}, new_event_dispo);
        $("#add-buttonIndisponible").click({date: date}, new_event_indispo);
        $("#add-buttonNeutre").click({date: date}, new_event_neutre);
        // Set current month as active
        $(".months-row").children().eq(date.getMonth()).addClass("active-month");
        init_planning();
        init_calendar(date);
    
});
//chargement des dates déjà disponibles
function init_planning(){
    //liste MEF est un enchainement de dates en format "D2024-03-27" ou "I2024-03-27"séparées par des ;
    var listeDispo = $("#listeMEF").val();
    if (listeDispo.length > 0){
        var tableau = listeDispo.split(";");
        //etude des postes -1 car à cause du dernier ";" le dernier poste estr à blanc
        for(var i=0; i<tableau.length -1 ; i++) {
            //création de l'évènement avec année, mois jour et true pour disponible
            event_add (tableau[i].substr(0, 1),tableau[i].substr(1, 4),tableau[i].substr(6, 2),tableau[i].substr(9, 2))
        }
    }
}

// Initialize the calendar by appending the HTML dates
function init_calendar(date) {
    $(".tbody").empty();
    $(".events-container").empty();
    var calendar_days = $(".tbody");
    var month = date.getMonth();
    var year = date.getFullYear();
    var day_count = days_in_month(month, year);//nbre jour ds le mois
    var row = $("<tr class='table-row'></tr>");
    var today = date.getDate();
    // Set date to 1 to find the first day of the month
    date.setDate(1);
    var first_day = date.getDay();
    // 35+firstDay is the number of date elements to be added to the dates table
    // 35 is from (7 days in a week) * (up to 5 rows of dates in a month)
    for(var i=1; i<36+first_day; i++) {
        // Since some of the elements will be blank, 
        // need to calculate actual date from index
        var day = i-first_day+1;
        // If it is a lundi, make a new row
        if(i%7===1) {
            calendar_days.append(row);
            row = $("<tr class='table-row'></tr>");
        }
        // if current index isn't a day in this month, make it blank
        if(i < first_day || day > day_count) {
            var curr_date = $("<td class='table-date nil'>"+"</td>");
            row.append(curr_date);
        }   
        else {
            var curr_date = $("<td class='table-date'>"+day+"</td>");
			//recherche de la dispo pour cette date
            var events = check_events(day, month+1, year);
            if(today===day && $(".active-date").length===0) {
                curr_date.addClass("active-date");
            }
			
            // si date disponible
            if(events.length!==0) {
                switch (events[0]["disponible"]){
                    case "D":
                        curr_date.addClass("event-date-disponible");
                        break;
                    case "I":
                        curr_date.addClass("event-date-indisponible");
                        break;
                    default :
                        curr_date.addClass("active-date");
                }
            }
			
            // Set onClick handler for clicking a date
            curr_date.click({events: events, month: months[month], day:day}, date_click);
            row.append(curr_date);
        }
    }
    // Append the last row and set the current year
    calendar_days.append(row);
    $(".year").text(year);
}

// Get the number of days in a given month/year
function days_in_month(month, year) {
    var monthStart = new Date(year, month, 1);
    var monthEnd = new Date(year, month + 1, 1);
    return Math.round((monthEnd - monthStart) / (1000 * 60 * 60 * 24));    
}

// Clic sur une date
function date_click(event) {
    $(".active-date").removeClass("active-date");
    $(this).addClass("active-date");
	//texte du bouton selon evt 
	var day = parseInt($(this).html());
	var month = getMonthNumber($(".active-month").text());
	var year = parseInt($("#year").text());
	var events = check_events(day, month, year);
};

// Event handler for when a month is clicked
function month_click(event) {
    var date = event.data.date;
    $(".active-month").removeClass("active-month");
    $(this).addClass("active-month");
    var new_month = $(".month").index(this);
    date.setMonth(new_month);
    init_calendar(date);
}
function getMonthNumber(mois){
//Récupère le n° du mois qui correspond au paramètre mois
    var moisNum = 0;
    var listeMois = $(".months-row > td");
    var i = 0;
    for (i = 0; i < listeMois.length ; i++) { 
            if (listeMois[i].outerText === mois.toUpperCase()){
                    moisNum=i+1;
                    break;}
    }
    return moisNum ;
}
// Event handler for when the year right-button is clicked
function next_year(event) {
    var date = event.data.date;
    var new_year = date.getFullYear()+1;
    $("year").html(new_year);
    date.setFullYear(new_year);
    init_calendar(date);
}

// Event handler for when the year left-button is clicked
function prev_year(event) {
    var date = event.data.date;
    var new_year = date.getFullYear()-1;
    $("year").html(new_year);
    date.setFullYear(new_year);
    init_calendar(date);
}
function new_event_dispo(event){
    new_event(event,"D");
}
function new_event_indispo(event){
    new_event(event,"I");
}
function new_event_neutre(event){
    new_event(event,"N");
}
// Event handler for clicking the new event button
function new_event(event,name) {
//paramètre évènement et D, I ou N
    // si pas de date sélectionnée on ne fait rien
    if($(".active-date").length===0)
        return;
       
    // Event handler for ok button
 	var date = event.data.date;
	var day = parseInt($(".active-date").html());
	
	// changement de disponibilité
	var newEvent = new_event_json(name, date, day);
        date.setDate(day);
        
        //dessin calendrier
	init_calendar(date);
	
	//ajout dans la liste des changements caché du formulaire sous la forme D01/10/2024 ou I01/10/2024 (Dispo/Indispo)
	//var evts_prec = $('#changements').val();
        var evts_prec = name + newEvent["day"] + "-" + newEvent["month"] + "-" + newEvent["year"];
        
        
        //Mise à jour en direct 
        Set_Form($('#url').val(),["changements"],[evts_prec]);
}

// Adds a json event to event_data
function new_event_json(name, date, day) {
    var event ;
	var i = check_events(day, date.getMonth()+1,date.getFullYear(),1)
	// si pas de disponibilité connue
	if (i==-1){
            event = event_add (name,date.getFullYear(),date.getMonth()+1,day);
	}
	else{
            //sinon changement de la dispo
             event_data["events"][i]["disponible"]= name;
             event = event_data["events"][i];
	}
	return event;
}
function event_add(disponible,annee,mois,jour){
    var event = {
                "disponible": disponible,
                "year": Number(annee),
                "month": Number(mois),
                "day": Number(jour)
        };
        //ajout de la dispo dans le fichier json
        event_data["events"].push(event);
        return event;
}

// Checks if a specific date has any events
function check_events(day, month, year, indice1event2=2) {
    var events = [];
	var indice=-1;
    for(var i=0; i<event_data["events"].length; i++) {
        var event = event_data["events"][i];
        if(event["day"]===day &&
            event["month"]===month &&
            event["year"]===year) {
                events.push(event);
                indice=i;
                break;}
    }
    if(indice1event2==1){return indice;}
	else{return events;}
}

// pour l'exemple
var event_data = {
    "events": [
        {
            "year": 2010,
            "month": 5,
            "day": 11,
            "disponible": "D"
        }
    ]
};
const months = [ 
    "January", 
    "February", 
    "March", 
    "April", 
    "May", 
    "June", 
    "July", 
    "August", 
    "September", 
    "October", 
    "November", 
    "December" 
];

})(jQuery);

//----------jsPlanning--------------
function jsPlanningAddDate(increment,url,z1,z2){
//increment positif ou négatif en jours
    let datePlanning =  new Date($('#dateDebutPlanning').val());
    datePlanning.setDate(datePlanning.getDate() + increment);
    var day = ("0" + datePlanning.getDate()).slice(-2);
    var month = ("0" + (datePlanning.getMonth() + 1)).slice(-2);
    var nouvelleDate = datePlanning.getFullYear()+"-"+(month)+"-"+(day) ;
    //alert("nouvelle " + nouvelleDate );
    $('#dateDebutPlanning').val(nouvelleDate);   
    //calcul du nouveau planning car le on change ne réagit pas
    Get_Form_zone(url, z1,z2);
}
function jsPlanningCodeActionChanger(ligne,codeAction){
//Indique que çà a été modifié sauf si on est en création
    controle = "#action" + ligne.toString();
    //alert("Modif" + controle + " Valeur avant " + $(controle).val());
    $(controle).val(codeAction); //action à Modif
    //alert("Modif" + controle + " Valeur après " + $(controle).val());
}
//-----------Fonctions utilisées dans la planning par période-----------
function jsPlanningChangerDateFin(indice){
//:Sur le planning période , récupère la date de début et initie la date de fin à j+1
    maDate = $("#dateDebut" + indice).val();
    $("#dateFin" + indice).val(maDate);
}

