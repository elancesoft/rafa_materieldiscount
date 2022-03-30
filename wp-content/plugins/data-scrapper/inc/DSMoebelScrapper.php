<?php

/**
 * Description of DSMoebelScrapper
 * Scraps the data from the websites
 *
 * @author Neeraj Mourya <neerajmorya@gmail.com>
 */
class DSMoebelScrapper
{

    public static $scrap_id;
    public static $scrap_started;
    public static $total_count = 0;
    public static $upload_dir;
    public static $category_logs_file;
    public static $product_logs_file;
    public static $image_logs_file = "";
    public static $de_cats = [
        "Stühle",
        "Barstühle",
        "Sitzbänke",
        "Tische",
        "Tischplatten",
        "Tischgestelle",
        "Terrassenmöbel",
        "Supermarktregale",
        "Beleuchtung",
        "Absperrung",
        "Kundenstopper",
        "Raumtrenner",
        "Besteckschrank",
        "Kofferwagen",
        "Mineralfaserplatten",
        "Gitterboxen",
        "Mehr",
        "Gastrogeräte"
    ];

    /**
     *
     * @var DSMoebelScrapper
     */
    private static $dsScrapper;

    /**
     * Get the active instance of DSMoebelScrapper
     *
     * @return DSMoebelScrapper
     * @since 1.0.0
     */
    public static function get_instance()
    {
        if (isset(self::$dsScrapper) && is_object(self::$dsScrapper)) {
        } else {
            self::$dsScrapper = new DSMoebelScrapper();
        }
        return self::$dsScrapper;
    }

    /**
     * Constructs the DSMoebelScrapper
     */
    public function __construct()
    {

        //delete_option( "ggmmoebel_scrap_info" );

        //	add_shortcode("scrap_categories", array($this, "scrap_main_categories"));
        //	add_shortcode("scrap_categories",array($this, "scrap_sub_categories"));
    }

    public function start()
    {
        //stop if safety is on
        DSUtility::stop_if_safety();
        //updating script status
        DSUtility::update_script_status();

        $current_time = time();
        //Getting Scrap info
        $scrap_options = get_option("ggmmoebel_scrap_info", array());

        //echo("Scrap Options : ");
        //echo(print_r($scrap_options,true));

        $is_new_scrap = true;
        if (isset($scrap_options) && !empty($scrap_options) && is_array($scrap_options)) {
            if ($scrap_options['scrap_status'] !== 'completed') {
                $is_new_scrap = false;
                self::$scrap_id = $scrap_options['scrap_id'];
                self::$scrap_started = $scrap_options['scrap_started'];
                error_log("Unfinished scrap found. Continueing");
            } else {
                $scrap_options = array();
                error_log("Completed Scrap found.");
            }
        }

        //echo("Scrap Options : ");
        //echo(print_r($scrap_options,true));

        if ($is_new_scrap) {
            self::$scrap_id = "ggmmoebel-" . date("Ymdhis");
            self::$scrap_started = $current_time;
            $scrap_options['scrap_id'] = self::$scrap_id;
            $scrap_options['scrap_started'] = self::$scrap_started;
            $scrap_options['scrap_status'] = 'running';
            update_option("ggmmoebel_scrap_info", $scrap_options);
            error_log("New Scrap Set");
        }

        //Deleting old invalid de categories
        //DSUtility::delete_invalid_categories();

        //Getting upload directory
        $upload_dir_details = wp_get_upload_dir();
        self::$upload_dir = $upload_dir_details['basedir'];

        //Opening categories log file
        $scrap_logs_dir = self::$upload_dir . "/scrapper-reports/" . self::$scrap_id;
        if (!file_exists($scrap_logs_dir)) {
            mkdir($scrap_logs_dir, 0755, true);
        }

        $category_logs_filename = $scrap_logs_dir . "/scrapped-categories" . ".csv";
        self::$category_logs_file = fopen($category_logs_filename, "a");

        //Opening products log file        
        $product_logs_filename = $scrap_logs_dir . "/scrapped-products" . ".csv";
        self::$product_logs_file = fopen($product_logs_filename, "a");

        //Opening images log file
        $image_logs_filename = $scrap_logs_dir . "/scrapped-images" . ".csv";
        self::$image_logs_file = fopen($image_logs_filename, "a");

        if ($is_new_scrap) {
            //writing report headers
            $this->write_category_log_line(["Time", "Category Name", "Term ID", "Parent ID", "Term URL", "Source ID", "Source URL", "Scrap From URL"]);
            $this->write_product_log_line(["Time", "Product Name", "Product ID", "Category ID", "Product URL", "Source ID", "Source Category ID", "Source URL", "Scrap From URL"]);
            $this->write_image_log_line(["Time", "Attachment ID", "Image URL", "Source URL", "Type", "Product / Category ID"]);
        }


        $this->scrap_main_categories();
    }
    public function finalise()
    {
        //deleting products updated more than 7 days ago
        //DSUtility::delete_old_products();

        //deleting categories updated more than 7 days ago
        //DSUtility::delete_old_categories();

        //deleting empty categories
        //$this->delete_empty_categories();

        //updating categories menu
        DSUtility::update_categories_menu();


        //closing files
        $this->write_category_log_line(["COMPLETED !"]);
        $this->write_product_log_line(["COMPLETED !"]);
        $this->write_image_log_line(["COMPLETED !"]);
        fclose(self::$category_logs_file);
        fclose(self::$product_logs_file);
        fclose(self::$image_logs_file);

        //setting scrap status to complete
        $scrap_options = get_option("ggmmoebel_scrap_info", array());
        $scrap_options['scrap_status'] = "completed";
        update_option("ggmmoebel_scrap_info", $scrap_options);


        // sending finished email
        DSUtility::send_finished_email("ggmmoebel");
    }

