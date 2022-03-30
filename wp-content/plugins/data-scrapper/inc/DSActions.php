<?php

/**
 * Description of DSActions
 *
 * @author Neeraj Mourya <neerajmorya@gmail.com>
 */
class DSActions {

    /**
     *
     * @var dsActions
     */
    private static $dsActions;

    /**
     * Get the active instance of DSActions
     *
     * @return DSActions
     * @since 1.0.0
     */
    public static function get_instance() {
        if (isset(self::$dsActions) && is_object(self::$dsActions)) {
            
        } else {
            self::$dsActions = new DSActions();
        }
        return self::$dsActions;
    }

    /**
     * DSActions Constructor
     */
    public function __construct() {
        //add_action("init", array($this, "test_scrap"));        
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        add_action("wp_ajax_ds_run_gastro_scrap",array($this,"ds_run_gastro_scrap"));
        add_action("wp_ajax_ds_run_moebel_scrap",array($this,"ds_run_moebel_scrap"));
        
        add_action("wp_ajax_ds_reset_gastro_scrap",array($this,"ds_reset_gastro_scrap"));
        add_action("wp_ajax_ds_reset_moebel_scrap",array($this,"ds_reset_moebel_scrap"));
        
        add_action("wp_ajax_ds_get_script_status",array($this, "ds_get_script_status"));
        
        add_action("wp_ajax_ds_get_safety", array($this, "ds_get_safety"));
        add_action("wp_ajax_ds_set_safety_on",array($this,"ds_set_safety_on"));
        add_action("wp_ajax_ds_set_safety_off",array($this,"ds_set_safety_off"));
        
        add_action("template_redirect", array($this,"template_redirect"));
		
		add_filter('bulk_actions-edit-product_cat', function($bulk_actions) {
			$bulk_actions['gbm-sync-cat'] = __('Syncronize', 'txtdomain');
			return $bulk_actions;
		});
		
		add_filter('handle_bulk_actions-edit-product_cat',function($redirect_url, $action, $post_ids) {
			return $this->do_bulk_edit_cats($redirect_url, $action, $post_ids);
		},10,3);
    }
    
    
    public function test_scrap(){
    //    DSUtility::update_categories_menu();
//        DSUtility::update_category_thumbnails();
//        DSUtility::delete_invalid_categories();
        // DSUtility::delete_categories_menu();
    }

    
    public function admin_enqueue_scripts(){
        wp_enqueue_style("ds-admin-style", DataScrapper::$PLUGIN_DIR_URI . "css/admin.css", array());
    }
 
    public function ds_run_gastro_scrap(){
        $ds_gastro_scrapper = DSGastroScrapper::get_instance();        
        $ds_gastro_scrapper->start();
//        $ds_gastro_scrapper->scrap_products(3686);
    }
    
    public function ds_run_moebel_scrap(){
        $ds_moebel_scrapper = DSMoebelScrapper::get_instance();
        $ds_moebel_scrapper->start();
    }
    
    public function ds_run_all_scrap(){
        $scrap_step = get_option("all_scrap_step", 1);
		ini_set('max_execution_time', 300);
        switch($scrap_step){
            case 1:
                $this->ds_run_gastro_scrap();
                update_option("all_scrap_step", 2);
            case 2:
                $this->ds_run_moebel_scrap();
                update_option("all_scrap_step", 1);            
        }
    }
    
    public function ds_reset_gastro_scrap(){
        error_log("Resetting ggmgastro scrap");
        delete_option("ggmgastro_scrap_info");
    }
    
    public function ds_reset_moebel_scrap(){
        error_log("Resetting ggmmoebel scrap");
        delete_option("ggmmoebel_scrap_info");
    }
    
