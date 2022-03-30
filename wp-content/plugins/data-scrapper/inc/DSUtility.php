<?php

/**
 * Defines Utility Functions
 *
 * @author Neeraj Mourya <neerajmorya@gmail.com>
 */
class DSUtility {

    /**
     * Returns substring between two given strings
     * 
     * @param type $str Source String
     * @param type $from First Substring
     * @param type $to Second Substring
     * @return string returns the substring between two string
     */
    public static function getStringBetween($str, $from, $to) {
        $sub = substr($str, strpos($str, $from) + strlen($from), strlen($str));
        return substr($sub, 0, strpos($sub, $to));
    }

    /**
     * Relaces first occurance of substring with given string
     * 
     * @param string $from  substring to be replaced
     * @param type $to substring to take place
     * @param type $content Source String
     * @return string returns string with replaced substring
     */
    public static function str_replace_first($from, $to, $content) {
        $from = '/' . preg_quote($from, '/') . '/';
        return preg_replace($from, $to, $content, 1);
    }

    public static function save_file($imageurl, $scrap_started) {
		
//        error_log("IMAGE URL : " . $imageurl);
        //check if image already exists then return its attachment id
        $attach_id = self::image_exists($imageurl);
        if (!empty($attach_id)) {
//            $update_time = get_post_meta($attach_id, "scrap_updated", true);
//            if ($scrap_started < $update_time) {
//                return $attach_id;
//            } else {
////                error_log("Image not found thus deleting");
//                wp_delete_attachment($attach_id, true);
//            }

            return $attach_id;
        }

        include_once( ABSPATH . 'wp-admin/includes/image.php' );
		$image_size = getimagesize($imageurl);
		
		if(!empty($image_size['mime'])){
			$image_mime = explode('/', $image_size['mime']);
			$imagetype = end($image_mime);
			$uniq_name = date('dmY') . '' . (int) microtime(true) . self::generateRandomString();
			$filename = $uniq_name . '.' . $imagetype;
			
			$uploaddir = wp_upload_dir();
			$uploadfile = $uploaddir['path'] . '/' . $filename;
			$contents = file_get_contents($imageurl);
			$savefile = fopen($uploadfile, 'w');
			fwrite($savefile, $contents);
			fclose($savefile);

			$wp_filetype = wp_check_filetype(basename($filename), null);

			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => $filename,
				'post_content' => '',
				'post_status' => 'inherit',
				'meta_input' => array(
					'source_url' => $imageurl,
					'scrap_updated' => time(),
				),
			);

			$attach_id = wp_insert_attachment($attachment, $uploadfile);
			$imagenew = get_post($attach_id);
			$fullsizepath = get_attached_file($imagenew->ID);
			$attach_data = wp_generate_attachment_metadata($attach_id, $fullsizepath);
			wp_update_attachment_metadata($attach_id, $attach_data);
			
			return $attach_id;
	
		}
		