    public function scrap_main_categories()
    {

        //echo("SCRAP CATEGORIES RAN");

        $parent_id = 0;

        $ch = curl_init();

        $page_url = "https://www.ggmmoebel.com/fr-fr-eur/";


        curl_setopt($ch, CURLOPT_URL, $page_url);
        //	setting proxy
        //  SelRevScrapper::get_instance()->set_proxy($ch);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:69.0) Gecko/20100101 Firefox/69.0';
        $headers[] = 'Accept-Language: en';
        $headers[] = 'Connection: keep-alive';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLINFO_HEADER_OUT, $headers);

        $curlinfo = curl_getinfo($ch);
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log("ERROR : ");
            error_log(curl_error($ch));
            //echo 'Error:' . curl_error($ch);
            //            $this->scrap_status = curl_error($ch);
        }
        curl_close($ch);

        $html_categories = $this->scrap_main_html_categories($result);

        $first_string = "\"category\":{\"list\":";
        $second_string = ",\"current\":";
        $json = DSUtility::getStringBetween($result, $first_string, $second_string);
        //echo("JSON : ");
        //////echo($json);



        if (!isset($json) || empty($json)) {
            return false;
        }

        $jsondata = json_decode($json);


        //echo("JSON DATA NOW");
        //echo(print_r($jsondata,true));
        //        return false;

        if (isset($jsondata) && !empty($jsondata) && is_array($jsondata)) {


            foreach ($jsondata as $category) {

                //stop if safety is on
                DSUtility::stop_if_safety();
                //updating script status
                DSUtility::update_script_status();

                //check if correct category
                if (!empty($category->id)) {
                    if ($category->id == 4939 || $category->id == "4939") {
                        continue;
                    }
                } else {
                    continue;
                }

                $preview_image = "";

                if (!empty($category->preview_image)) {

                    $preview_image = "https://d1jedffssjmp3k.cloudfront.net/img/400/400/resize/media/catalog/category/" . $category->preview_image;
                }


                $category_name = trim($category->name);



                if (!in_array($category_name, $html_categories)) {
                    //  echo ("Not Found in html categories : " . $category_name);
                    continue;
                }
                if (in_array($category_name, self::$de_cats)) {
                    //echo "Found in DE";
                    continue;
                }

                //	error_log("Found Category : " . $category_name);
                //  error_log("Source URL : " . $category->url_path);
                //  $description = $category->description;
                $scrapped_from = $page_url;
                $source_url = "/fr-fr-eur/{$category->url_path}";
                $slug = str_replace(".html", "", $category->url_path);
                //                error_log("$category_name, $scrapped_from, $source_url");

                $term_id = 0;




                $term = DSUtility::term_exists($source_url, $category->id, $category_name, $slug, $parent_id);

                //if (!term_exists($category_name, "product_cat", $parent_id)) {
                if ($term === false) {
                    $term = wp_insert_term($category_name, "product_cat", [
                        //                        "description" => $description,
                        "parent" => $parent_id,
                        "slug" => $slug
                    ]);

                    if (!is_wp_error($term)) {
                        $term_id = $term['term_id'];
                    }
                } else {
                    $term = get_term_by("name", $category_name, "product_cat");
                    wp_update_term($term->term_id, 'product_cat', [
                        "name" => $category_name,
                        "slug" => $slug
                    ]);
                    $term_id = $term->term_id;
                }

                //                error_log("TERM ID : " . $term_id);
                //                error_log(print_r($term,true));
                //skip if already scrapped within same scrap id
                $scrap_updated = get_term_meta($term_id, "scrap_updated", true);
                if ($this->is_updated_in_current_scrap($scrap_updated)) {
                    //                    error_log("Returning as updated in current scrap");
                    continue;
                }

                if ($term_id > 0) {
                    update_term_meta($term_id, "source_website", "www.ggmmoebel.com");
                    update_term_meta($term_id, "scrapped_from", $scrapped_from);
                    update_term_meta($term_id, "source_url", $source_url);
                    update_term_meta($term_id, "source_id", $category->id);
                    update_term_meta($term_id, "source_parent_id", $category->parent_id);
                    update_term_meta($term_id, "source_position", $category->position);
                    if (!empty($preview_image)) {
                        update_term_meta($term_id, "source_image_url", $preview_image);
                    }

                    update_term_meta($term_id, "is_main_category", "yes");

                    //updating category image
                    $this->scrap_category_image($term_id, $preview_image);

                    self::$total_count++;

                    $this->write_category_log_line([
                        date("Y-m-d h:i:s"),
                        $category_name,
                        $term_id,
                        $parent_id,
                        get_term_link($term_id),
                        $category->id,
                        $source_url,
                        $scrapped_from
                    ]);
                    //                    error_log(self::$total_count . " categories imported");
                    update_term_meta($term_id, "products_updated", 0);
                    update_term_meta($term_id, "scrap_updated", time());
                }
            }
        }

        $this->scrap_categories(0);

        $this->finalise();
    }

    public function scrap_main_html_categories($result)
    {
        $html_categories = array();

        $html = str_get_html($result);

        if ($html) {
            foreach ($html->find(".main-menu .main-menu__item") as $row) {
                $category_name = trim($row->find("a span", 0)->plaintext);
                $category_name = str_replace("&amp;", "&", $category_name);
                $source_url = $row->find("a", 0)->href;
                $html_categories[] = $category_name;
            }
        }
        return $html_categories;
    }
    public function scrap_single_category($cat_id)
    {
        //echo "<p>> PARENT ID Gastro : ".$parent_id."</p>";
        if (empty($cat_id)) {
            return;
        }

        wp_update_term_count_now([$cat_id], "product_cat");

        $term = get_term_by('id', $cat_id, 'product_cat');

        //var_dump($terms);

        //echo("Found Terms");

        $this->scrap_sub_categories($term->term_id);
        //scrapping products
        $this->scrap_products($term->term_id);


        wp_update_term_count_now([$cat_id], "product_cat");
        update_term_meta($cat_id, "products_updated", time());
    }
    public function scrap_categories($parent_id)
    {
        //echo("Scrap categories ran");
        //echo "<p>> PARENT ID Moebel : ".$parent_id."</p>";


        wp_update_term_count_now([$parent_id], "product_cat");
        $terms = get_terms([
            "taxonomy" => "product_cat",
            "parent" => $parent_id,
            "hide_empty" => false,
            'meta_query' => array(
                array(
                    'key' => 'source_website',
                    'value' => 'www.ggmmoebel.com',
                    'compare' => '='
                ),
                array(
                    'key' => 'products_updated',
                    'value' => self::$scrap_started,
                    'compare' => '<',
                    'type' => 'NUMERIC'
                )
            )
        ]);

        //echo("Found Terms");
        //echo(print_r($terms, true));

        foreach ($terms as $term) {
            //scrapping sub categories
            $this->scrap_sub_categories($term->term_id);
            //scrapping products
            $this->scrap_products($term->term_id);
        }
        wp_update_term_count_now([$parent_id], "product_cat");
        update_term_meta($parent_id, "products_updated", time());
    }

    public function scrap_sub_categories($parent_id)
    {
        //echo("PARENT ID : " . $parent_id);
        if (empty($parent_id)) {
            //return;
        }
        $parent_term = get_term($parent_id, "product_cat");
        $source_url = get_term_meta($parent_id, "source_url", true);
        $parent_source_id = get_term_meta($parent_id, "source_id", true);

        //echo("Scrapping children for " . $parent_term->name);
        //echo("SOURCE URL : " . $source_url);
        //
        //echo("SCRAP SUB CATEGORIES RAN");

        $ch = curl_init();

        $page_url = "https://www.ggmmoebel.com{$source_url}";
        curl_setopt($ch, CURLOPT_URL, $page_url);
        //setting proxy
        //        SelRevScrapper::get_instance()->set_proxy($ch);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:69.0) Gecko/20100101 Firefox/69.0';
        $headers[] = 'Accept-Language: en';
        $headers[] = 'Connection: keep-alive';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLINFO_HEADER_OUT, $headers);

        $curlinfo = curl_getinfo($ch);
        $result = curl_exec($ch);
        //echo("RESULT : ");
        ////echo($result);
        if (curl_errno($ch)) {
            error_log("ERROR : ");
            error_log(curl_error($ch));
            return false;
            //echo 'Error:' . curl_error($ch);
            //            $this->scrap_status = curl_error($ch);
        }
        curl_close($ch);

        $first_string = "\"categoriesMap\":";
        $second_string = ",\"notFoundCategoryIds\":";
        $json = DSUtility::getStringBetween($result, $first_string, $second_string);
        //echo("JSON : ");
        ////echo($json);

        if (!isset($json) || empty($json)) {
            return false;
        }

        $jsondata = json_decode($json);
        //echo("JSON DATA NOW");
        //echo(print_r($jsondata,true));
        //        return false;
        if (isset($jsondata) && !empty($jsondata) && is_object($jsondata)) {
            foreach ($jsondata as $key => $category) {
                //stop if safety is on
                DSUtility::stop_if_safety();
                //updating script status
                DSUtility::update_script_status();


                //check if correct category
                if ($category->id == 4939 || $category->id == "4939") {
                    continue;
                }


                if ($category->name == $parent_term->name) {
                    continue;
                }
                //                error_log("{$category->parent_id} = {$parent_source_id}");
                if (strval($category->parent_id) != strval($parent_source_id)) {
                    //                    error_log("Not Matched skipping");
                    continue;
                }

                $preview_image = "https://d1jedffssjmp3k.cloudfront.net/img/400/400/resize/media/catalog/category/" . $category->preview_image;
                //                error_log("Preview Image : " . $preview_image);
                //                return false;
                $category_name = $category->name;

                $description = isset($category->description) ? $category->description : "";
                $scrapped_from = $page_url;
                $source_url = "/fr-fr-eur/{$category->url_path}";
                $slug_parts = explode("/", $category->url_path);
                $slug = "";
                if (is_array($slug_parts) && !empty($slug_parts)) {
                    $slug = $slug_parts[sizeof($slug_parts) - 1];
                    $slug = str_replace(".html", "", $slug);
                }
                //                error_log("$category_name, $scrapped_from, $source_url");

                $term_id = 0;
                $term = DSUtility::term_exists($source_url, $category->id, $category_name, $slug, $parent_id);
                //if (!term_exists($category_name, "product_cat", $parent_id)) {
                if ($term === false) {
                    $term = wp_insert_term($category_name, "product_cat", [
                        //                        "description" => $description,
                        "parent" => $parent_id,
                        "slug" => $slug
                    ]);

                    if (!is_wp_error($term)) {
                        $term_id = $term['term_id'];
                    }
                } else {
                    $term = get_term_by("name", $category_name, "product_cat");
                    wp_update_term($term->term_id, 'product_cat', [
                        "name" => $category_name,
                        "slug" => $slug
                    ]);
                    $term_id = $term->term_id;
                }

                //skip if already scrapped within same scrap id
                $scrap_updated = get_term_meta($term_id, "scrap_updated", true);
                if ($this->is_updated_in_current_scrap($scrap_updated)) {
                    continue;
                }
                //                error_log("TERM ID : " . $term_id);
                //                error_log(print_r($term,true));

                if ($term_id > 0) {
                    update_term_meta($term_id, "source_website", "www.ggmmoebel.com");
                    update_term_meta($term_id, "scrapped_from", $scrapped_from);
                    update_term_meta($term_id, "source_url", $source_url);
                    update_term_meta($term_id, "source_id", $category->id);
                    update_term_meta($term_id, "source_parent_id", $category->parent_id);
                    update_term_meta($term_id, "source_position", $category->position);
                    update_term_meta($term_id, "source_image_url", $preview_image);
                    update_term_meta($term_id, "is_main_category", "no");
                    //updating category image
                    $this->scrap_category_image($term_id, $preview_image);

                    self::$total_count++;

                    $this->write_category_log_line([
                        date("Y-m-d h:i:s"),
                        $category_name,
                        $term_id,
                        $parent_id,
                        get_term_link($term_id),
                        $category->id,
                        $source_url,
                        $scrapped_from
                    ]);
                    //                    error_log(self::$total_count . " categories imported");
                    update_term_meta($term_id, "products_updated", 0);
                    update_term_meta($term_id, "scrap_updated", time());
                }
            }
            $this->scrap_categories($parent_id);
        }
    }

    /**
     * Scraps products of categories
     * @param type $category_id
     */
    public function scrap_products($category_id)
    {

        $category_term = get_term($category_id, "product_cat");
        $category_source_id = get_term_meta($category_id, "source_id", true);
        $category_source_url = get_term_meta($category_id, "source_url", true);

        //echo("Source ID : " . $category_source_id);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.ggmmoebel.com/api/catalog/vue_storefront_magento_moebel_french/product/_search?_source_exclude=description%2Cconfigurable_options%2Csgn%2C%2A.sgn%2Cmsrp_display_actual_price_type%2C%2A.msrp_display_actual_price_type%2Crequired_options&_source_include=activity%2Ctype_id%2C%2Asku%2Cproduct_links%2Ctax_class_id%2Cspecial_price%2Cspecial_to_date%2Cspecial_from_date%2Cname%2Cprice%2Cprice_incl_tax%2Coriginal_price_incl_tax%2Coriginal_price%2Cspecial_price_incl_tax%2Cid%2Cimage%2Csale%2Cnew%2Curl_path%2Curl_key%2Cstatus%2Ctier_prices%2Cconfigurable_children.sku%2Cconfigurable_children.price%2Cconfigurable_children.special_price%2Cconfigurable_children.price_incl_tax%2Cconfigurable_children.special_price_incl_tax%2Cconfigurable_children.original_price%2Cconfigurable_children.original_price_incl_tax%2C%2Aimage%2C%2Asmall_image%2Cconfigurable_children.color%2Cconfigurable_children.size%2Cconfigurable_children.tier_prices%2Cfinal_price%2Cconfigurable_children.final_price%2Cshort_description%2Cinfo%2Cstock%2Cerrors%2Cmanufacturer_dimensions%2Camasty_labels%2Csales_blacklist%2Corder_in_shop%2Cshipping_method%2Caverage_review%2Csales_price_%2A%2Ccapacity%2Cgross_width%2Cgross_depth%2Crecommended_filling_quantity%2Cnumber_of_doors%2Cnumber_of_pizzas_per_chamber%2Cnumber_of_burners%2Cnumber_of_drawers%2Cshow_on_storefront%2Cnew_article%2Cshow_on_storefront_customers_recommend%2Csupply_date%2Cno_leasing%2Cdelivery_time_zero_stock%2Cdelivery_time%2Crequest_item%2Cdisplay_mode_akeneo%2Ccms_content_page&from=0&size=1000&sort=price%3Aasc');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"query":{"bool":{"filter":{"bool":{"must":[{"terms":{"visibility":[2,3,4]}},{"terms":{"status":[0,1]}},{"terms":{"category_ids":[' . $category_source_id . ']}}]}}}}}');

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'Authority: api.ggmmoebel.com';
        $headers[] = 'Sec-Ch-Ua: \"Chromium\";v=\"92\", \" Not A;Brand\";v=\"99\", \"Google Chrome\";v=\"92\"';
        $headers[] = 'Accept: application/json';
        $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
        $headers[] = 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36';
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Origin: https://www.ggmmoebel.com';
        $headers[] = 'Sec-Fetch-Site: same-site';
        $headers[] = 'Sec-Fetch-Mode: cors';
        $headers[] = 'Sec-Fetch-Dest: empty';
        $headers[] = 'Referer: https://www.ggmmoebel.com/';
        $headers[] = 'Accept-Language: en-US,en;q=0.9,hi;q=0.8';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        $curl_info = curl_getinfo($ch);
        //echo("INFO : " . print_r($curl_info, true));
        if (curl_errno($ch)) {
            //echo 'Error:' . curl_error($ch);
        }


        //echo("Results");
        ////echo($result);

        if (!isset($result) || empty($result)) {
            return false;
        }

        $jsondata = json_decode($result);
        //echo("PRODUCT JSON DATA NOW");
        //echo(print_r($jsondata, true));
        $product_counts = 0;
        $product_exists = 0;
        $product_inserted = 0;
        if (isset($jsondata) && !empty($jsondata) && is_object($jsondata)) {
            foreach ($jsondata->hits->hits as $productdata) {
                $product_counts++;
                //stop if safety is on
                DSUtility::stop_if_safety();
                //updating script status
                DSUtility::update_script_status();

                $sku_prefix = $this->generate_random_string(3); // Added by TuanPV[elancefoxvn]

                $product_id = 0;
                $temp_products = get_posts([
                    'post_type' => 'product',
                    'meta_key' => '_sku',
                    'meta_value' => $productdata->_source->sku,
                    'meta_compare' => 'LIKE' // added by TuanPV [elancefoxvn] for sku_prefix task
                ]);

                if (!empty($productdata->_source->url_path)) {


                    $slug = $productdata->_source->url_path ? $productdata->_source->url_path : "";

                    if (!empty($slug)) {
                        $slug = str_replace(".html", "", $slug);
                        $slug = sanitize_title($slug);
                    }


                    if (sizeof($temp_products) > 0) {

                        $product_id = !empty($temp_products[0]->ID) ? $temp_products[0]->ID : "";

                        if (!empty($product_id)) {
                            wp_update_post([
                                "post_title" => sanitize_text_field($productdata->_source->name),
                                "post_name" => $slug,
                            ]);

                            $product_exists++;
                            //                    error_log("Product SKU already exists thus continue");
                            //                    continue;
                        }
                    } else {
                        //Creating product
                        $product_id = wc_get_product_id_by_sku($productdata->_source->name);
                        if ($product_id > 0) {
                            wp_update_post([
                                "post_title" => sanitize_text_field($productdata->_source->name),
                                "post_name" => $slug,
                            ]);
                            $product_exists++;
                        } else {
                            $post_args = array(
                                'post_author' => intval(1), // The user's ID
                                'post_title' => sanitize_text_field($productdata->_source->name), // The product's Title
                                'post_name' => $slug,
                                'post_type' => 'product',
                                'post_status' => 'publish', // This could also be $data['status'];
                                'meta_input' => array(
                                    // '_sku' => $productdata->_source->sku,
                                    '_sku' => $sku_prefix . '_' . $productdata->_source->sku,
                                    'source_url' => $productdata->_source->url_path,
                                    'source_id' => $productdata->_source->id,
                                    'source_website' => 'www.ggmmoebel.com',
                                ),
                            );

                            $product_id = wp_insert_post($post_args);
                            $product_inserted++;
                        }
                    }
                }
                // do not move forward if already scrapped
                $scrap_updated = get_post_meta($product_id, "scrap_updated", true);
                if ($this->is_updated_in_current_scrap($scrap_updated)) {
                    continue;
                }

                //setting product image if not set
                //                if (get_post_thumbnail_id($product_id) == 0) {

                //setting post thumbnails
                $preview_image = "https://d1jedffssjmp3k.cloudfront.net/img/400/400/resize/media/catalog/product" . $productdata->_source->image;
                $attach_id = DSUtility::save_file($preview_image, self::$scrap_started);
                set_post_thumbnail($product_id, $attach_id);
                //write image logs
                $attach_url = wp_get_attachment_image_url($attach_id, "full");
                self::write_image_log_line([date("Y-m-d h:i:s"), $attach_id, $attach_url, $preview_image, "Product", $product_id]);
                //                }

                //setting gallery images if not set
                //                $image_gallery = get_post_meta($product_id, "_product_image_gallery", true);
                //                if (!isset($image_gallery) || empty($image_gallery) || $image_gallery == false || $image_gallery == 0) {
                if (isset($productdata->_source->media_gallery) && is_array($productdata->_source->media_gallery)) {
                    $images = array();
                    $images_ids = array();
                    foreach ($productdata->_source->media_gallery as $media) {
                        if (!in_array($media->image, $images)) {
                            $med_image = $media->image;
                            $med_image = str_replace("\\u002F", "/", $med_image);
                            $images[] = $med_image;
                            $preview_image = "https://d1jedffssjmp3k.cloudfront.net/img/400/400/resize/media/catalog/product" . $media->image;
                            $attach_id = DSUtility::save_file($preview_image, self::$scrap_started);
                            $images_ids[] = $attach_id;
                            //write image log
                            $attach_url = wp_get_attachment_image_url($attach_id, "full");
                            self::write_image_log_line([date("Y-m-d h:i:s"), $attach_id, $attach_url, $preview_image, "Product", $product_id]);
                        }
                    }
                    if (!empty($images_ids)) {
                        $attach_ids = implode(",", $images_ids);
                        update_post_meta($product_id, "_product_image_gallery", $attach_ids);
                    }
                }
                //                }


                // If the post was created okay, let's try update the WooCommerce values.
                if (!empty($product_id)  && function_exists('wc_get_product') && !empty($productdata->_source->url_path)) {

                    //                    error_log(print_r($productdata->_source, true));
                    $product = wc_get_product($product_id);
                    //                    $product->set_sku($productdata->_source->sku); // Generate a SKU with a prefix. (i.e. 'pre-123') 
                    $product->set_regular_price($productdata->_source->sales_price_fr + 10); // Be sure to use the correct decimal price.
                    $product->set_sale_price($productdata->_source->sales_price_fr);

                    $category_ids = $product->get_category_ids();
                    //                    error_log("CATEGORY IDS : ");
                    //                    error_log(print_r($category_ids,true));
                    $category_ids = array_merge($category_ids, DSUtility::get_term_parents_ids($category_id));
                    $category_ids = array_unique($category_ids, SORT_REGULAR);
                    //                    error_log("CATEGORIES NOW : ");
                    //                    error_log(print_r($category_ids,true));
                    $product->set_category_ids($category_ids); // Set multiple category ID's.                    
                    $product->save(); // Save/update the WooCommerce order object.

                    $this->write_product_log_line([date("Y-m-d h:i:s"), $productdata->_source->name, $product_id, $category_id, get_permalink($product_id), $productdata->_source->id, $category_source_id, $productdata->_source->url_path, $category_source_url]);

                    /*
					$to = 'info.rdcorp@gmail.com'; 
					
					$subject = 'Moebel Updated';
					
					$body = 'Moebel Updated - ' . $product_id . " - " . implode(",",$category_ids);
					 
					wp_mail( $to, $subject, $body );
					*/
                    $this->scrap_additional_product_content($product_id, $productdata);
                }

                //update scrap update time
                update_post_meta($product_id, 'scrap_updated', time());
            }
        }

        curl_close($ch);
        $parents_cat = implode(">", DSUtility::get_term_parents_name($category_id));
        //echo("CATEGORY: " . $parents_cat);
        //echo("PRODUCT COUNTS : {$product_counts} | PRODUCT EXISTS : {$product_exists} | PRODUCT INSERTED : {$product_inserted}");
        return $product_counts;
    }

    public function scrap_additional_product_content($product_id, $productdata)
    {
        $source_url = $productdata->_source->url_path;

        if (empty($source_url)) {
            return;
        }
        //echo("SCRAP PRODUCCT");
        $page_url = "https://www.ggmmoebel.com/fr-fr-eur/{$source_url}";
        //echo("Page URL : " . $page_url);
        //echo("product permalink : " . get_permalink($product_id));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $page_url);
        //setting proxy
        //        SelRevScrapper::get_instance()->set_proxy($ch);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:69.0) Gecko/20100101 Firefox/69.0';
        $headers[] = 'Accept-Language: en';
        $headers[] = 'Connection: keep-alive';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLINFO_HEADER_OUT, $headers);

        $curlinfo = curl_getinfo($ch);
        $result = curl_exec($ch);
        //echo("RESULT : ");
        ////echo($result);
        if (curl_errno($ch)) {
            error_log("ERROR : ");
            error_log(curl_error($ch));
            return false;
            //echo 'Error:' . curl_error($ch);
            //            $this->scrap_status = curl_error($ch);
        }
        curl_close($ch);

        $first_string = ",\"product\":";
        $second_string = ",\"ggm-reset-password\"";
        $json = DSUtility::getStringBetween($result, $first_string, $second_string);

        if (!isset($json) || empty($json)) {
            return false;
        }
        $jsondata = json_decode($json);
        //echo("ADD JSON DATA");
        //echo(print_r($jsondata,true));

        $first_string = "<body>";
        $second_string = "<script>window.";
        $result = DSUtility::getStringBetween($result, $first_string, $second_string);


        // Added code by TuanPV [elancefoxvn]
        $url_api_ggmmoebel = "http://51.210.3.40:3000/api/ggmmoebel/{$source_url}";
        $ch_api = curl_init();
        curl_setopt($ch_api, CURLOPT_URL, $url_api_ggmmoebel);
        curl_setopt($ch_api, CURLOPT_RETURNTRANSFER, 1);

        $result_api = curl_exec($ch_api);
        $resultAPIObj = json_decode($result_api);
        $result_api_html = $resultAPIObj->html;

        $first_string = "<body>";
        $second_string = "<footer";
        $result_api_html = DSUtility::getStringBetween($result_api_html, $first_string, $second_string);
        $result_api_html .= "</div></div></div></div>";

        $html_api = str_get_html($result_api_html);
        // END Added code by TuanPV [elancefoxvn]

        $html = str_get_html($result);

        // Commented by TuanPV: update regular price => old code
        /*$regular_price = 0;
        if (!empty($html->find(".price-wrapper .price-original", 0))) {
            $regular_price = $html->find(".price-wrapper .price-original", 0)->plaintext;
            $regular_price = trim($regular_price);
            $regular_price = str_replace("*", "", $regular_price);
            $regular_price = str_replace("EUR", "", $regular_price);
            $regular_price = str_replace(",", ".", $regular_price);
        }*/

        $regular_price = 0;
        if (!empty($html_api->find(".price-wrapper .price-original", 0))) {
            $regular_price = $html_api->find(".price-wrapper .price-original", 0)->plaintext;
            $regular_price = trim($regular_price);
            $regular_price = str_replace("*", "", $regular_price);
            $regular_price = str_replace("EUR", "", $regular_price);
            $regular_price = str_replace(",", ".", $regular_price);
        }

        echo '$regular_price' . $regular_price;

        // Commented by TuanPV: update delivery time => old code
        /*$delivery_time = "";
        if (!empty($html->find(".delivery-time-stock", 0)->plaintext)) {
            $delivery_time = $html->find(".delivery-time-stock", 0)->plaintext;
        }
        if (isset($delivery_time) && !empty($delivery_time)) {
            $delivery_time = trim($delivery_time);
        }
        update_post_meta($product_id, "delivery_time", $delivery_time);*/

        // Added code by TuanPV
        $delivery_time = "";
        if (!empty($html_api->find(".delivery-time .stock", 0)->plaintext)) {
            $delivery_time = $html_api->find(".delivery-time .stock", 0)->plaintext;
        }
        if (isset($delivery_time) && !empty($delivery_time)) {
            $delivery_time = trim($delivery_time);
        }
        update_post_meta($product_id, "delivery_time", $delivery_time);
        // END Added code by TuanPV

        //scrapping variable product data
        // $has_variables = $this->scrap_variable_product_data($product_id, $html, $regular_price); // Commented by TuanPV => old code
        $has_variables = $this->scrap_variable_product_data($product_id, $html_api, $regular_price);

        $description = "";
        try {
            if (!empty($jsondata->current->description[0])) {
                $description = $jsondata->current->description[0]->items[0]->value;
            }
        } catch (\Throwable $e) {
        } catch (\Exception $e) {
        }

        $technical_data = "";
        try {
            $technical_data .= "<div class='ggmtech'>";
            foreach ($jsondata->current->technical_data as $techdata) {
                $technical_data .= "<h3>{$techdata->label}</h3>";
                $technical_data .= "<table>";
                foreach ($techdata->attributes as $attribute) {
                    $technical_data .= "<tr><th>{$attribute->label}</th><td>{$attribute->value}</td></tr>";
                }
                $technical_data .= "</table>";
            }
            $techdata .= "</div>";
        } catch (\Throwable $e) {
        } catch (\Exception $e) {
        }
        $description .= $technical_data;

        //        $short_description = $html->find(".info-wrapper .middle-content-left .cl-middle-text", 1)->innertext;
        $short_description = $jsondata->current->short_description;
        $short_description .= "<div class='delivery_time'>{$delivery_time}</div>";


        // echo '$regular_price-after=' . $regular_price;//tuanpv

        $product = wc_get_product($product_id);
        $product->set_regular_price($regular_price);
        //        if($has_variables){
        //            $product->set_sale_price("");
        //        }
        $product->set_short_description($short_description);
        $product->set_description($description);
        $product->save();
        wp_update_post(["ID" => $product_id]);

        // die('done'.$product_id);//tuanpv
    }

    public function scrap_variable_product_data($product_id, $html, $regular_price)
    {
        $variables = array();
        $qty_arr = array();
        foreach ($html->find(".tier-prices-wrapper .-body .tier-price") as $element) {
            $qty = 0;
            $incl_tax = 0;
            $excl_tax = 0;

            $qty = $element->find("div", 0)->plaintext;
            $qty = trim($qty);
            $qty = str_replace("from ", "", $qty);
            $qty = trim($qty);
            if (in_array($qty, $qty_arr)) {
                continue;
            } else {
                $qty_arr[] = $qty;
            }

            $incl_tax = $element->find("div", 2)->plaintext;
            $incl_tax = trim($incl_tax);
            $incl_tax = str_replace("EUR", "", $incl_tax);
            $incl_tax = str_replace(",", ".", $incl_tax);

            $excl_tax = $element->find("div", 1)->plaintext;
            $excl_tax = trim($excl_tax);
            $excl_tax = str_replace("EUR", "", $excl_tax);
            $excl_tax = str_replace(",", ".", $excl_tax);

            $regular_price = is_numeric($regular_price) ? floatval($regular_price) : 0;
            $excl_tax = is_numeric($excl_tax) ? floatval($excl_tax) : 0;
            $discount = $regular_price - $excl_tax;
            $discount = round($discount, 2);

            $variables[] = array(
                "pbq_quantity" => $qty,
                "pbq_discount" => $discount
            );

            //            error_log("From {$qty} - {$regular_price} - {$excl_tax} - {$discount}");
        }

        if (!empty($variables)) {
            update_post_meta($product_id, "pbq_discount_table_data", $variables);
            update_post_meta($product_id, "pbq_pricing_type_enable", "enable");
            update_post_meta($product_id, "pbq_pricing_type", "fixed");
            update_post_meta($product_id, "pbq_table_layout", "hover_table");

            return true;
        }
        return false;
    }

    /**
     * scraps category image
     * @param type $term_id
     * @param type $image_url
     */
    public function scrap_category_image($term_id, $image_url)
    {
        //updating image
        //        $image_updated = get_term_meta($term_id, "thumbnail_id", true);
        //        if (!isset($image_updated) || empty($image_updated) || $image_updated == false || $image_updated == 0) {
        //            $attach_id = get_term_meta($term_id, "thumbnail_id", true);
        //            if (!isset($attach_id) || empty($attach_id) || $attach_id == false) {
        $attach_id = DSUtility::save_file($image_url, self::$scrap_started);
        update_term_meta($term_id, "thumbnail_id", $attach_id);
        //            }
        $attach_url = wp_get_attachment_image_url($attach_id, "full");
        self::write_image_log_line([date("Y-m-d h:i:s"), $attach_id, $attach_url, $image_url, "Category", $term_id]);
        //        }
    }

    public function delete_empty_categories()
    {
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            //            'parent' => 0,
            'meta_query' => array(
                array(
                    'key' => 'source_website',
                    'value' => 'www.ggmmoebel.com',
                    'compare' => '='
                )
            )
        ]);


        foreach ($terms as $term) {
            if ($term->count == 0) {
                wp_delete_term($term->term_id, 'product_cat');
            }
        }
    }

    /**
     * Checks if updated within scrap id or not
     * @param type $update_time
     * @return boolean
     */
    public function is_updated_in_current_scrap($update_time = 0)
    {
        if (self::$scrap_started < $update_time) {
            return true;
        }
        return false;
    }

    /**
     * Writes log lines to an open file
     * @param string $logs
     */
    public static function write_category_log_line($logs)
    {
        //writing logs to the file
        array_walk($logs, function (&$x) {
            $x = '"' . $x . '"';
        });
        $logs = implode(";", $logs) . PHP_EOL;

        fwrite(self::$category_logs_file, $logs);
    }

    /**
     * Writes log lines to an open file
     * @param string $logs
     */
    public static function write_product_log_line($logs)
    {
        //writing logs to the file
        array_walk($logs, function (&$x) {
            $x = '"' . $x . '"';
        });
        $logs = implode(";", $logs) . PHP_EOL;

        fwrite(self::$product_logs_file, $logs);
    }

    /**
     * Writes log lines to an open file
     * @param string $logs
     */
    public static function write_image_log_line($logs)
    {
        //writing logs to the file
        array_walk($logs, function (&$x) {
            $x = '"' . $x . '"';
        });
        $logs = implode(";", $logs) . PHP_EOL;

        fwrite(self::$image_logs_file, $logs);
    }

    /**
     * Generate random string for SKU
     * @author TuanPV [https://freelancer.com/u/elancefoxvn]
     * @param number $length
     * @return string $random_string
     */
    public function generate_random_string($length = 3)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);

        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return strtoupper(substr(md5(time()), 0, $length));
    }
}
