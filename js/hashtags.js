jQuery(document).ready(function($){

    function autocomplete(){


       $('#tags').tagsInput({

            autocomplete_url:'http://tibor.dev/api/autocomplete'
        });
    }

    autocomplete();
    // for textarea.
    $('textarea')
        .focus(function() { $(this).css("background", "none") })
        .blur(function() { if ($(this)[0].value == '') { $(this).css("background", "url(images/benice.png) center center no-repeat") } });
});


