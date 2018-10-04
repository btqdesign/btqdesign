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
        $(this).parents(".dropdown").find('.btn').val($(this).data('value'));
    });
}


$(document).ready(App.init());
$(window).on("load", App.loaded());