        else {
			return false;
		}
//        error_log("New Attachment ID : " . $attach_id);
        
    }

    public static function image_exists($imageurl) {
        $attachments = get_posts([
            'post_type' => 'attachment',
            'meta_key' => 'source_url',
            'meta_value' => $imageurl, 
        ]);

        if (sizeof($attachments) > 0 && !empty($attachments[0]->ID)) {
            return $attachments[0]->ID;
        }
        return false;
    }

    public static function stop_if_safety() {
        //stopping script if safety on
//        $GLOBALS['wp_object_cache']->delete( 'ds_safety_switch', 'options' );
//        wp_cache_delete("ds_safety_switch");
//        $switch_value = get_option("ds_safety_switch","off");


        global $wpdb;
        $prefix = $wpdb->prefix;
        $switch_value = "off";
        $query = $wpdb->get_results("SELECT option_value FROM {$prefix}options WHERE option_name='ds_safety_switch'");
        if ($query) {
            foreach ($query as $row) {
                $switch_value = $row->option_value;
            }
        }

//        error_log("Found switch value : " . $switch_value);
        if ($switch_value == "on") {
//            error_log("Script Safety is ON can not execute");
            die();
            exit(0);
        }
    }

    public static function update_script_status() {
        //updating script status
        update_option("ds_script_last_active", time(), false);
    }

    public static function get_term_parents_ids($term_id) {
        $term = get_term($term_id, "product_cat");
        $terms = array();
        $terms[] = $term_id;
        while ($term->parent > 0) {
            $term = get_term($term->parent, "product_cat");
            $terms[] = $term->term_id;
        }
        $terms = array_reverse($terms);
        return $terms;
    }

    public static function get_term_parents_name($term_id) {
        $term = get_term($term_id, "product_cat");
        $parents[] = $term->name;
        $parents = array();
        while ($term->parent > 0) {
            $term = get_term($term->parent, "product_cat");
            $parents[] = $term->name;
        }
        $parents = array_reverse($parents);
        return $parents;
    }

    public static function delete_old_products() {
        //7 days old time from now.
        $current_time = time();
//        $old_days = 7 * 24 * 60 * 60;
        $old_days = 7 * 24 * 60 * 60;
        $old_time = $current_time - $old_days;

        //get list of all products updated more than 7 days ago
        $products = get_posts([
            'post_type' => 'product',
            'meta_key' => 'scrap_updated',
            'meta_value' => $old_time,
            'meta_compare' => '<',
            'posts_per_page' => -1
        ]);

        foreach ($products as $product) {
            //delete products
            wp_delete_post($product->ID, false);
        }
//        error_log("PRODUCTS COUNT : " . sizeof($products));
    }

    public static function delete_old_categories() {
        //7 days old time from now.
        $current_time = time();
//        $old_days = 7 * 24 * 60 * 60;
        $old_days = 7 * 24 * 60 * 60;
        $old_time = $current_time - $old_days;

        //get list of all products updated more than 7 days ago
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'meta_key' => 'scrap_updated',
            'meta_value' => $old_time,
            'meta_compare' => '<',
        ]);

        foreach ($terms as $term) {
            wp_delete_term($term->term_id, 'product_cat');
        }
    }

    public static function update_category_thumbnails() {
        //get list of all products updated more than 7 days ago
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ]);
        $current_time = time();
        foreach ($terms as $term) {
            $source_url = get_term_meta($term->term_id, "source_image_url", true);
//            if($term->term_id == 4778){
//                echo "Source URL : " . $source_url . "<br>";
//                echo "Thumbnail ID : " . get_term_meta($term->term_id, "thumbnail_id", true);
//                die;
//            }
            if (isset($source_url) && !empty($source_url) && $source_url != false && $source_url != 0) {
                $attachment = null;
                $attachments = get_posts([
                    'post_type' => 'attachment',
                    'meta_key' => 'source_url',
                    'meta_value' => $source_url,
                ]);
                if (isset($attachments) && is_array($attachments) && sizeof($attachments) > 0) {
                    update_term_meta($term->term_id, "thumbnail_id", $attachments[0]->ID);
                } else {
//                    self::save_file($source_url, $current_time);
                }
            }
        }
    }

    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function term_exists($source_url,$source_id,$category_name,$cat_slug, $parent_id) {
         
		
		$terms = get_terms([
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
			/*'parent' => $parent_id,
			'search' => $category_name,*/
			//removing it because we want to merge them both source websites' categories
			'meta_query' => array(
				array(
					'key' => 'source_id',
					'value' => $source_id,
					'compare' => '='
				)
			)
		]);
		
        if (isset($terms) && is_array($terms) && !empty($terms)) {
            
			return $terms;
        } 
		
        if (term_exists($cat_slug, "product_cat", $parent_id)) {
            // $term = get_term_by("name", $category_name, "product_cat");
			$term = get_term_by("slug", $cat_slug, "product_cat");
			
            return $term;
        }

        return false;
    }

    public static function delete_invalid_categories() {

        $de_cats = array_merge(DSGastroScrapper::$de_cats, DSMoebelScrapper::$de_cats);

        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'parent' => 0,
                // 'meta_query' => array(
                //     array(
                //         'key' => 'source_url',
                //         'value' => "fr-de-eur",
                //         'compare' => 'LIKE'
                //     )
                // )
        ]);

//        echo "<strong>Deleted Following de categories:</strong><br>";
//        echo "<table>";
        foreach ($terms as $term) {
            if (!in_array($term->name, $de_cats)) {
                continue;
            }
//            $source_url = get_term_meta($term->term_id, "source_url", true);
            wp_delete_term($term->term_id, 'product_cat');
//            echo "<tr>";
//            echo "<td>{$term->term_id}</td>";
//            echo "<td>{$term->name}</td>";
//            echo "<td>{$source_url}</td>";
//            echo "</tr>";
        }