    public function ds_get_script_status(){
        $current_time = time();
        $GLOBALS['wp_object_cache']->delete( 'ds_script_last_active', 'options' );
        $value = get_option("ds_script_last_active",0);
        $last_date = date("Y-m-d h:i:s", $value);
        $time_ago = ($current_time - $value);
        $time_string = "";
        $color = "red";
        
        if($time_ago == 0){
            $color = "red";
            $time_string = " Unknown ";
        }else if($time_ago < 20){
            $color = "green";
            $time_string = " Active " .$time_ago . " seconds ago.";
        }else if($time_ago < 60){
            $color = "orange";
            $time_string = " Active " .$time_ago . " seconds ago.";
        }else if($time_ago > 60){
            $color = "red";
            $time_in_min = round($time_ago/60);
            $time_string = " Active " . $time_in_min . " minutes ago.";
        }
        $time_string = "<div style='vertical-align:middle'><div style='display:inline-block;vertical-align:middle;width:16px;height:16px;background-color:{$color};border:1px solid #aaa;'></div> <span style='vertical-align:middle;'>{$time_string}</span></div>";
        
        wp_send_json_success([
            "status" => "success",
            "value" => $time_string,
            "last_active_seconds" => $value
        ]);
        die();
    }
    
    public function ds_get_safety(){
        $GLOBALS['wp_object_cache']->delete( 'ds_safety_switch', 'options' );
        $value = get_option("ds_safety_switch","off");
        wp_send_json_success([
            "status" => "success",
            "value" => $value,
        ]);
        die();
    }
    
    public function ds_set_safety_on(){        
        update_option("ds_safety_switch", "on", false);
        error_log("Safety switched on");
        wp_send_json_success([
            "status" => "success"
        ]);
        die();
    }
    
    public function ds_set_safety_off(){
        update_option("ds_safety_switch", "off", false);
        error_log("Safety switched off");
        wp_send_json_success([
            "status" => "success"
        ]);
        die();
    }
    
    public function template_redirect(){
		
        // Run URLS
        // https://localhost/wptest?ds_scrap=yes&ds_scrap_key=4WaBzTGWQKvQQE74&ds_scrap_type=ggmgastro
        // https://localhost/wptest?ds_scrap=yes&ds_scrap_key=4WaBzTGWQKvQQE74&ds_scrap_type=ggmmoebel
		//exit();
        if(isset($_REQUEST['ds_scrap']) && isset($_REQUEST['ds_scrap_type']) && isset($_REQUEST['ds_scrap_key'])){
			
            $ds_scrap = sanitize_text_field($_REQUEST['ds_scrap']);
            $ds_scrap_type = sanitize_text_field($_REQUEST['ds_scrap_type']);
            $ds_scrap_key = sanitize_text_field($_REQUEST['ds_scrap_key']);
            
            if($ds_scrap != "yes" || $ds_scrap_key != "4WaBzTGWQKvQQE74"){
                return false;
            }
            switch($ds_scrap_type){
                case 'ggmgastro':
                    $this->ds_run_gastro_scrap();
                    break;
                case 'ggmmoebel':
                    $this->ds_run_moebel_scrap();
                    break;
				case 'remove-duplicates-ggmmoebel':
					// Remove duplicate categories and merge them with existing ones
					DSUtility::remove_duplicate_terms("www.ggmmoebel.com");
					break;
				case 'remove-duplicates-ggmgastro':
					// Remove duplicate categories and merge them with existing ones
					DSUtility::remove_duplicate_terms("www.ggmgastro.com");
					break;
				case 'sync-cats':
					$cat_ids = $_REQUEST["cats-ids"];
					DSUtility::sync_cats($cat_ids);
					break;
                case 'all_scrap':
                    $this->ds_run_all_scrap();
                    break;
            }
            
        }
    }
	
	public function do_bulk_edit_cats($redirect_url, $action, $post_ids){
		if ($action == 'gbm-sync-cat') {
			$post_ids_str = implode(",",$post_ids);
			$redirect_url = "https://materiel.discount/?ds_scrap=yes&ds_scrap_key=4WaBzTGWQKvQQE74&ds_scrap_type=sync-cats&cats-ids=".$post_ids_str;
			$redirect_url = add_query_arg('gbm-sync-cat', count($post_ids), $redirect_url);
		}
		 return $redirect_url;
	}
}
