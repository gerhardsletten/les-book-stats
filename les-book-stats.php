<?php
/*
Plugin Name: Foreningen !Les bok statistikk
Plugin URI: http://www.foreningenles.no
Description: Skjema for statistikk for bøker
Author: Gerhard Sletten
Version: 1.0
Author URI: http://www.metabits.no
*/
if (!class_exists("LesBookStats")) {
	class LesBookStats {
		var $_wpdb;
    var $prefix;
    var $version;
		function __construct() {
			global $wpdb;
			$this->_wpdb = $wpdb;
      $this->version = '1.0.0';
      $this->prefix = "les_bookstats_";
      add_action('admin_enqueue_scripts', array( &$this, "enqueue_ressources" ));
			add_action( "admin_menu", array( &$this, "create_admin_menu" ) );

      $post_type = $this->get_option( 'post_type');
      $tax_author = $this->get_option( 'tax_author');
      if($post_type && $tax_author && function_exists("register_field_group")) {
        $this->custom_fields(array('field' => $tax_author, 'param' => 'ef_taxonomy'));
        $this->custom_fields(array('field' => $post_type, 'title' => 'Kjønn hovedperson', 'name' => 'sex_main_character'));
      }
		}

    function enqueue_ressources() {
      $dev = false;
      if ($dev) {
        wp_enqueue_script('les-book-stats', '//0.0.0.0:8082/main.js', array(), $this->version, true);
      } else {
        wp_enqueue_script('les-book-stats', plugin_dir_url( __FILE__ ) . 'build/bundle.js', array(), $this->version, true);
        wp_enqueue_style('les-book-stats', plugin_dir_url( __FILE__ ) . 'build/bundle.css', false, $version);
      }
      
    }

		function create_admin_menu() {
			add_menu_page( "Bok Statistikk", "Bok Statistikk", "level_10", "book-stats", array( &$this, "main_page" ) );
      add_submenu_page( "book-stats", "Innstillinger", 'Innstillinger', "level_10", "book-stats-settings", array( &$this, "settings_admin_page" ) );
		}
		
		function main_page() {
      $post_type = $this->get_option( 'post_type');
      $tax_author = $this->get_option('tax_author');
      $tax_genre = $this->get_option('tax_genre');
      $data = array();
      $out = '';
      if ($post_type && $tax_author && $tax_genre) {
        $posts = get_posts(array(
          'posts_per_page' => 500,
          'post_type' => $post_type
        ));
        $general = array('label' => 'Bøker per år', 'stats' => array());
        $tmp_year = array();
        $data1 = array('label' => 'Kjønn forfatter','stats' => array());
        $data2 = array('label' => 'Kjønn hovedperson','stats' => array());
        $data3 = array('label' => 'Sjanger','stats' => array());
        foreach($posts as $post) {
          $year = intval(date_i18n('Y', strtotime($post->post_date)));
          /* Books per year */
          if (!$tmp_year[$year]) {
            $tmp_year[$year] = 0;
          }
          $tmp_year[$year]++;
          /* Sex author */
          $terms = wp_get_post_terms($post->ID,$tax_author);
          if ($terms) {
            foreach($terms as $term) {
              $value = get_field('sex', $tax_author . '_' . $term->term_id);
              $data1['stats'][] = array(
                'year' => $year,
                'value' => $value ? $value : 'n/a'
              );
            }
          }
          /* Sex main character */
          $sex_character = get_field('sex_main_character', $post->ID);
          $data2['stats'][] = array(
            'year' => $year,
            'value' => $sex_character ? $sex_character : 'n/a'
          );
          /* Genre */
          $terms = wp_get_post_terms($post->ID,$tax_genre);
          if ($terms) {
            foreach($terms as $term) {
              $data3['stats'][] = array(
                'year' => $year,
                'value' => $term->name
              );
            }
          }
        }
        foreach($tmp_year as $key => $val) {
          $general['stats'][] = array(
            'year' => $key,
            'value' => $val
          );
        }
        $data = array(
          'general' => $general,
          'stats' => array($data1,$data2,$data3)
        );
      }
      
      echo sprintf("
          <div class='wrap'>
            <h1>Statistikk over bøker</h1>
            <p>Viser sammendrag over forskjellige år</p>
            <div id='les-book-stats-main'></div>
            <script>
            var lesBookStats = %s
            </script>
          </div>
        ",
        json_encode($data)
      );
		}

    function settings_admin_page() {
      $out = "<div class='wrap'><form method='post'>";
      if(isset($_POST['update_settings'])) {
        $out .= $this->general_message("The settings is updated", "The settings is now updated.");
        $this->update_option( 'post_type', sanitize_text_field($_POST['bi_post_type']) );
        $this->update_option( 'tax_author', sanitize_text_field($_POST['bi_tax_author']) );
        $this->update_option( 'tax_genre', sanitize_text_field($_POST['bi_tax_genre']) );
      }
      
      $out .= sprintf( '<table class="form-table">
        <tbody>
          %s
          %s
          %s
        </tbody>
      </table>',
      $this->display_option_row("Bok post type", "bi_post_type", get_post_types(array('public'=>true)),$this->get_option( 'post_type')),
      $this->display_text_row("Taksonomi forfatter", "bi_tax_author",  $this->get_option('tax_author')),
      $this->display_text_row("Taksonomi sjanger", "bi_tax_genre",  $this->get_option('tax_genre'))
      );

      // Actions
      $actions = 
      "<div class='alignleft actions' style='margin: 10px 0 10px'>
        <input type='submit' value='Oppdater innstillinger' name='update_settings' class='button-primary action' />
      </div>";

      $out .= $actions;
      $out .= "</form></div>";

      echo "<h2>Innstillinger</h2>";
      echo $out;
    }

    function general_message($title, $content) {
      return sprintf( '<div id="message" class="updated below-h2"><p><strong>%s</strong><br />%s</p></div>',$title,$content);
    }

    function display_row($label, $content) {
      return sprintf( '<tr>
        <th scope="row">%s</th>
        <td>%s</td>
      </tr>', $label,$content);
    }

    function display_text_row($title, $key, $value,$comment = "") {
      return $this->display_row(
        sprintf( '<label for="%s">%s</label>', $key,$title),
        sprintf( '<input name="%s" type="text" id="%s" value="%s" class="regular-text">%s', $key,$key,$value,$comment)
      );
    }
    function display_option_row($title, $key, $options, $selected) {
      $options_html = "";
      foreach($options as $option_key => $option_value) {
        $options_html .= sprintf( '<option %s value="%s">%s</option>', ($option_key == $selected ? 'selected="selected"' : ""),$option_key,$option_value);
      }
      return $this->display_row(
        sprintf( '<label for="%s">%s</label>', $key,$title),
        sprintf( '<select name="%s" id="%s">%s</select>', $key,$key,$options_html)
      );
    }

    function get_option( $option, $default = false ) {
      return get_option( $this->prefix . $option, $default );
    }

    function update_option( $key, $option ) {
      update_option( $this->prefix . $key, $option );
    }

    function custom_fields ($params = array()) {
      $options = array_merge(array(
        'title' => 'Kjønn',
        'name' => 'sex',
        'param' => 'post_type',
        'field' => 'author'
      ), $params);
      register_field_group(array (
        'id' => sprintf('acf_stats_%s_%s', $options['field'], $options['param']),
        'title' => $options['title'],
        'fields' => array (
          array (
            'key' => sprintf('%sfield_%s',$this->prefix,$options['field']),
            'label' => $options['title'],
            'name' => $options['name'],
            'type' => 'select',
            'choices' => array (
              'female' => 'Kvinne',
              'male' => 'Mann',
              'mix' => 'Ubestemt kjønn',
            ),
            'default_value' => '',
            'allow_null' => 0,
            'multiple' => 0,
          ),
        ),
        'location' => array (
          array (
            array (
              'param' => $options['param'],
              'operator' => '==',
              'value' => $options['field'],
              'order_no' => 0,
              'group_no' => 0,
            ),
          ),
        ),
        'options' => array (
          'position' => 'normal',
          'layout' => 'default',
          'hide_on_screen' => array (
          ),
        ),
        'menu_order' => 0,
      ));
    }
	}
}
if (class_exists("LesBookStats")) {
	$orderform = new LesBookStats();
}



?>