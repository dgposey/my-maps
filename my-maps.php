<?php
	/*
		Plugin Name: My Maps
		Description: Add Google Maps to your Wordpress installation.
		Version: 0.1
		
		Author: David Posey
		Author URI: http://www.davidgposey.com
	*/
	
	
	/*****************
	*** Admin Page ***
	*****************/
	
	/*** Initialize the page ***/
	add_action("admin_menu", "mm_settings_page");
	function mm_settings_page(){
		add_menu_page("My Maps Settings Page", "My Maps Settings", "administrator", "my_maps", "mm_settings_page_html", "", 81);
	}
	
	function mm_settings_page_html(){
		?>
			<div id="mm_admin_page">
				<h2>My Maps Settings</h2>
				<p>Please keep these settings up to date.</p>
				<form action="options.php" method="post">
					<?php
						settings_fields("mm");
						do_settings_sections("mm_page");
					?>
					
					<input type="submit" class="button-primary" name="submit" value="Save Settings" />
				</form>
			</div>
		<?php
	}
	
	
	/*** Add sections and fields ***/
	add_action("admin_init", "mm_add_settings");
	function mm_add_settings(){
		add_settings_section("mm_settings_section", "My Maps Settings", "mm_settings_section_callback", "mm_page");
		
		add_settings_field("mm_settings_field_api_key", "Google Maps API Key", "mm_settings_field_api_key_callback", "mm_page", "mm_settings_section");
		register_setting("mm", "mm_settings_field_api_key");
	}
	
	/*** Section: Maps API Key ***/
	function mm_settings_section_callback(){
		echo "You must sign up for a <a href='https://developers.google.com/maps/documentation/javascript/tutorial#api_key' target='_blank'>Maps API Key</a> from Google before entering it here, and it <strong>must</strong> be a v3 key (v2 keys won't work here).";
	}
	
	function mm_settings_field_api_key_callback(){
		?>
			<input type="text" class="regular-text" name="mm_settings_field_api_key" id="mm_settings_field_api_key" value="<?php echo get_option("mm_settings_field_api_key"); ?>" />
		<?php
	}
	
	/*** Section: ***/
	
	
	
	
	/********************
	*** Template Tags ***
	********************/

	
	$map_id_counter = 0;
	
	// general purpose template tag for use in any template
	function the_map($address, $zoom = 12, $type = "ROADMAP", $width = 600, $height = 600, $map_id = ""){
		global $map_id_counter;
		if(strlen($map_id) == 0){
			$map_id = "map" . $map_id_counter;
			$map_id_counter++;
		}
		
		$valid_types = array("ROADMAP", "SATELLITE", "HYBRID", "TERRAIN");
		$type = strtoupper($type);
		if(!in_array($type, $valid_types)){
			$type = "ROADMAP";
		}
		
		if($zoom > 21){
			$zoom = 21;
		}else if($zoom < 0){
			$zoom = 0;
		}
		
		map($address, $zoom, $type, $width, $height, $map_id);
	}
	
	// template tag for use only in templates for map cpt
	function the_map_cpt(){
		global $post;
		
		$address = get_post_meta($post->ID, "map_address", true);
		$zoom = get_post_meta($post->ID, "map_zoom", true);
		$type = get_post_meta($post->ID, "map_type", true);
		$width = get_post_meta($post->ID, "map_width", true);
		$height = get_post_meta($post->ID, "map_height", true);
		
		the_map($address, $zoom, $type, $width, $height);
	}
	
	/***********************
	*** Custom Post Type ***
	***********************/
	
	add_action( 'init', 'register_cpt_map' );
	function register_cpt_map() {
		$labels = array(
			'name' => _x( 'Maps', 'map' ),
			'singular_name' => _x( 'Map', 'map' ),
			'add_new' => _x( 'Add New', 'map' ),
			'add_new_item' => _x( 'Add New Map', 'map' ),
			'edit_item' => _x( 'Edit Map', 'map' ),
			'new_item' => _x( 'New Map', 'map' ),
			'view_item' => _x( 'View Map', 'map' ),
			'search_items' => _x( 'Search Maps', 'map' ),
			'not_found' => _x( 'No maps found', 'map' ),
			'not_found_in_trash' => _x( 'No maps found in Trash', 'map' ),
			'parent_item_colon' => _x( 'Parent Map:', 'map' ),
			'menu_name' => _x( 'Maps', 'map' ),
		);
		$args = array(
			'labels' => $labels,
			'hierarchical' => false,
			'supports' => array( 'title', 'editor' ),
			'taxonomies' => array( 'category' ),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_icon' => get_bloginfo("wpurl") . '/wp-content/plugins/my-maps/images/icon.png',
			'show_in_nav_menus' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'has_archive' => true,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capability_type' => 'post'
		);
		register_post_type( 'map', $args );
	}
	
	/*** add meta boxes ***/
	// address field
	add_action("add_meta_boxes", "add_map_address_field");
	function add_map_address_field(){
		add_meta_box(
			"map_address_field",
			"Address",
			"map_address_field_html",
			"map",
			"normal",
			"default"
		);
	}
	function map_address_field_html($post){
		?>
			<p>Use any address that is recognized by Google Maps.</p>
			<input type="text" class="regular-text" name="map_address" value="<?php echo get_post_meta($post->ID, "map_address", true); ?>" />
		<?php
	}
	
	// size
	add_action("add_meta_boxes", "add_map_size_field");
	function add_map_size_field(){
		add_meta_box(
			"map_size_field",
			"Map Size",
			"map_size_field_html",
			"map",
			"normal",
			"default"
		);
	}
	function map_size_field_html($post){
		?>
			<p>Enter values in <a href="http://en.wikipedia.org/wiki/Pixel" target="_blank">pixels</a>.</p>
			Width: <input type="text" class="" name="map_width" value="<?php echo get_post_meta($post->ID, "map_width", true); ?>" />px&nbsp;&nbsp;&nbsp;
			Height: <input type="text" class="" name="map_height" value="<?php echo get_post_meta($post->ID, "map_height", true); ?>" />px
		<?php
	}
	
	// map properties
	add_action("add_meta_boxes", "add_map_properties_field");
	function add_map_properties_field(){
		add_meta_box(
			"map_properties_field",
			"Map Properties",
			"map_properties_field_html",
			"map",
			"normal",
			"default"
		);
	}
	function map_properties_field_html($post){
		?>
			Zoom Level (0-21): <input type="text" class="" name="map_zoom" value="<?php echo get_post_meta($post->ID, "map_zoom", true); ?>" /><br />
			Map Type: <input type="text" class="" name="map_type" value="<?php echo get_post_meta($post->ID, "map_type", true); ?>" />
		<?php
	}
	
	
	
	add_action("save_post", "save_map_fields", 10, 2);
	function save_map_fields($post_id, $post){
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		
		update_post_meta($post_id, "map_address", $_POST["map_address"]);
		update_post_meta($post_id, "map_width", $_POST["map_width"]);
		update_post_meta($post_id, "map_height", $_POST["map_height"]);
		update_post_meta($post_id, "map_zoom", $_POST["map_zoom"]);
		update_post_meta($post_id, "map_type", $_POST["map_type"]);
	}
	
	
	/********************************
	*** General Purpose Functions ***
	********************************/
	add_action("wp_head", "init_maps");
	function init_maps(){
		$api_key = get_option("mm_settings_field_api_key");
		?>
			<style type="text/css">
				html { height: 100% }
				body { height: 100%; margin: 0; padding: 0 }
				/*.map_canvas {
					height: 600px;
					width: 600px;
				}*/
			</style>
			<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=<?php echo $api_key; ?>&sensor=false"></script>
			<script type="text/javascript">
				var init_funcs = new Array();
				window.onload = initialize;
				
				function initialize(){
					for(var i = 0 ; i < init_funcs.length ; i++){
						window[init_funcs[i]]();
					}
				}
			</script>
		<?php
	}
	
	function map($address, $zoom, $type, $width, $height, $map_id){
		?>
			
			<script type="text/javascript">
				
				function initialize<?php echo $map_id; ?>(){
					// Do some geocoding!
					var geocoder = new google.maps.Geocoder();
					var address = "<?php echo $address; ?>";
					geocoder.geocode({'address': address}, function(results, status){
						if (status == google.maps.GeocoderStatus.OK){
							// set the options
							var myOptions = {
								center: results[0].geometry.location,
								zoom: <?php echo $zoom; ?>,
								mapTypeId: google.maps.MapTypeId.<?php echo $type; ?>
							};
							// make a map
							var <?php echo $map_id; ?> = new google.maps.Map(document.getElementById("<?php echo $map_id; ?>_canvas"), myOptions);
							// add a marker
							var marker = new google.maps.Marker({
								map: <?php echo $map_id; ?>,
								position: results[0].geometry.location
							});
						}else{
							alert("Geocode was not successful for the following reason: " + status);
						}
					});
				}
				
				init_funcs.push("initialize<?php echo $map_id; ?>");
				
			</script>
			<div id="<?php echo $map_id; ?>_canvas" class="map_canvas" style="width:<?php echo $width; ?>px;height:<?php echo $height; ?>px;"></div>
		<?php
	}
	
?>