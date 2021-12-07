<?php

/**
 * Plugin name: Movies
 * Plugin URI: 
 * Description: Search Movies
 * Author: AR
 * Author URI: 
 * text-domain: 
 */
 
function register_custom_post_type_movie() {
    $args = array(
        "label" => __( "Movies", "" ),
        "labels" => array(
            "name" => __( "Movies", "" ),
            "singular_name" => __( "Movie", "" ),
            "featured_image" => __( "Movie Poster", "" ),
            "set_featured_image" => __( "Set Movie Poster", "" ),
            "remove_featured_image" => __( "Remove Movie Poster", "" ),
            "use_featured_image" => __( "Use Movie Poster", "" ),
        ),
        "public" => true,
		'has_archive' => true,
        "publicly_queryable" => true,
        "show_ui" => true,
        "show_in_rest" => false,
        "has_archive" => false,
        "show_in_menu" => true,
        "exclude_from_search" => false,
        "capability_type" => "post",
        "map_meta_cap" => true,
        "hierarchical" => false,
        "rewrite" => array( "slug" => "movie", "with_front" => true ),
        "query_var" => true,
        "supports" => array( "title", "editor", "thumbnail","custom-fields" ),
        "taxonomies" => array( "category" ),
    );
    register_post_type( "movie", $args );
	
	

}
add_action( 'init', 'register_custom_post_type_movie' );


// Add the custom columns to the Movie post type:
add_filter( 'manage_movie_posts_columns', 'set_custom_edit_movie_columns' );
function set_custom_edit_movie_columns($columns) {
   // unset( $columns['author'] );
    $columns['Movie Director'] = __( 'Movie Director', 'your_text_domain' );
 //   $columns['publisher'] = __( 'Publisher', 'your_text_domain' );

    return $columns;
}
add_filter( 'manage_movie_posts_columns', 'sets_custom_edit_movie_columns' );
function sets_custom_edit_movie_columns($columns) {
   // unset( $columns['author'] );
    $columns['Movie Cast'] = __( 'Movie Cast', 'your_text_domain' );
 //   $columns['publisher'] = __( 'Publisher', 'your_text_domain' );

    return $columns;
}

// Add the data to the custom columns for the movie post type:
add_action( 'manage_movie_posts_custom_column' , 'custom_movie_column', 10, 2 );
function custom_movie_column( $column, $post_id ) {
    switch ( $column ) {

        case 'Movie Director' :
            $terms = get_post_meta( $post_id , 'Movie Director' , true );
            if ( is_string( $terms ) )
                echo $terms;
            else
                _e( 'Unable to get author(s)', 'your_text_domain' );
            break;

        case 'Movie Cast' :
            echo get_post_meta( $post_id , 'Movie Cast' , true ); 
            break;

    }
}



//Image
//init the meta box
add_action( 'after_setup_theme', 'custom_postimage_setup' );
function custom_postimage_setup(){
    add_action( 'add_meta_boxes', 'custom_postimage_meta_box' );
    add_action( 'save_post', 'custom_postimage_meta_box_save' );
}

function custom_postimage_meta_box(){

    //on which post types should the box appear?
    $post_types = array('movie','page');
    foreach($post_types as $pt){
        add_meta_box('custom_postimage_meta_box',__( 'More Featured Images', 'yourdomain'),'custom_postimage_meta_box_func',$pt,'side','low');
    }
}

function custom_postimage_meta_box_func($post){

    //an array with all the images (ba meta key). The same array has to be in custom_postimage_meta_box_save($post_id) as well.
    $meta_keys = array('second_featured_image','third_featured_image','forth_featured_image','fifth_featured_image');

    foreach($meta_keys as $meta_key){
        $image_meta_val=get_post_meta( $post->ID, $meta_key, true);
        ?>
        <div class="custom_postimage_wrapper" id="<?php echo $meta_key; ?>_wrapper" style="margin-bottom:20px;">
            <img src="<?php echo ($image_meta_val!=''?wp_get_attachment_image_src( $image_meta_val)[0]:''); ?>" style="width:100%;display: <?php echo ($image_meta_val!=''?'block':'none'); ?>" alt="">
            <a class="addimage button" onclick="custom_postimage_add_image('<?php echo $meta_key; ?>');"><?php _e('Add image','yourdomain'); ?></a><br>
            <a class="removeimage" style="color:#a00;cursor:pointer;display: <?php echo ($image_meta_val!=''?'block':'none'); ?>" onclick="custom_postimage_remove_image('<?php echo $meta_key; ?>');"><?php _e('remove image','yourdomain'); ?></a>
            <input type="hidden" name="<?php echo $meta_key; ?>" id="<?php echo $meta_key; ?>" value="<?php echo $image_meta_val; ?>" />
        </div>
    <?php } ?>
    <script>
    function custom_postimage_add_image(key){

        var $wrapper = jQuery('#'+key+'_wrapper');

        custom_postimage_uploader = wp.media.frames.file_frame = wp.media({
            title: '<?php _e('select image','yourdomain'); ?>',
            button: {
                text: '<?php _e('select image','yourdomain'); ?>'
            },
            multiple: false
        });
        custom_postimage_uploader.on('select', function() {

            var attachment = custom_postimage_uploader.state().get('selection').first().toJSON();
            var img_url = attachment['url'];
            var img_id = attachment['id'];
            $wrapper.find('input#'+key).val(img_id);
            $wrapper.find('img').attr('src',img_url);
            $wrapper.find('img').show();
            $wrapper.find('a.removeimage').show();
        });
        custom_postimage_uploader.on('open', function(){
            var selection = custom_postimage_uploader.state().get('selection');
            var selected = $wrapper.find('input#'+key).val();
            if(selected){
                selection.add(wp.media.attachment(selected));
            }
        });
        custom_postimage_uploader.open();
        return false;
    }

    function custom_postimage_remove_image(key){
        var $wrapper = jQuery('#'+key+'_wrapper');
        $wrapper.find('input#'+key).val('');
        $wrapper.find('img').hide();
        $wrapper.find('a.removeimage').hide();
        return false;
    }
    </script>
    <?php
    wp_nonce_field( 'custom_postimage_meta_box', 'custom_postimage_meta_box_nonce' );
}

function custom_postimage_meta_box_save($post_id){

    if ( ! current_user_can( 'edit_posts', $post_id ) ){ return 'not permitted'; }

    if (isset( $_POST['custom_postimage_meta_box_nonce'] ) && wp_verify_nonce($_POST['custom_postimage_meta_box_nonce'],'custom_postimage_meta_box' )){

        //same array as in custom_postimage_meta_box_func($post)
        $meta_keys = array('second_featured_image','third_featured_image','forth_featured_image','fifth_featured_image');
        foreach($meta_keys as $meta_key){
            if(isset($_POST[$meta_key]) && intval($_POST[$meta_key])!=''){
                update_post_meta( $post_id, $meta_key, intval($_POST[$meta_key]));
            }else{
                update_post_meta( $post_id, $meta_key, '');
            }
        }
    }
}

?>
aa code