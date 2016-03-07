# SilverStripe Geodata Uploadfield

[![Build Status](https://api.travis-ci.org/zarocknz/silverstripe-geodata-uploadfield.svg?branch=master)](https://travis-ci.org/zarocknz/silverstripe-geodata-uploadfield)
[![Latest Stable Version](https://poser.pugx.org/zarocknz/silverstripe-geodata-uploadfield/version.svg)](https://github.com/zarocknz/silverstripe-geodata-uploadfield/releases)
[![Latest Unstable Version](https://poser.pugx.org/zarocknz/silverstripe-geodata-uploadfield/v/unstable.svg)](https://packagist.org/packages/zarocknz/silverstripe-geodata-uploadfield)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/zarocknz/silverstripe-geodata-uploadfield.svg)](https://scrutinizer-ci.com/g/zarocknz/silverstripe-geodata-uploadfield?branch=master)
[![Total Downloads](https://poser.pugx.org/zarocknz/silverstripe-geodata-uploadfield/downloads.svg)](https://packagist.org/packages/zarocknz/silverstripe-geodata-uploadfield)
[![License](https://poser.pugx.org/zarocknz/silverstripe-geodata-uploadfield/license.svg)](https://github.com/zarocknz/silverstripe-geodata-uploadfield/blob/master/license.md)

File upload field for forms with a Google Map to allow the user to set the location of the uploaded media, for example photos.

Latitude, Longitude, and Zoom fields are saved in to the same DataObject as the Image the upload field is for when the form is submitted.

If the file has geolocation tags in it once the file is chosen the marker on the map will move to that location automatically (the user can adjust manually if desired).

Note this only works on front-end forms at this time.

## Requirements
    * Silverstripe 3.1.x+
    * JQuery-1.7.1+

## Usage example

Add the following fields to the DataObject your form submissions are saved in to...
```php
'Latitude'  => 'Varchar(255)',
'Longitude' => 'Varchar(255)',
'Zoom'      => 'Int',
```

Also ensure your data object has an Image (or other object which allows file upload)...
```php
private static $has_one = array(
    'Image' => 'Image',
);
```

Then create your form using the GeodataUploadField instead of a normal Silverstripe upload field...
```php
public function getFrontEndFields($params = null)
{
    $fields = parent::getFrontEndFields($params);

    // Create GeoData Upload field.
    $upload = GeoDataUploadField::create('Image', 'Image');

    // Set options to prevent selection of existing or access to the filesystem as per Silverstripe docs.
    $upload->setCanAttachExisting(false);
    $upload->setCanPreviewFolder(false);

    $fields->replaceField('Image', $upload);

    return $fields;
}
```

## Constructor options

There are a few other things you can pass to the constructor besides the field name and title. For the third parameter you can
pass an array of options which will override the default options for the map in _config/geodata-uploadfield.yml.

If you have named the Latitude, Longitude, or Zoom fields differently in your DataObject you need to pass the names of them in to the constructor as the last 3 parameters otherwise nothing will be saved on form submission.

The following example shows creating a GeoDataUploadField passing some options and the differently named lat and lng fields...

```php
    $options =
    $upload = GeoDataUploadField::create(
        'Image',                // Name.
        'Select an Image',      // Title
        array(                  // Options.
            'map' => array(
                'zoom' => 10
            )
        ),
        'theLatField',          // Latitude field name.
        'theLngField',          // Longitude field name.
        'theZomField'           // Zoom field name.
    );
```

Remember, as long as you have named the fields on the dataobject Latitude, Longitude, and Zoom you don't need to pass their names in to the constructor.

## Credits
This module is heavily based on the BetterBrief/silverstripe-googlemapfield by Will Morgan and others which has a BSD license.

This module also includes the Javascript EXIF Reader - jQuery plugin 0.1.3 by Jacob Seidelin which has a MPL License.

All I have really done is bought these two together and modified them to work in the way I needed for a project.

I would like to thank the creators and contributors of those repositories / libraries.

## Maintainer
zarocknz - https://github.com/zarocknz

## TODO
    * Try to get this working CMS side.