//        echo "</table>";
    }
    
    public static function delete_categories_menu(){
        $menuname = "Dynamic Categories Menu";
        // Does the menu exist already?
        $menu_exists = wp_get_nav_menu_object($menuname);
        

        // If it doesn't exist, let's create it.
        if ($menu_exists) {
            wp_delete_nav_menu($menu_exists->term_id);
        }
    }

    public static function update_categories_menu() {
        $menuname = "Dynamic Categories Menu";
        // Does the menu exist already?
        $menu_exists = wp_get_nav_menu_object($menuname);
        /*
		$menu_id = 52552;

        // If it doesn't exist, let's create it.
        if (!$menu_exists) {
            //$menu_id = wp_create_nav_menu($menuname);
			echo "Menu not found";
			exit();
        } else {
            $menu_id = $menu_exists->term_id;
        }*/
		
		$menu_id = 52552;

        $menu_items = wp_get_nav_menu_items($menu_id,[
            "nopaging" => true
        ]);

        //updating main menu
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'parent' => 0,
            'meta_query' => array(
                array(
                    'key' => 'is_main_category',
                    'value' => 'yes',
                    'compare' => '='
                ),
            )
        ]);
        
        
        foreach ($terms as $term) {
            $item_id = self::menu_item_exists($term, $menu_items);
//            error_log("Term Name : " . $term->name);
//            error_log("Item ID : ". $item_id);
//            echo "<br>Item ID Before : {$item_id}<br>"; 
            $item_id = wp_update_nav_menu_item($menu_id, $item_id, [
                'menu-item-title' => $term->name,
                'menu-item-object-id' => $term->term_id,
                'menu-item-object' => 'product_cat',
                'menu-item-status' => 'publish',
                'menu-item-type' => 'taxonomy',
            ]);
//            echo "<br>Item ID After : {$item_id}<br>";
            $thumbnail_id = get_term_meta($term->term_id, "thumbnail_id", true);
            update_post_meta($item_id, "_nm_menu_item_thumbnail", $thumbnail_id);

            //updating sub menu            
            $subterms = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'parent' => $term->term_id,
            ]);
            foreach ($subterms as $subterm) {
                $sub_item_id = self::menu_item_exists($subterm, $menu_items);
                $sub_item_id = wp_update_nav_menu_item($menu_id, $sub_item_id, [
                    'menu-item-title' => $subterm->name,
                    'menu-item-object-id' => $subterm->term_id,
                    'menu-item-object' => 'product_cat',
                    'menu-item-status' => 'publish',
                    'menu-item-type' => 'taxonomy',
                    'menu-item-parent-id' => $item_id
                ]);
                $sub_thumbnail_id = get_term_meta($subterm->term_id, "thumbnail_id", true);
                update_post_meta($sub_item_id, "_nm_menu_item_thumbnail", $sub_thumbnail_id);
            }
        }

