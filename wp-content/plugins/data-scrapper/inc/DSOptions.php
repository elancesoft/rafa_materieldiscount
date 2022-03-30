<?php
/**
 * Define class DSOptions if not exists
 */
if (!class_exists('DSOptions')) {

    /**
     * DSOptions class
     * Implements the options user interface inside settings
     * to customise messages corresponding to the email events.
     * 
     * @since 1.0.0
     */
    class DSOptions {

        /**
         * @var DSOptions
         */
        private static $dsOptions;

        /**
         * Construts the DSOptions instance
         */
        private function __construct() {
            add_action('admin_menu', array($this, 'admin_menu'));
        }

        /**
         * Returns DSOptions object if exists
         * else instantiate a new object and return.
         * 
         * @return DSOptions
         * 
         * | Returns DSOptions object if exists
         * else instantiate a new object and return.
         * 
         * @since 1.0.0
         */
        public static function get_instance() {
            if (isset(self::$dsOptions) && is_object(self::$dsOptions)) {
                
            } else {
                self::$dsOptions = new DSOptions();
            }
            return self::$dsOptions;
        }

        /**
         * Adds the options page and menu to the admin menu
         * 
         * @since 1.0.0
         */
        public function admin_menu() {
            if (is_admin()) {
                $page_title = 'Data Scrapper';
                $menu_title = 'Data Scrapper';
                $capability = 'edit_themes';
                $menu_slug = 'ds-options-menu';
                $function = array($this, 'page');
//                $icon_uri = plugin_dir_url('') . '/egrapes-license-manager/images/admin-icon.png';
                add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function);
            }
        }

        /**
         * Controls and decides whether to process the form
         * or display(render) the page.
         * 
         * @since 1.0.0
         */
        public function page() {
            $errors = array();
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $errors = $this->validate();
                if (isset($errors) && is_array($errors) && empty($errors)) {
                    $this->submit();
                    ?>
                    <p class="success">Settings saved.</p>
                    <?php
                }
            }
            $this->render($errors);
        }

        /**
         * Display or render the page
         * @param array $errors Errors list
         * 
         * @since 1.0.0
         */
        public function render($errors = array()) {
            $settings = get_option('ds_scrapper_settings');
            $safes = get_option("ds_safety_switch", "off");
            ?>

            <div class="ds-admin-container wrap">
                <form name="license_manager_options" class="ds-form" method="POST">
				
                    <?php wp_nonce_field('ds_options_form', 'ds_options_form_nonce'); ?>     

                    <?php if (isset($errors) && !empty($errors)): ?>
						<p class="error">Please check the errors in the form.</p>
                    <?php endif; ?>

                    <?php if (isset($errors['nonce']) && in_array('invalid', $errors['nonce'])): ?>
                        <p class="error">Invalid Request</p>
                    <?php endif; ?>

                    <h3>Scrapper Settings</h3>
                    <p>
                        <strong>Script Status:-</strong><br>
						<div id="script-status"></div>             
                    </p>


                    <!-- <h3>Scrapper Settings</h3>
                    <label><input type="checkbox" name="settings[skip_images]" value="yes" <?php echo checked($settings['skip_images'], "yes", true); ?>> Skip Images</label>
                    <p class="submit"><input name="submit" id="submit" class="button button-primary" value="Save Changes" type="submit"></p>
                    <p>Please use following buttons to run scrap script manually. <br>
						(the script will be automatically stopped after php max execution time reached)
						</p>-->
                    <hr>
                    <p>
                        <label for="safety-switch"><strong>Safety Switch : <strong></label> 
                        <!-- Rounded switch -->
                        <label for="safety-switch" class="ds-switch">
							<input id="safety-switch" type="checkbox" value="on" <?php echo checked($safes, "on", true); ?> >
							<span class="ds-slider round"></span>
                        </label><br>
                        <strong>If this switch is ON all the running executions will start stopping and must be stopped within few seconds (may take upto minute in some cases) </strong><br>
						<strong>If this switch is ON then script do not work. Executions link will not work. Crons will not work.</strong>
                        <strong>Put it off when you want to use the script.</strong>
                    </p>

                    <br><br>
                    <button id="start-gastro-scrap" class="not-started">Start GGMGastro Scrap</button>
                    <p class="scrap-gastro-message" style="display:none;">Scrap started. Please check product categories for updates.</p>

                    <button id="start-moebel-scrap" class="not-started">Start GGMMoebel Scrap</button>
                    <p class="scrap-moebel-message" style="display:none;">Scrap started. Please check product categories for updates.</p>                    
                    <hr>
                                    
                    <div style="display:none">
                        <p>Please use following buttons to reset scrapper (In next run it scrapper will start from beginning instead of resuming).</p>
                        <button id="reset-gastro-scrap" class="">Reset GGMGastro Scrap</button>
                        <button id="reset-moebel-scrap" class="">Reset GGMMoebel Scrap</button>
                    </div>
                                    
                    <p>
                        <strong>Following are the links to execute the script:</strong>
                    </p>
                    
					<p>
                        <strong>for www.ggmgastro.com : </strong><br>
                        <?php echo site_url("?ds_scrap=yes&ds_scrap_key=4WaBzTGWQKvQQE74&ds_scrap_type=ggmgastro"); ?>
                    </p>
                    
					<p>
						<strong>for www.ggmmoebel.com : </strong><br>
                        <?php echo site_url("?ds_scrap=yes&ds_scrap_key=4WaBzTGWQKvQQE74&ds_scrap_type=ggmmoebel"); ?>
                    </p>
                    
					<p>
                        <strong>Single link to scrap both one by one : </strong><br>
						<?php echo site_url("?ds_scrap=yes&ds_scrap_key=4WaBzTGWQKvQQE74&ds_scrap_type=all_scrap"); ?>
                    </p>
					
					<p>
                        <strong>for duplicate category removal for www.ggmmoebel.com : </strong><br>
						<?php echo site_url("?ds_scrap=yes&ds_scrap_key=4WaBzTGWQKvQQE74&ds_scrap_type=remove-duplicates-ggmmoebel"); ?>
                    </p>
					
					<p>
                        <strong>for duplicate category removal for www.ggmgastro.com : </strong><br>
						<?php echo site_url("?ds_scrap=yes&ds_scrap_key=4WaBzTGWQKvQQE74&ds_scrap_type=remove-duplicates-ggmgastro"); ?>
                    </p>
                    
					<p>
						Please use above links to create cron jobs to execute the script.
						please create two seperate crons for each scripts.
						Cron should repeat job according to the max_execution_time set on the server.
						If php's max_execution_time is 300 then, cron should repeat job in each 300 seconds (5 min).
						and if max_execution_time is 600 then, cron should repeat job in 600 seconds (10 min).
						if max_execution_time is 0 means infinite then cron can be set to repeat in 2 or 3 days (according to the time script completes its execution). 
                    </p>

                    <p>You can find csv log files of imported or scrapped data in folder /wp-content/uploads/scrapper-reports/ </p>
									
					<p>Current Scrap Options:</p>
						<?php 
							$scrap_options = get_option("ggmmoebel_scrap_info", array());
							echo "<p>ggmmoebel_scrap_info :</p><p><pre>";
							print_r($scrap_options);
							echo "</pre></p>";
							$scrap_options2 = get_option("ggmgastro_scrap_info", array());
							echo "<p>ggmgastro_scrap_info :</p><p><pre>";
							print_r($scrap_options2);
							echo "</pre></p>";
						?>
					<h3>Categories for www.ggmgastro.com :</h3>
					<div style="height:300px;overflow-y:scroll">
					<?php 
					DSUtility::list_terms("www.ggmgastro.com");
					?>
					</div>
					<h3>Categories for www.ggmmoebel.com :</h3>
					<div style="height:300px;overflow-y:scroll">
					<?php 
					DSUtility::list_terms("www.ggmmoebel.com");
					?>
					</div> 
					
					<!--<h3>Deleting Duplicate Categories for www.ggmgastro.com :</h3>
					<div style="height:300px;overflow-y:scroll">
					<?php 
					//DSUtility::remove_duplicate_terms("www.ggmgastro.com");
					?>
					</div>
					
					<h3>Deleting Duplicate Categories for www.ggmmoebel.com :</h3>
					 <div style="height:300px;overflow-y:scroll">
					<?php 
					//DSUtility::remove_duplicate_terms("www.ggmmoebel.com");
					?>
					</div>-->
					
				</form>
			</div>
			<script>
				jQuery(document).ready(function (e) {
					setInterval(function () {
						wp.ajax.post("ds_get_script_status", {})
								.done(function (response) {
									console.log(response);
									if (response.status == "success") {
										jQuery("#script-status").html(response.value);
									} else {
										safes.prop("checked", false);
									}

								});
					}, 5000);

					jQuery("#start-gastro-scrap.not-started").on("click", function (e) {
						e.preventDefault();
						console.log("Script Gastro started");
						jQuery("#start-gastro-scrap").prop("disabled", "disabled");
						jQuery("#start-gastro-scrap").removeClass("not-started");
						jQuery(".scrap-gastro-message").show();
						wp.ajax.post("ds_run_gastro_scrap", {})
								.done(function (response) {
									console.log("Script gastro ended");
								});
					});

					jQuery("#start-moebel-scrap.not-started").on("click", function (e) {
						e.preventDefault();
						console.log("Script Moebel started");
						jQuery("#start-moebel-scrap").prop("disabled", "disabled");
						jQuery("#start-moebel-scrap").removeClass("not-started");
						jQuery(".scrap-moebel-message").show();
						wp.ajax.post("ds_run_moebel_scrap", {})
								.done(function (response) {
									console.log("Script Moebel ended");
								});
					});

					jQuery("#reset-gastro-scrap").on("click", function (e) {
						e.preventDefault();
						console.log("Reset Gastro");
						wp.ajax.post("ds_reset_gastro_scrap", {})
								.always(function (response) {
									window.alert("Gastro Scrapper Reset done.");
								});
					});

					jQuery("#reset-moebel-scrap").on("click", function (e) {
						e.preventDefault();
						console.log("Reset Moebel");
						wp.ajax.post("ds_reset_moebel_scrap", {})
								.always(function (response) {
									window.alert("Moebel Scrapper Reset done.");
								});
					});

					jQuery("#reset-moebel-scrap").on("click", function (e) {
						e.preventDefault();
						console.log("Reset Moebel");
						wp.ajax.post("ds_reset_moebel_scrap", {})
								.always(function (response) {
									window.alert("Moebel Scrapper Reset done.");
								});
					});

					jQuery("#safety-switch").on("change", function (e) {
						e.preventDefault();
						var safes = jQuery(this);
						if (safes.is(':checked')) {
							wp.ajax.post("ds_set_safety_on", {})
									.done(function (response) {
										console.log(response);
										if (response.status == "success") {
											window.alert("Safety Switched ON.");
										} else {
											safes.prop("checked", false);
										}

									});
						} else {
							wp.ajax.post("ds_set_safety_off", {})
									.done(function (response) {
										console.log(response);
										if (response.status == "success") {
											window.alert("Safety Switched OFF.");
										} else {
											safes.prop("checked", true);
										}

									});
						}

					});
				});
			</script>
		<?php                    
		}

			/**
			 * Validates the submitted form.
			 * 
			 * @return array
			 * | Returns the errors if found else empty array
			 * 
			 * @since 1.0.0
			 */
			function validate() {
				$errors = array();

				// Check if our nonce is set.    
				if (!isset($_POST['ds_options_form_nonce'])) {
					$errors['nonce'] = array('invalid');
				}

				// Verify that the nonce is valid.
				if (!wp_verify_nonce($_POST['ds_options_form_nonce'], 'ds_options_form')) {
					$errors['nonce'] = array('invalid');
				}


				return $errors;
			}

			/**
			 * Handles the form submit
			 * Stores the values if form validated.
			 * @return void
			 * @since 1.0.0
			 */
			function submit() {

				$settings = $_POST['settings'];
				update_option('ds_settings', $settings);
			}

	}

	//Instantiates the DSOptions object
	$dsOptions = DSOptions::get_instance();
}