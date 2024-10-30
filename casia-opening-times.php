<?php
/**
Plugin Name: Casia Opening Times
Description: This plugin adds shortcode which displays opening times of establishments to your site. Plugin creates link Opening Hours to WP admin menu. Under that option is link Establishments. When this link is active, user can insert establishments. Page for that appears when user clicks on button "Add new" which is next to page title. On page that opens user can insert name of establishment and its opening info about every weekday. After establishment is saved, it is possible to display its opening info in a table. Shortcode [cas-opening-times establishment="Example"] must be inserted for that, attribute establishment must be name of establishment.
Version: 1.0
Author: castellar120
License: GPLv2 or later
Text Domain: casot
*/

//Actions
add_action( 'wp_enqueue_scripts', 'casot_custom_styles' );
add_action( 'wp_enqueue_scripts', 'casot_scripts' );

add_action('admin_init', 'casot_remove_submenu_page');
add_action('save_post', 'save_custom_meta');

// create custom plugin settings menu
add_action('admin_menu', 'casot_plugin_create_menu');

/* Register Admin Sub Menu */
add_action( 'admin_menu', 'casot_establishment_submenu' );

add_action( 'admin_enqueue_scripts', 'casot_admin_style' );

//Shortcodes
add_shortcode( 'cas-opening-times', 'cas_opening_times_shortcode' );

/**
 * @global array $casot_days Daynames.
 *
 * @since 1.0.0
 */
  $casot_days = [['name'=>'monday'], ['name'=>'tuesday'], ['name'=>'wednesday'], ['name'=>'thursday'], ['name'=>'friday'], ['name'=>'saturday'], ['name'=>'sunday']];

/**
 * @global string $prefix Prefix for custom meta fields.
 *
 * @since 1.0.0
 */
  $prefix = 'custom_';

/**
 * @global array $custom_meta_fields Array into which custom meta fields are added.
 *
 * @since 1.0.0
 */
