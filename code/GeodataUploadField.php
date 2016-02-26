<?php

/**
 * GeoDataUploadField
 * For recording the geolocation of images, movies etc.
 *
 * Automatically displays a map under the upload field using the Google Maps API and includes hidden
 * fields on the form to record the latitude, longitute, and zoom level which are then saved to the
 * dataobject record after the form is submitted.
 *
 * If the file has geolocation tags in it (such as jpeg) the marker on the map will move automatically
 * to that location. The user can move the map marker / pin manually to specify the location of the media.
 *
 * You can also search for locations using the search box (if enabled), which uses the Google Maps Geocoding API.
 * @author <@ZarockNZ>
 *
 * Note: Heavily based on the silverstripe-googlemapfield by @willmorgan, but altered as needed to do what I want.
 * Note: Also based on / includes Javascript EXIF Reader to get the geotags from a file at time of selection by
 * Jacob Seidelin, cupboy@gmail.com, http://blog.nihilogic.dk
 *
 * @TODO //++ Check what need to include here or elsewhere to satisfy the licence requirements.
 */

class GeoDataUploadField extends UploadField
{
	/**
 	 * @var FormField
 	 */
	protected $latField;

	/**
 	 * @var String
 	 */
	protected $latFieldName;

	/**
 	 * @var FormField
 	 */
	protected $lngField;

	/**
 	 * @var String
 	 */
	protected $lngFieldName;

	/**
 	 * @var FormField
 	 */
	protected $zoomField;

	/**
 	 * @var String
 	 */
	protected $zoomFieldName;

	/**
 	 * @var array
 	 */
	protected $options = array();

	/**
	 * Constructor
 	 * @param String $name    The name of the upload field.
 	 * @param String $title   Title of the field on the form.
 	 * @param array  $options User defined options for the google map.
 	 * @param String $latFieldName Name of the Latitude property in the dataobject to save the lat value in.
 	 * @param String $lngFieldName Name of the Longitude property in the dataobject to save the lng value in.
 	 * @param String $zoomFieldName Name of the Zoom property in the dataobject to save the zoom value in.
 	 */
    public function __construct($name, $title = null, $options = array(), $latFieldName='Latitude',
		$lngFieldName='Longitude', $zoomFieldName='Zoom')
    {
		// Call parent constructor to do normal upload field things.
		parent::__construct($name, $title);

		// Save the lat and long field names for later use.
		$this->latFieldName = $latFieldName;
		$this->lngFieldName = $lngFieldName;
		$this->zoomFieldName = $zoomFieldName;

		// Set up the google map options passing in user supplied options.
		$this->setupOptions($options);

		// Call function to create hidden long, lat, zoom level fields on the form.
		$this->setupChildren();
    }

	/**
 	 * Merge options preserving the first level of array keys
 	 * @param array $options
 	 */
	public function setupOptions(array $options)
	{
		// Get the default google map options.
		$this->options = static::config()->default_options;

		// Merge them with the user defined options.
		foreach($this->options as $name => &$value) {
			if(isset($options[$name])) {
				if(is_array($value)) {
					$value = array_merge($value, $options[$name]);
				}
				else {
					$value = $options[$name];
				}
			}
		}
	}

	/**
 	 * Set up child hidden fields, and optionally the search box.
 	 * @return FieldList the children
	 */
	public function setupChildren()
	{
		// Create the latitude/longitude hidden fields.
		$this->latField = HiddenField::create(
			$this->name.'[Latitude]',
			'Lat',
			$this->getDefaultValue('Latitude')
		)->addExtraClass('googlemapfield-latfield');

		$this->lngField = HiddenField::create(
			$this->name.'[Longitude]',
			'Lng',
			$this->getDefaultValue('Longitude')
		)->addExtraClass('googlemapfield-lngfield');

		$this->zoomField = HiddenField::create(
			$this->name.'[Zoom]',
			'Zoom',
			$this->getDefaultValue('Zoom')
		)->addExtraClass('googlemapfield-zoomfield');

		$this->children = new FieldList(
			$this->latField,
			$this->lngField,
			$this->zoomField
		);

		// Create searhc box if should do so.
		if ($this->options['show_search_box']) {
			$this->children->push(
				TextField::create('Search')
					->addExtraClass('googlemapfield-searchfield')
					->setAttribute('placeholder', 'Search for a location')
			);
		}

		return $this->children;
	}

	/**
 	 * Returns the default value for the field.
 	 * @param  String $name The name of the field.
 	 * @return String The value of the field.
 	 */
	public function getDefaultValue($name)
	{
		$fieldValues = $this->getOption('default_field_values');
		return isset($fieldValues[$name]) ? $fieldValues[$name] : null;
	}

