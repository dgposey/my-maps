README
My Maps Wordpress plugin
by David Posey
http://www.davidgposey.com

Google Maps API Key
===================
You need to get a Google Maps API key to use this plugin.  You can get one following these directions: https://developers.google.com/maps/documentation/javascript/tutorial#api_key

Once you have your key, insert it in the My Maps Settings page.

Template Tags
=============
There are two theme template tags.  

the_map
-------
This is a general purpose template tag for use in any of your template files:

the_map($address, $zoom, $type, $width, $height, $map_id)

$address: string: required: Any address that is recognizable by Google Maps.
$zoom: integer: optional: Any value from 0 (entire world) to 21 (very close up)
$type: string: optional: Any of the four values ROADMAP, SATELLITE, HYBRID, or TERRAIN
$width: integer: optional: The width in pixels of your map.
$height: integer: optional: The height in pixels of your map.
$map_id: string: optional: A unique string identifying this map.



the_map_cpt
-----------
This template tag is for use only in single-map.php, the template file for a Map custom post type.  It accepts no arguments; instead it takes values from the fields in your custom post type.