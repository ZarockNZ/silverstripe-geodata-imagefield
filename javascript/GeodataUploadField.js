$(document).ready(function() {

    $(".geodataupload").on('change', function(e) {
        $(this).fileExif(someCallback);
    });

    function someCallback(exifObject) {
        alert('in callback');
    }

    /*
    var getGeoData = function(exifObject) {

        // Get the name of the field - how now given this is in the callback?
        //var name = $(this).attr('name');

        //++ Remember actually want from the file, this is just to get getting the fields.
        //++ would in fact update them once run the conversion like in the PHP found to
        //++ convert the information from the file.

        // Get lat and long values from the hidden field.
        //var lat = $('#' + name + '-Latitude').val();
        //var lng = $('#' + name + '-Longitude').val();
        //var zom = $('#' + name + '-Zoom').val();

        //alert('Lat/lng:' + lat + ',' + lng);

        console.log(exifObject);
    }

    //+++
    $(".geodataupload").on('change', function(e) {
        $(this).fileExif(getGeoData);
    });

    */

});
