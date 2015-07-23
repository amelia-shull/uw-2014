<?php

class UW_Page_Attributes_Meta_Box
{

  const ID = 'pageparentdiv';
  const TITLE = 'Page Attributes';
  const POSTTYPE = 'page';
  const POSITION = 'side';
  const PRIORITY = 'core';

  function __construct()
  {
    $this->HIDDEN = array( 'No Sidebar' );
    add_action( 'add_meta_boxes', array( $this, 'replace_meta_box' ) );
    add_action( 'save_post', array( $this, 'save_postdata' ) );

  }

  function replace_meta_box()
  {
      remove_meta_box( 'pageparentdiv', 'page', 'side');
	    add_meta_box( 'uwpageparentdiv', 'Page Attributes', array( $this, 'page_attributes_meta_box' ), 'page', 'side', 'core' );
  }

  function page_attributes_meta_box( $post )
  {

    $post_type_object = get_post_type_object( $post->post_type );

    if ( $post_type_object->hierarchical )
    {
      $dropdown_args = array(
        'post_type'        => $post->post_type,
        'exclude_tree'     => $post->ID,
        'selected'         => $post->post_parent,
        'name'             => 'parent_id',
        'show_option_none' => __('(no parent)'),
        'sort_column'      => 'menu_order, post_title, sidebar',
        'echo'             => 0,
      );

          /**
           * Filter the arguments used to generate a Pages drop-down element.
           *
           * @since 3.3.0
           *
           * @see wp_dropdown_pages()
           *
           * @param array   $dropdown_args Array of arguments used to generate the pages drop-down.
           * @param WP_Post $post          The current WP_Post object.
           */
          $dropdown_args = apply_filters( 'page_attributes_dropdown_pages_args', $dropdown_args, $post );

          $pages = wp_dropdown_pages( $dropdown_args );

          if ( ! empty( $pages ) )
          { ?>

            <p><strong><?php _e('Parent') ?></strong></p>
            <label class="screen-reader-text" for="parent_id"><?php _e('Parent') ?></label>

            <?php echo $pages; ?>

            <?php
          } // end empty pages check
    } // end hierarchical check.

    if ( 'page' == $post->post_type && 0 != count( get_page_templates( $post ) ) ) {
      $template = !empty($post->page_template) ? $post->page_template : 'default';
      ?>


    <p><strong><?php _e('Template') ?></strong></p>

    <label class="screen-reader-text" for="page_template"><?php _e('Page Template') ?></label>

    <p><input type='radio' value='default' name="page_template" <?php checked( $template, 'default', true ); ?> ><?php _e('Default Template'); ?></input></p>

    <?php $this->page_template_dropdown($template); ?>

    <?php } 
    $sidebar = get_post_meta($post->ID, "sidebar");
    wp_nonce_field( 'sidebar_nonce' , 'sidebar_name' );
    ?>

    <p><strong><?php _e('Sidebar') ?></strong></p>

    <label class="screen-reader-text" for="sidebar"><?php _e('Sidebar') ?></label>

    <p><input type="checkbox" name="sidebar" <?php if( $sidebar[0] == "on" ) { ?>checked="checked"<?php } ?> /><?php _e('No Sidebar') ?></p>

    <p><strong><?php _e('Order') ?></strong></p>

    <p><label class="screen-reader-text" for="menu_order"><?php _e('Order') ?></label><input name="menu_order" type="text" size="4" id="menu_order" value="<?php echo esc_attr($post->menu_order) ?>" /></p>

    <p><?php if ( 'page' == $post->post_type ) _e( 'Need help? Use the Help tab in the upper right of your screen.' ); ?></p>

    <?php
  }

  function page_template_dropdown( $default = '' ) {

    $templates = get_page_templates( get_post() );

    ksort( $templates );

    foreach ( array_keys( $templates ) as $template )
    {
      if( in_array($template, $this->HIDDEN ))
      {
        continue;
      }

      $checked = checked( $default, $templates[ $template ], false );
      echo "<p><input type='radio' name='page_template' value='" . $templates[ $template ] . "' $checked >$template</input></p>";
    }

  }

  function save_postdata( $post_ID = 0 ){
    $post_ID = (int) $post_ID;
    $post_type = get_post_type( $post_ID );
    $post_status = get_post_status( $post_ID );
    if (isset($post->post_type) && 'page' != $post->post_type ) {
        return $post_ID;
    }
    if ( ! empty( $_POST ) && check_admin_referer( 'sidebar_nonce', 'sidebar_name') ) { //limit to only pages
      if ($post_type) {
      update_post_meta($post_ID, "sidebar", $_POST["sidebar"]);
      }
    }
   return $post_ID;
  }

}

new UW_Page_Attributes_Meta_Box;