//        echo "<pre>";
//        print_r($menu_items);
//        echo "<pre>";
//        die;
    }

    public static function menu_item_exists($term, $items) {
        $term_link = get_term_link($term->term_id);
        foreach ($items as $item) {
            if ($term_link == $item->url) {
                //echo "<br>{$term->name} found same <br>";
                return ((int) $item->db_id);
            }
        }
        return 0;
    }

	public static function list_terms($source){
		$terms = get_terms([
            "taxonomy" => "product_cat",
            "hide_empty" => false,
            'meta_query' => array(
                array(
                    'key' => 'source_website',
                    'value' => $source,
                    'compare' => '='
                )
            )
        ]);
		$i=1;
		foreach($terms as $t){
			echo "<p> $i - ".$t->term_id ." > ".$t->name; 
			$i++;
			//wp_delete_term( $t->term_id, 'product_cat' );
			$parent = get_term_by( 'id', $t->parent, "product_cat");
			if($parent)
				echo " - Parent : ".$parent->term_id ." > ".$parent->name;
			echo "</p>"; 
			
		} 
		
	}
	
	public static function remove_duplicate_terms($source){
		
		
		update_option($source."_ldt_iteration",1000);
		
		//update_option($source."_ldt_offset",0);
		
		$total_terms = wp_count_terms(array(
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		) );
		
		//echo "<p>Total Terms : " . $total_terms . "</p>";
		
		$offset = !empty(get_option($source."_ldt_offset") )? intval(get_option($source."_ldt_offset")) : 0;
		
		$iteration = !empty(get_option($source."_ldt_iteration") )? intval(get_option($source."_ldt_iteration")) : 0;
		
		if($offset >= $total_terms){ 
			update_option($source."_ldt_offset",0);
			exit ("Finished - Offset is above total items");
		}
		
		
		//echo "Processing ". $offset . " - ". ($offset+$iteration) ." terms";
		
		$number = $total_terms - $offset;
		
		$terms = get_terms([
            "taxonomy" => "product_cat",
            "hide_empty" => false,
			"offset" => $offset,
			"number" => $number,
            'meta_query' => array(
                array(
                    'key' => 'source_website',
                    'value' => $source,
                    'compare' => '='
                )
            )
        ]);
		
		//echo "<p>Found Terms : " .(count($terms)) . "</p>";
		
		update_option($source."_ldt_offset",($offset+$iteration));
		$i=1;
		foreach($terms as $t){
			//echo "<p> Processing $i - ".$t->term_id ." > ".$t->name; 
			$i++;
			if($i>$iteration || $i>=$total_terms){ 
				exit();
			}
			//wp_delete_term( $t->term_id, 'product_cat' );
			$parent = get_term_by( 'id', $t->parent, "product_cat");
			
			$source_id = get_term_meta($t->term_id,"source_id",true);
			
			if($parent && $source_id) {
				
				
				$parent_id = $parent->term_id;
				
				//echo "<p> Parent : " . $parent_id ."</p>";
				
				//echo "<p> Source : " . $source_id ."</p>";
				
				
				//echo " - Parent : ".$parent->term_id ." > ".$parent->name;
				//echo " - Source ID : ".$source_id;
				$dup_terms = get_terms([
					"taxonomy" => "product_cat",
					"hide_empty" => false,
					"parent" => $parent_id,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key' => 'source_website',
							'value' => $source,
							'compare' => '='
						),
						array(
							'key' => 'source_id',
							'value' => $source_id,
							'compare' => '='
						)
					)
				]);
				
				foreach($dup_terms as $dt){
					if($dt->term_id == $t->term_id) {
						continue;
					}
					DSUtility::delete_duplicate_terms($dt->term_id,$parent_id);
				}
			}
			//echo "</p>"; 
			
			
			
			
		}
	}
	
	public static function delete_duplicate_terms($term_id,$original_term_id){
		
		if(empty($original_term_id)){
			return;
		}
		
		//echo "<p>Processing > " . $term_id;
		
		// List child of this one 
		
		$child_terms = get_terms([
			"taxonomy" => "product_cat",
			"hide_empty" => false,
			"parent" => $term_id
		]);
		
		// Assign child terms to original parent
		
		if(!empty($child_terms)) {
			foreach($child_terms as $dct){
				$update = wp_update_term( 1, 'category', array(
					'parent' => $original_term_id
				) );
				 
				if ( ! is_wp_error( $update ) ) {
					//echo "<p> Updated Parent : " . $original_term_id ." to ". $dct->term_id ." > " . $dct->name;
				}
			}			
		}
		
		// Assign posts to original parent
		
		$term_posts = get_posts([
			"post_type" => "product",
			"product_cat" => $term_id,
			"post_per_page" => -1
		]);
		
		if(!empty($term_posts)){
			foreach($term_posts as $tpost){
				wp_set_post_terms( $tpost->ID, $original_term_id, "product_cat" );
				//echo "Setting " . $original_term_id ." to " . $tpost->ID;
			}
		}
		
		// delete term 
		
		wp_delete_term( $term_id, 'product_cat' );
		
		//echo "<p>Term # ".$term_id." is deleted</p>";
		
	}
	
	public static function send_finished_email($source){
		$multiple_recipients = array(
			'Rafalaffeier@gmail.com',
			'info.rdcorp@gmail.com'
		);
		$subject = 'Scrapper Finished - ' .$source;
		$body = 'The Scrapper for ' .$source. " is finished.";
		wp_mail( $multiple_recipients, $subject, $body );
	}
	
	public static function sync_cats($cat_ids){
		
		if(empty($cat_ids)) return;
		
		$cat_ids_arr = explode(",",$cat_ids);
		$ds_gastro_scrapper = DSGastroScrapper::get_instance();
		$ds_moebel_scrapper = DSMoebelScrapper::get_instance();
		
		foreach($cat_ids_arr as $cat_id){
			$scrap_source_website = get_term_meta($cat_id,"source_website",true);
			echo "<p>Processing : ".$cat_id."</p>";
			
			if(strpos($scrap_source_website,"ggmmoebel")){
				echo "<p>Scrap Type : ggmmoebel</p>";
				$scrap_type = "ggmmoebel";
				$ds_moebel_scrapper->scrap_single_category(intval($cat_id));
				update_term_meta($cat_id, "scrap_updated", time());
			}
			if(strpos($scrap_source_website,"ggmgastro")){
				echo "<p>Scrap Type : ggmgastro</p>";
				$scrap_type = "ggmgastro";
				$ds_gastro_scrapper->scrap_single_category(intval($cat_id));
				update_term_meta($cat_id, "scrap_updated", time());
			}
		}
		
		exit();
	}
	
	
}