$custom_meta_fields = [];

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function cas_load_textdomain() {
  load_plugin_textdomain( 'casot', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}

/**
 * Shows opening time of given day.
 *
 * @param string $day Day to shoe info about.
 * @param string $day Day to shoe info about.
 *
 * @return string Returns opening times or word Closed if closed on given day.
 *
 * @since 1.0.0
 */
function cas_dayinfo($day, $est_id) {
  $opens = get_post_meta($est_id, 'custom_opening_' . $day, true);
  $closes = get_post_meta($est_id, 'custom_closing_' . $day, true);
  $closed = get_post_meta($est_id, 'custom_closed_' . $day, true);

  if ($closed) {
    _e('Closed', 'casot');
  } else {
    echo $opens . " - " . $closes;
  }
}

/**
 * Create shortcode.
 *
 * @since 1.0.0
 */
// Add Shortcode
function cas_opening_times_shortcode( $atts ) {

  // Attributes
  $atts = shortcode_atts(
    array(
      'establishment' => '',
    ),
    $atts,
    'cas-opening-times'
  );

  ob_start(); ?>
    <h2><?php _e('Opening times', 'casot'); ?></h2>
    <div class="cas-table__container">
      <p><?php echo $atts['establishment']; ?></p>
      <div class="cas-table">
        <?php
          foreach ($GLOBALS['casot_days'] as $key => $value) {
        ?>
            <div class="cas-table__el cas-table__el--first">
              <?php
                switch ($value['name']) {
                  case 'monday':
                    _e('M', 'casot');
                    break;
                  case 'tuesday':
                    _e('T', 'casot');
                    break;
                  case 'wednesday':
                    _e('W', 'casot');
                    break;
                  case 'thursday':
                    _e('T', 'casot');
                    break;
                  case 'friday':
                    _e('F', 'casot');
                    break;
                  case 'saturday':
                    _e('S', 'casot');
                    break;
                  case 'sunday':
                    _e('S', 'casot');
                    break;
                }
              ?>
            </div>
            <div class="cas-table__el cas-table__el--second">
              <?php
                $est = get_page_by_title($atts['establishment'], 'OBJECT', 'casot_establishment');
                cas_dayinfo($value['name'], $est->ID);
              ?>
            </div>
            <br>
        <?php
          }
        ?>
      </div>
    </div>
  <?php return ob_get_clean();
}

// Register Style
function casot_custom_styles() {

  wp_register_style( 'casot_main', '/wp-content/plugins/' . basename( dirname( __FILE__ ) ) . '/main.css', false, false );
  wp_enqueue_style( 'casot_main' );

}

// Register Script
function casot_scripts() {
  wp_register_script( 'casot_main/js', '/wp-content/plugins/' . basename( dirname( __FILE__ ) ) . '/main.js', array('jquery'), false, true );
  wp_enqueue_script( 'casot_main/js' );

}

// Register Admin Styles
function casot_admin_style() {
  wp_register_style( 'casot_main/admin', '/wp-content/plugins/' . basename( dirname( __FILE__ ) ) . '/admin.css', false, false );
  wp_enqueue_style( 'casot_main/admin' );
}

/**
 * create new top-level menu
 *
 * @since 1.0.0
 */
function casot_plugin_create_menu() {
  add_menu_page('Opening Hours', 'Opening Hours', 'administrator', 'casot-opening-hours', '' , 'dashicons-clock' );
}

if ( ! function_exists('casot_offices') ) {

// Register Custom Post Type
function casot_offices() {

  $labels = array(
    'name'                  => _x( 'Establishments', 'Post Type General Name', 'casot' ),
    'singular_name'         => _x( 'Establishment', 'Post Type Singular Name', 'casot' ),
    'menu_name'             => __( 'Establishments', 'casot' ),
    'name_admin_bar'        => __( 'Establishment', 'casot' ),
    'archives'              => __( 'Item Archives', 'casot' ),
    'attributes'            => __( 'Item Attributes', 'casot' ),
    'parent_item_colon'     => __( 'Parent Item:', 'casot' ),
    'all_items'             => __( 'All Items', 'casot' ),
    'add_new_item'          => __( 'Add New Item', 'casot' ),
    'add_new'               => __( 'Add New', 'casot' ),
    'new_item'              => __( 'New Item', 'casot' ),
    'edit_item'             => __( 'Edit Item', 'casot' ),
    'update_item'           => __( 'Update Item', 'casot' ),
    'view_item'             => __( 'View Item', 'casot' ),
    'view_items'            => __( 'View Items', 'casot' ),
    'search_items'          => __( 'Search Item', 'casot' ),
    'not_found'             => __( 'Not found', 'casot' ),
    'not_found_in_trash'    => __( 'Not found in Trash', 'casot' ),
    'featured_image'        => __( 'Featured Image', 'casot' ),
    'set_featured_image'    => __( 'Set featured image', 'casot' ),
    'remove_featured_image' => __( 'Remove featured image', 'casot' ),
    'use_featured_image'    => __( 'Use as featured image', 'casot' ),
    'insert_into_item'      => __( 'Insert into item', 'casot' ),
    'uploaded_to_this_item' => __( 'Uploaded to this item', 'casot' ),
    'items_list'            => __( 'Items list', 'casot' ),
    'items_list_navigation' => __( 'Items list navigation', 'casot' ),
    'filter_items_list'     => __( 'Filter items list', 'casot' ),
  );
  $args = array(
    'label'                 => __( 'Establishment', 'casot' ),
    'description'           => __( 'Post Type Description', 'casot' ),
    'labels'                => $labels,
    'supports'              => array( 'title', 'custom-fields' ),
    'taxonomies'            => array( 'category', 'post_tag' ),
    'hierarchical'          => false,
    'public'                => true,
    'show_ui'               => true,
    'show_in_menu'          => false,
    'menu_position'         => 5,
    'show_in_admin_bar'     => true,
    'show_in_nav_menus'     => true,
    'can_export'            => true,
    'has_archive'           => true,
    'exclude_from_search'   => false,
    'publicly_queryable'    => true,
    'capability_type'       => 'page',
    'show_in_rest'          => false,
  );
  register_post_type( 'casot_establishment', $args );

}
add_action( 'init', 'casot_offices', 0 );

}
 
/**
 * Add admin menu
 *
 * @since 1.0.0
 */
function casot_establishment_submenu(){
 
    add_submenu_page(
        'casot-opening-hours',                 // parent slug
        'Establishments',             // page title
        'Establishments',             // sub-menu title
        'edit_posts',                 // capability
        'edit.php?post_type=casot_establishment' // your menu menu slug
    );
}

/**
 * Remove submenu page Opening Hours from main menu page Opening Hours.
 *
 * @since 1.0.0
 */
function casot_remove_submenu_page() {
  remove_submenu_page('casot-opening-hours', 'casot-opening-hours');
}

/**
 * Add the Meta Box for opening times to Establishment post type.
 *
 * @since 1.0.0
 */
function add_custom_meta_box() {
    add_meta_box(
        'custom_meta_box', // $id
        __('Opening hours', 'casot'), // $title
        'show_custom_meta_box', // $callback
        'casot_establishment', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'add_custom_meta_box');

foreach ($GLOBALS['casot_days'] as $key => $value) {
  array_push($custom_meta_fields,
    array(
      'label'=> __(ucfirst($value['name']), 'casot'),
      'id'    => $prefix.'section_'.$value['name'],
      'type'  => 'section'
    ),
    array(
      'label'=> __('Opening time', 'casot'),
      'desc'  => __('Opening time', 'casot'),
      'id'    => $prefix.'opening_'.$value['name'],
      'type'  => 'text'
      ),
    array(
      'label'=> __('Closing time', 'casot'),
      'desc'  => __('Closing time', 'casot'),
      'id'    => $prefix.'closing_'.$value['name'],
      'type'  => 'text'
    ),
    array(
      'label'=> __('Closed', 'casot'),
      'desc'  => __('Closed', 'casot'),
      'id'    => $prefix.'closed_'.$value['name'],
      'type'  => 'checkbox'
    ));
}

/**
 * Function to show custom meta boxes.
 *
 * @since 1.0.0
 */
function show_custom_meta_box() {
global $custom_meta_fields, $post;
// Use nonce for verification
echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
     
    // Begin the field table and loop
    echo '<table class="form-table">';
    foreach ($custom_meta_fields as $field) {
        // get value of this field if it exists for this post
        $meta = get_post_meta($post->ID, $field['id'], true);
        // begin a table row with
        echo '<tr>
                <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
                <td>';
                switch($field['type']) {
                    // text
                    case 'text':
                        echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
                            <br /><span class="description">'.$field['desc'].'</span>';
                    break;
                    // checkbox
                    case 'checkbox':
                        echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
                            <label for="'.$field['id'].'">'.$field['desc'].'</label>';
                } //end switch
        echo '</td></tr>';
    } // end foreach
    echo '</table>'; // end table
}

/**
 * Save custom meta boxes data.
 *
 * @since 1.0.0
 */
function save_custom_meta($post_id) {
    global $custom_meta_fields;
     
    // verify nonce
    if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__))) 
        return $post_id;
    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;
    // check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id))
            return $post_id;
        } elseif (!current_user_can('edit_post', $post_id)) {
            return $post_id;
    }
     
    // loop through fields and save the data
    foreach ($custom_meta_fields as $field) {
        $old = get_post_meta($post_id, $field['id'], true);
        $new = $_POST[$field['id']];
        if ($new && $new != $old) {
            update_post_meta($post_id, $field['id'], $new);
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old);
        }
    } // end foreach
}
?>