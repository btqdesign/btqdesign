var App = {
    preloader: null,
    init: function() {
        console.log("Ready...");

        resizeCajas();
        dropddown();

    },
    loaded: function() {
        console.log("Loaded...");
    }
}

//RESIZE CAJAS
$(window).on("resize", resizeCajas);
function resizeCajas(){
	var anchoCaja = $('.abrir-form a').width();
	// var anchoCaja = $('.abrir-form a').css({'max-width'});
	console.log(anchoCaja);
	var altoCaja = $('.abrir-form a').css({'min-height': anchoCaja});
	console.log(altoCaja);
}

function dropddown(){
    $(".dropdown-menu a").click(function(){
        $(this).parents(".dropdown").find('.btn').html($(this).text() + ' <i class="fas fa-caret-down"></i>');
        $(this).parents(".dropdown").find('.btn').val($(this).text());
    });
}


$(document).ready(App.init());
$(window).on("load", App.loaded());





/**
 * Replace the "css-selector" placeholders in the code below with the element selectors on your landing page.
 * You can use #id or any other CSS selector that will define the input field explicitly.
 * Example: "Email": "#MyEmailField".
 * If you don't have a field from the list below placed on your landing, leave the placeholder or remove the line.
 */
var config = {
    fields: {
        "Name":               "#name",
        "GivenName":          "#given_name",
        "MiddleName":         "#middle_name",
        "Surname":            "#surname",
        "MobilePhone":        "#mobile_phone",
        "Email":              "#email",
        "UsrLineaSolicitada": "#usr_linea_solicitada",
        "UsrAreaDeVisita":    "#usr_area_de_visita",
        "Owner":              "#owner"
    },
    landingId: "b3e9790b-b03d-4bcd-9e53-e531e8444ca6",
    serviceUrl: "https://ioa.bpmonline.com/0/ServiceModel/GeneratedObjectWebFormService.svc/SaveWebFormObjectData",
    redirectUrl: "https://test.btqdesign.com/bpmonline/recibido.html"
};
function setVals() {
    jQuery('#name').val(jQuery('#given_name').val() + ' ' + jQuery('#middle_name').val() + ' ' + jQuery('#surname').val());
    jQuery('#usr_linea_solicitada').val(jQuery('#usr_linea_solicitada_dropdown').val());
    jQuery('#usr_area_de_visita').val(jQuery('#usr_area_de_visita_dropdown').val());
    jQuery('#owner').val(jQuery('#owner_dropdown').val());
}
/**
* The function below creates a object from the submitted data.
* Bind this function call to the "onSubmit" event of the form or any other elements events.
* Example: <form class="mainForm" name="landingForm" onSubmit="createObject(); return false">
*/
function createObject() {
    setVals();
    landing.createObjectFromLanding(config);
}
/**
* The function below inits landing page using URL parameters.
*/
function initLanding() {
    landing.initLanding(config);
}
jQuery(document).ready(initLanding);
