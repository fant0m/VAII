$(document).ready(function() {
    $('header form > input').on('focus', function() {
        $(this).animate({width: '300px'});
    }).on('blur', function() {
        $(this).animate({width: '200px'});
    });
    $('header nav a').on('click', function() {
    	$('.nav.navbar-toggleable-xs.in').removeClass('in');
    });
});