	 /**
 	 * Sets the value of the field and also of the child fields.
 	 * @param array $value Submitted form data.
 	 * @param DataObject $record The dataobject containing the record data.
 	 * @return UploadField self reference.
 	 */
	public function setValue($value, $record=null)
	{
		// Check if there is data for this object in the record, there is after the
		// form is submitted but normally not when the page is displayed for the first time.
		if (isset($record[$this->name])) {
			// Set the values for the lat and long fields to the values from the records
			// we need to do this otherwise the come saving time they will only have the default values.
			$this->latField->setValue(
				$record[$this->name]['Latitude']
			);

			$this->lngField->setValue(
				$record[$this->name]['Longitude']
			);

			$this->zoomField->setValue(
				$record[$this->name]['Zoom']
			);
		}

		// Call parent to do the normal things including returning self reference.
		return parent::setValue($value, $record);
	}

	/**
	 * Includes needed things in the front end.
	 */
	protected function requireDependencies()
	{
		// Set up some map params, including initialising the map.
		$gmapsParams = array(
			'callback' => 'googlemapfieldInit',
		);

		// Add google maps API key if there is one.
		if ($key = $this->getOption('api_key')) {
			$gmapsParams['key'] = $key;
		}

		// Require the needed CSS and javascript for the Google Map.
		Requirements::css(GEODATA_UPLOADFIELD_BASE .'/css/GoogleMapField.css');
		Requirements::javascript(GEODATA_UPLOADFIELD_BASE .'/javascript/GoogleMapField.js');
		Requirements::javascript('//maps.googleapis.com/maps/api/js?' . http_build_query($gmapsParams));

		// Require the javascript to read the geotag information from the selected file.
		//++ @TODO sort inclusion of JS in the site as there already is some older stuff.
		Requirements::javascript(GEODATA_UPLOADFIELD_BASE .'/javascript/jquery-1.7.1.js');

		//++ @TODO sort why the functions supposedly added in this file can't be called.
		//++ Perhaps move this to our own file below since rally only need one (might sort JS issues)
		Requirements::javascript(GEODATA_UPLOADFIELD_BASE .'/javascript/jquery.exif.js');

		// Require the JS to listen for the change event on the file upload.
		Requirements::javascript(GEODATA_UPLOADFIELD_BASE .'/javascript/GeodataUploadField.js');
	}

	/**
	 * Get the merged option that was set on __construct
	 * @param string $name The name of the option
	 * @return mixed
	 */
	public function getOption($name)
	{
		// Quicker execution path for "."-free names
		if (strpos($name, '.') === false) {
			if (isset($this->options[$name])) return $this->options[$name];
		} else {
			$names = explode('.', $name);
			$var = $this->options;

			foreach($names as $n) {
				if(!isset($var[$n])) {
					return null;
				}

				$var = $var[$n];
			}

			return $var;
		}
	}

	/**
	 * Set an option for this field
	 * @param string $name The name of the option to set
	 * @param mixed $val The value of said option
	 * @return $this
	 */
	public function setOption($name, $val)
	{
		// Quicker execution path for "."-free names
		if(strpos($name,'.') === false) {
			$this->options[$name] = $val;
		} else {
			$names = explode('.', $name);

			// We still want to do this even if we have strict path checking for legacy code
			$var = &$this->options;

			foreach($names as $n) {
				$var = &$var[$n];
			}

			$var = $val;
		}

		return $this;
	}

    /**
     * Called by SS to output the field.
     * @param array $properties [description]
     */
	public function Field($properties = array())
    {
		// Set up the JS options, placing the map pin in the default location and with the default zoom level.
		$jsOptions = array(
			'coords' => array(
				$this->getDefaultValue('Latitude'),
				$this->getDefaultValue('Longitude')
			),
			'map' => array(
				'zoom' => $this->getDefaultValue('Zoom') ?: $this->getOption('map.zoom'),
				'mapTypeId' => 'ROADMAP',
			),
		);

		$jsOptions = array_replace_recursive($this->options, $jsOptions);
		$this->setAttribute('data-settings', Convert::array2json($jsOptions));
		$this->requireDependencies();

		return parent::Field($properties);
	}

	/**
	 * @return FieldList The Latitude/Longitude/Zoom fields
	 */
	public function getChildFields()
    {
		return $this->children;
	}

	/**
	 * Called before the dataobject record is saved.
	 * @param  DataObjectInterface $record The record.
	 * @return [type]                      [description]
	 */
	public function saveInto(DataObjectInterface $record)
	{
		// On the dataobject record set the lat, long, zoom fields (names specified by the dev at
		// time of construction) to the values of the lat long and zoom child fields of this class.
		$record->setCastedField($this->latFieldName, $this->latField->dataValue());
		$record->setCastedField($this->lngFieldName, $this->lngField->dataValue());
		$record->setCastedField($this->zoomFieldName, $this->zoomField->dataValue());

		// Do parent stuff as normal.
		return parent::saveInto($record);
	}
}
