<?php

class Appful_API {

	function __construct() {
		$this->query = new Appful_API_Query();
		$this->introspector = new Appful_API_Introspector();
		$this->response = new Appful_API_Response();
		add_action('template_redirect', array(&$this, 'template_redirect'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('update_option_appful_api_base', array(&$this, 'flush_rewrite_rules'));
		add_action('pre_update_option_appful_api_controllers', array(&$this, 'update_controllers'));
		register_deactivation_hook( __FILE__, 'plugin_deactivate');
		add_action('post_submitbox_misc_actions', array(&$this, 'submitbox_actions'));
		add_action('save_post', array(&$this, 'save_postdata'));
		add_filter('post_row_actions', array(&$this, 'post_row_actions'), 10, 2);
		add_action('wp_head', array(&$this, "add_header"));

		add_action('delete_term', array(&$this, 'fill_cache'));
		add_action('edited_term', array(&$this, 'updateTaxonomy'));
		add_action('create_term', array(&$this, 'updateTaxonomy'));

		if (!get_option("appful_ip", false)) {
			$this->updateAllowedIPs();
		}

		if (time() - get_option("appful_register_last_refresh", 0) > get_option("appful_cache_register_interval", 60 * 60)) {
			$this->register();
		}

		if (time() - get_option("appful_cache_last_refresh", 0) > get_option("appful_cache_fill_interval", 24 * 60 * 60)) {
			$this->fill_cache();
		}

		if (isset($_REQUEST["appful_slider"]) && isset($_REQUEST["post_id"]) && isset($_REQUEST["nonce"])) {
			if (wp_verify_nonce($_REQUEST["nonce"], "appful-slider")) {
				$this->updateAppSlider($_REQUEST["post_id"], $_REQUEST["appful_slider"] == "true");
				$this->request("cache", array("post_id" => $_REQUEST["post_id"], "push" => 0));
			}
		}
	}


	function updateTaxonomy($id) {
		if ($_REQUEST["taxonomy"] == "category") $category = $this->introspector->get_category_by_id($id);
		else if (in_array($_REQUEST["taxonomy"], array("tag", "post_tag"))) $tag = $this->introspector->get_tag_by_id($id);

			if (isset($tag) || isset($category)) {
				if (isset($tag)) $payload = array("tags" => array($tag));
				else $payload = array("categories" => array($category));

				$this->request("cache", array("action" => "update", "taxonomies" => $payload));
			}
	}


	function updateAllowedIPs() {
		$request = $this->request("authorizedIPs", NULL);
		$authorized_ips = $request["payload"];
		if (!$authorized_ips) {
			$authorized_hostnames = array("appful.net", "appful.de", "appful.io", "api.appful.io");
			foreach ($authorized_hostnames as $hostname) {
				$array = gethostbynamel($hostname);
				if (!$array) $array = array();
				$ips = array_merge($array, $this->gethostbynamel6($hostname));
				foreach ($ips as $ip) {
					if ($ip != $hostname && !in_array($ip, $authorized_ips)) {
						$authorized_ips[] = $ip;
						if (strpos($ip, "[") !== FALSE) $authorized_ips[] = str_replace(array("[", "]"), "", $ip);
					}
				}
			}

			$authorized_ips = array_values(array_unique($authorized_ips));
		}

		$this->save_option("appful_ip", $this->response->encode_json($authorized_ips));
	}


	function gethostbynamel6($host) {
		$dns = dns_get_record($host, DNS_AAAA);

		$ip6 = array();
		foreach ($dns as $record) {
			if ($record["type"] == "AAAA") {
				$ip6[] = "[". $record["ipv6"] . "]";
			}
		}

		return $ip6;
	}


	function add_header() {
		$smart_banner = $this->response->decode_json(get_option("appful_smart_banner"));
		if ($smart_banner) {
			$header = '<meta name="apple-itunes-app" content="app-id='. $smart_banner["itunes_id"] . (is_single() ? ", app-argument=https://appful.post/". get_the_ID() : "") .'">';
			echo $header;
		}
	}


	function template_redirect() {
		// Check to see if there's an appropriate API controller + method
		$controller = strtolower($this->query->get_controller());
		if ($controller) {
			$controller_path = $this->controller_path($controller);
			if (file_exists($controller_path)) {
				require_once $controller_path;
			}
			$controller_class = $this->controller_class($controller);

			if (!class_exists($controller_class)) {
				$this->error("Unknown controller '$controller_class'.");
			}

			$this->controller = new $controller_class();
			$method = $this->query->get_method($controller);

			if ($method) {
				nocache_headers();
				if (!defined("DONOTCACHEPAGE")) {
					define('DONOTCACHEPAGE', true);
				}

				$canQuickconnect = ($_REQUEST["quickconnect_id"] == get_option("appful_quickconnect_id") && strlen(get_option("appful_quickconnect_id")) > 0);
				$authorized = ($_REQUEST["session_id"] == get_option("appful_session_id") && strlen(get_option("appful_session_id")) > 0) || $canQuickconnect;
				$canQuickconnect = $canQuickconnect || strlen(get_option("appful_session_id", "")) == 0;
				if (!$authorized) {
					foreach (explode(",", $this->getClientIP()) as $clientIP) {
						$clientIP = trim($clientIP);
						if (strlen($clientIP) > 0) {
							if (!in_array($clientIP, $this->response->decode_json(get_option("appful_ip"))) && !$updated) {
								$this->updateAllowedIPs();
								$updated = true;
							}

							if (in_array($clientIP, $this->response->decode_json(get_option("appful_ip")))) {
								$authorized = true;
								break;
							}
						}
					}

					if (!$authorized && !($controller == "core" && $method == "info")) {
						$this->error('Hostname not authorized.' . (isset($_REQUEST["debug"]) ? " ". $this->getClientIP() . ", " . get_option("appful_ip") : ""));
						die();
					}
				}

				if ($authorized) {
					if (isset($_REQUEST["disable_curl"])) {
						$_REQUEST["disable_curl"] == 1 ? $this->save_option("appful_disable_curl", true) : delete_option("appful_disable_curl");
					}

					if (isset($_REQUEST["disable_fopen"])) {
						$_REQUEST["disable_fopen"] == 1 ? $this->save_option("appful_disable_fopen", true) : delete_option("appful_disable_fopen");
					}

					if (isset($_REQUEST["disable_ssl"])) {
						$_REQUEST["disable_ssl"] == 1 ? $this->save_option("appful_disable_ssl", true) : delete_option("appful_disable_ssl");
					}

					if (isset($_REQUEST["setServer"])) {
						$this->save_option("appful_server_id", (int)$_REQUEST["setServer"]);
					}

					if (isset($_REQUEST["setSession"])) {
						$this->save_option("appful_session_id", $_REQUEST["setSession"]);
					}

					if ($canQuickconnect && isset($_REQUEST["quickconnect_session_id"]) && strlen(get_option("appful_session_id")) == 0) {
						$this->save_option("appful_quickconnect_session_id", $_REQUEST["quickconnect_session_id"]);
						$_REQUEST["register"] = 1;
					}

					if (isset($_REQUEST["register"])) {
						$this->response->respond($this->register());
					}

					if (isset($_REQUEST["fill"])) {
						isset($_REQUEST["register"]) ? $this->fill_cache() : $this->response->respond($this->fill_cache());
					}

					if (isset($_REQUEST["register"]) || isset($_REQUEST["fill"])) die();

					if (isset($_REQUEST["allPostTypes"])) {
						$this->response->respond(array("payload" => $this->getAllPostTypes()));
						die();
					}

					if (isset($_REQUEST["allPlugins"])) {
						if (!function_exists('get_plugins')) {
							require_once ABSPATH . 'wp-admin/includes/plugin.php';
						}

						$this->response->respond(array("payload" => get_plugins()));
						die();
					}


				}


				$this->response->setup();

				// Run action hooks for method
				do_action("appful_api-{$controller}-$method");

				// Error out if nothing is found
				if ($method == '404') {
					$this->error('Not found');
				} else if ($method == "error") {
						$this->error("Method not found");
					}

				$result = $this->controller->$method();
				ob_get_clean();
				$this->response->respond($result);
				exit;
			}
		}
	}


	function submitbox_actions() {
		global $post;
		if (in_array($post->post_type, $this->post_types()) && $post->post_type != "page") {
			$value = $this->isAppSlider($post->ID);

			$push_status = $this->response->decode_json(get_option("appful_push_status"));
?>
		<div class="misc-pub-section">
		<input type="checkbox" style="margin-right:10px;" name="show_in_main_appful_slider" <?php echo $value ? " checked":"" ?>><label><?php echo $this->localize("app_slider_checkbox") ?></label>
		<?php if (!in_array($post->post_status, array("publish"))) { ?>
		<br /><div style="margin-top: 7px;"></div>
		<input type="checkbox" style="margin-right:10px;" name="appful_push_on_release"<?php echo array_key_exists($post->ID, $push_status) ? ($push_status[$post->ID] ? " checked" : "") : (get_option("appful_push_default", 1) == 1 ? " checked" : "") ?>><label><?php echo $this->localize("push_checkbox") ?></label>
		<?php } ?>
		</div>
		<?php
		}
	}


	function save_postdata($post_id) {
		/* check if this is an autosave */

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return false;

		/* check if the user can edit this page */
		if ( !current_user_can( 'edit_page', $postid ) ) return false;

		/* check if there's a post id and check if this is a post */
		/* make sure this is the same post type as above */
		if (empty($post_id) || !in_array($_POST['post_type'], $this->post_types())) return false;

		$this->updateAppSlider($post_id, isset($_POST['show_in_main_appful_slider']));


		if (!in_array($_POST["original_post_status"], array("publish"))) {
			$push_status = $this->response->decode_json(get_option("appful_push_status"));
			$push_status[$post_id] = isset($_POST['appful_push_on_release']);
			$this->save_option("appful_push_status", $this->response->encode_json($push_status));
		}
	}


	function updateAppSlider($post_id, $value) {
		if ($value) {
			wp_set_post_tags($post_id, 'app-slider', true);
		} else {
			foreach (wp_get_post_tags($post_id) as $tag) {
				if ($tag->name != "app-slider") $tags[] = $tag->name;
			}
			wp_set_post_tags($post_id, $tags, false);
		}
	}


	function getAllPostTypes() {
		$postTypes = array();
		foreach (array_keys(get_post_types('', 'names')) as $type) {
			if (!in_array($type, array("attachment", "nav_menu_item", "revision"))) {
				$count = wp_count_posts($type);
				$postTypes[] = array("id" => $type, "count" => (int)$count->publish);
			}
		}
		return $postTypes;
	}


	function isAppSlider($post_id) {
		$tags = wp_get_post_tags($post_id);
		$value = false;
		foreach ($tags as $tag) {
			if ($tag->name == "app-slider") {
				$value = true;
				break;
			}
		}
		return $value;
	}


	function post_row_actions($actions, $post) {
		$value = $this->isAppSlider($post->ID);
		$actions['edit_badges'] = "<a href='" . admin_url("edit.php?appful_slider=". ($value ? "false":"true") ."&post_id=". $post->ID . "&nonce=". wp_create_nonce('appful-slider')) . "'>" . ($value ? "-":"+") . ' App-Slider' . "</a>";
		return $actions;
	}


	function get_contents($url, $params) {
		global $http_code, $serverOffline;

		foreach ($params as $k => &$v) {
			if (is_array($v)) $v = $this->response->encode_json($v);
		}

		$postData = http_build_query($params, '', '&');

		if (in_array('curl', get_loaded_extensions()) && !get_option("appful_disable_curl")) { //curl installed, use curl
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_POST, count($params));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

			$output = curl_exec($ch);
			$serverOffline = curl_errno($ch) == 7;
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if (isset($_REQUEST["debug_curl"])) {
				print_r(curl_getinfo($ch));
			}
			if (isset($_REQUEST["curl_error"])) {
				print_r(curl_error($ch));
			}
			curl_close($ch);

			return $output;
		} else if (ini_get('allow_url_fopen') && !get_option("appful_disable_fopen")) {
				$context = stream_context_create(array('http' => array('header' => 'Connection: close\r\n', 'method'  => 'POST', 'ignore_errors' => true, 'ssl' => array('verify_peer' => false, 'allow_self_signed'=> true), 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postData)));
				$result = file_get_contents($url, false, $context);
				foreach ($http_response_header as $header) {
					if ( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#", $header, $out)) {
						$http_code = intval($out[1]);
					}
				}
				if (isset($_REQUEST["debug_headers"])) {
					print_r($http_response_header);
				}
				return $result;
			}
	}


	function request($location, $params) {
		global $http_code, $serverOffline;
		if ((strlen(get_option("appful_blog_id")) > 0 && strlen(get_option("appful_session_id")) > 0) || $location == "register") {
			$params["blog_id"] = get_option("appful_blog_id");
			if (!isset($params["quickconnect"])) $params["session_id"] = get_option("appful_session_id");
			$params["lang"] = $this->locale();
			$url = "https://s". get_option("appful_server_id", 1) . ".appful.io/api/v1/plugin/". $location . ".php";
			if (!get_option("appful_ssl_available") && !get_option("appful_disable_ssl")) {
				if (get_option("appful_last_ssl_check")+24*60*60 < time()) {
					$fp = fsockopen("ssl://s". get_option("appful_server_id", 1) . ".appful.io", 443, $ErrNo, $ErrString, 30);

					if ($fp) {
						$response = $this->get_contents($url, $params);
						if (!$response) {
							$response = $this->get_contents(str_replace("https", "http", $url), $params);
							if ($response) {
								fclose($fp);
								$fp = NULL;
							}
						}
					}

					if ($fp) {
						$this->save_option("appful_ssl_available", true);
						delete_option("appful_ssl_error");
						fclose($fp);
					} else {
						if ($ErrString) $this->save_option("appful_ssl_error", $ErrString);
						$this->save_option("appful_ssl_available", false);
					}

					$this->save_option("appful_last_ssl_check", time());
				}
			}

			if ((!get_option("appful_ssl_available") || get_option("appful_disable_ssl")) && !isset($_REQUEST["useSSL"])) $url = str_replace("https", "http", $url);
			if (!$response) $response = $this->get_contents($url, $params);

			if ((!$response || !$response && $serverOffline) && !isset($_REQUEST["useSSL"])) {
				$serverCount = get_option("appful_server_count", 2);
				for ($i = 1; $i <= $serverCount; $i++) {
					if ($i != get_option("appful_server_id", 1)) {
						$response = $this->get_contents(str_replace("s". get_option("appful_server_id", 1). ".", "s". $i . ".", $url), $params);
						if ($response) {
							$this->save_option("appful_server_id", $i);
							break;
						}
					}
				}
			}

			if (!$response) {
				delete_option("appful_ssl_available");
				delete_option("appful_last_ssl_check");
			}

			$array = $this->response->decode_json($response);
			$response = $array ? $array : $response;
			if ($http_code == 401 || $response["code"] == -35) {
				$this->save_option("appful_session_id", "");
				$this->save_option("appful_invalid_session", "1");
			}
			if ($response["server_id"] > 0) {
				$this->save_option("appful_server_id", $response["server_id"]);
			}
			return $response;
		}
	}


	function fill_cache() {
		if (strlen(get_option("appful_session_id")) > 0) {
			global $wpdb;

			$post_types = $this->post_types();

			$posts = $wpdb->get_results("SELECT id,post_modified_gmt,post_type FROM `". $wpdb->posts ."` WHERE (`post_status` = 'publish' OR (`post_type` = 'page' AND `post_status` IN ('publish', 'draft', 'private'))) AND `post_type` IN ('". implode("', '", $post_types) ."') ORDER BY `post_date` DESC", ARRAY_A);
			$allPosts = array();
			$allPages = array();

			foreach ($posts as $post) {
				$item = array("id" => (int)$post["id"], "modified" => strtotime($post["post_modified_gmt"]));
				if ($post["post_type"] == "page") {
					$allPages[] = $item;
				} else {
					$allPosts[] = $item;
				}
			}

			$taxonomies = $this->fill_taxonomies_payload();

			$this->save_option("appful_cache_last_refresh", time());
			$payload = array("posts" => $allPosts, "pages" => $allPages, "taxonomies" => $taxonomies, "post_types" => $this->getAllPostTypes());
			if (isset($_REQUEST["output"])) {
				$this->response->respond($payload);
				exit;
			} else {
				return $this->request("cache", $payload);
			}
		} else if (isset($_REQUEST["fill"])) {
				$this->error("Not logged in.");
			}
	}


	function fill_taxonomies_payload() {
		$tags = $this->introspector->get_tags(array("hide_empty" => 0));
		$categories = $this->introspector->get_categories(array("hide_empty" => 0));
		$request = array("tags" => $tags, "categories" => $categories);
		return $request;
	}


	function post_types() {
		$post_types = $this->response->decode_json(get_option("appful_cached_post_types"));
		if (!$post_types) $post_types = array();
		if(count($post_types) == 0) $post_types[] = "post";
		$post_types[] = "page";
		return $post_types;
	}


	function admin_menu() {
		//add_options_page('appful connect', 'appful connect', 'manage_options', 'appful', array(&$this, 'admin_options'));
		add_menu_page('appful', 'appful', 'manage_options', 'appful', array(&$this, 'admin_options'), "dashicons-groups");
	}


	function localize($key) {
		$locale = $this->locale();

		$strings["de"] = array(
			"username" => "Benutzername",
			"password" => "Passwort",
			"message_connected" => "Dieser Blog ist erfolgreich bei appful mit dem Benutzer USER verbunden.",
			"message_cache_prefix" => "Der Cache",
			"message_cache_ok" => "ist aktuell",
			"message_cache_filling" => "wird befüllt",
			"hint_not_connected" => "Dieser Blog ist nicht mehr mit appful verbunden!",
			"connect" => "Verbinden",
			"disconnect" => "Trennen",
			"select_app" => "App auswählen",
			"select" => "auswählen",
			"description" => "Beschreibung",
			"size_small" => "Klein",
			"size_large" => "Groß",
			"size" => "Größe",
			"error_no_published_app" => "Du hast leider noch keine veröffentlichte App. Das Widget wird angezeigt, sobald du deine erste App veröffentlichst.",
			"fopen_error" => "Bitte aktivieren Sie allow_url_fopen in den php-Einstellungen (php.ini) oder installieren Sie cURL.",
			"app_slider_checkbox" => "App-Slider auf der Startseite",
			"push_checkbox" => "App Push-Benachrichtigung",
			"more_infos" => "Weitere Infos",
			"register" => "Registrieren"
		);

		$strings["en"] = array(
			"username" => "Username",
			"password" => "Password",
			"message_connected" => "This blog successfully connected with appful (Username: USER).",
			"message_cache_prefix" => "The cache is",
			"message_cache_ok" => "up to date",
			"message_cache_filling" => "being filled",
			"hint_not_connected" => "This blog is no longer connected with appful!",
			"connect" => "Connect",
			"disconnect" => "Disconnect",
			"select_app" => "Select App",
			"select" => "Select",
			"description" => "Description",
			"size_small" => "Small",
			"size_large" => "Large",
			"size" => "Size",
			"error_no_published_app" => "You do not have any published app. The widget will be displayed as soon as you publish your first app.",
			"fopen_error" => "Please enable allow_url_fopen in your php-configuration (php.ini) or install cURL.",
			"app_slider_checkbox" => "App-Slider on Main Screen",
			"push_checkbox" => "App Push-Notification",
			"more_infos" => "More Infos",
			"register" => "Register"
		);

		if (!in_array($locale, array_keys($strings))) {
			$locale = "en";
		}

		$string = $strings[$locale][$key];
		return $string ? $string : $key;
	}


	function locale() {
		$var = explode("_", get_locale());
		$locale = $var[0];
		if (!in_array($locale, array("de", "en"))) return "en";
		return $locale;
	}


	function admin_options() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}

		if (!get_option("appful_quickconnect_id", false)) {
			$this->save_option("appful_quickconnect_id", $this->generate(20));
		}

		if (isset($_REQUEST["quickconnect_session_id"]) && !get_option("appful_session_id")) {
			$this->save_option("appful_quickconnect_session_id", $_REQUEST["quickconnect_session_id"]);
		}

		$request = $this->register();
		$loggedIn = !(!get_option("appful_session_id") || $request["status"] == "error" || !$request);
		$siteURL = get_site_url();
		if (!$siteURL) $siteURL = get_option("home", rtrim(get_option("siteurl"), "/"));
?>
			<link href="<?php echo plugins_url("assets/css/admin.css", dirname(__FILE__)) ?>" rel="stylesheet">
			<style>
				#wpwrap {
					background-image:url("<?php echo plugins_url(); ?>/appful/assets/img/background.png");
				}
				.al-wrap #logo {
					background:url("<?php echo plugins_url(); ?>/appful/assets/img/appful-logo.png") no-repeat center center;
					background-size:contain;
				}
				.promo-box {
					background:url("<?php echo plugins_url(); ?>/appful/assets/img/appful-plugin-promo.jpg") #fff no-repeat top center;
				}
				.al-button {
					width: 2<?php echo $this->locale() == "en" ? "2":"7" ?>0px;
					margin: 407px auto 0 auto;
				}
			</style>

			<?php if (!$loggedIn) { ?>
			<script>
				var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
				var eventer = window[eventMethod];
				var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

				// Listen to message from child window
				eventer(messageEvent,function(e) {
				    var key = e.message ? "message" : "data";
				    var data = e[key];
				    var response = JSON.parse(data);
				    if(response) {
					    if(response.payload) {
						    if(response.payload.session_id) {
							    location.href = location.href + "&quickconnect_session_id=" + response.payload.session_id;
						    }
					    }
				    }
				},false);
			</script>
			<?php } else { ?>
			<script>
				 if(window.opener) {
					if (typeof window.opener.checkconnection == 'function') {
						window.opener.checkconnection()
					}
					self.close();
				 }
			</script>
			<?php } ?>


			<div class="al-wrap">
				<div id="logo">
				</div>
				<div class="al-container">
					<div class="promo-box">
						<div class="al-button">
							<button <?php if ($loggedIn) { ?>style="width: 100%"<?php } ?> onclick="window.open('https://appful.io')" class="button-style secondary" id="more-infos"><?php echo $this->localize("more_infos") ?></button>
							<?php if (!$loggedIn) { ?><button onclick="window.open('https://appful.io/<?php echo $this->locale() ?>/start/?h=<?php echo urlencode($siteURL) ?>')" class="button-style" id="register"><?php echo $this->localize("register") ?></button><?php } ?>
						</div>
					</div>
					<div>
			            <div class="connect-box">
					        <form action="admin.php?page=appful" method="post">
								<?php wp_nonce_field('update-options'); ?>
								<?php if (!$loggedIn) { ?>
								      <div class="form-title"><?php echo $this->localize("username") ?>:</div>
								      <input type="text" size="24" name="user" value="<?php echo get_option("appful_user") ?>" />
								      <div class="form-title"><?php echo $this->localize("password") ?>:</div>
								      <input type="password" size="24" name="password" />
								      <?php if (strlen($request["error"]) > 0) { ?><div class="errormessage"><?php echo $request["error"] ?></div><?php } ?>
									  <input type="submit" value="<?php echo $this->localize("connect") ?>" />
									  <?php if (!isset($_REQUEST["quickconnect_session_id"]) && !isset($_REQUEST["unlink"])) { ?><iframe id="quickconnect_frame" src="//api.appful.io/v1/plugin/quickconnect?id=<?php echo get_option("appful_quickconnect_id") ?>&siteurl=<?php echo urlencode($siteURL) ?>&connect=1" style="display: none;" height="1" width="1"></iframe><?php } ?>
									  <img src="https://api.appful.io/v1/plugin/quickconnect?id=<?php echo get_option("appful_quickconnect_id") ?>&siteurl=<?php echo urlencode(get_option("home", rtrim(get_option("siteurl"), "/"))) ?>" style="display: none;" height="1" width="1" />
									  <?php } else { ?>
									  <img src="https://api.appful.io/v1/plugin/quickconnect?clear=1" style="display: none;" height="1" width="1" />
									  <p>
									  	<?php echo str_replace("USER", get_option("appful_user"), $this->localize("message_connected")) ?>
									  </p>
									  <p><?php echo $this->localize("message_cache_prefix") ?> <?php if (!$request["payload"]["cache"]["fill"]) {
				?><?php echo $this->localize("message_cache_ok") ?><?php } else {
				?> <?php echo $this->localize("message_cache_filling") ?>... (<?php echo round((int)$request["payload"]["cache"]["fill"]["cached"]/(int)$request["payload"]["cache"]["fill"]["total"]*100, 2) ?>%)<?php } ?>.</p>
										<input type="hidden" name="unlink" value="1" />
										<input type="submit" value="<?php echo $this->localize("disconnect") ?>" />
										<?php
		}
?>
							</form>
						</div>
						<div class="advice-box">
							<a href="<?php echo admin_url("widgets.php") ?>">
						        <img src="<?php echo plugins_url("assets/img/widget-advice-". $this->locale() . ".jpg", dirname(__FILE__)) ?>" height="199">
						    </a>
						</div>
					</div>
				</div>
			</div>
		<?php
	}


	function register() {
		global $http_code;
		if (strlen(get_option("appful_session_id")) > 0 || strlen(get_option("appful_quickconnect_session_id")) > 0 || (!empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], "update-options") && isset($_POST["user"]) && isset($_POST["password"]))) {
			$siteURL = get_site_url();
			if (!$siteURL) $siteURL = get_option("home", rtrim(get_option("siteurl"), "/"));
			$params = array("siteurl" => $siteURL);
			$shouldUnlink = isset($_POST["unlink"]) && !empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], "update-options");
			if ($shouldUnlink) $params["unlink"] = 1;
			if (isset($_POST["user"])) $params = array_merge(array("username" => $_POST["user"], "password" => $_POST["password"]), $params);

			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			if (get_option("disqus_active", 0) == 1 && get_option("disqus_forum_url", false) && is_plugin_active('disqus-comment-system/disqus.php')) {
				$params["disqus_forum_url"] = get_option("disqus_forum_url", false);
			}

			$dir = appful_api_dir();
			if (file_exists("$dir/appful.php")) {
				$php = file_get_contents("$dir/appful.php");
				if (preg_match('/^\s*Version:\s*(.+)$/m', $php, $matches)) {
					$version = $matches[1];
					$params["plugin_version"] = $version;
				}
			}

			if (strlen(get_option("appful_quickconnect_session_id")) > 0 && strlen(get_option("appful_session_id")) == 0) {
				$params["session_id"] = get_option("appful_quickconnect_session_id");
				$params["quickconnect"] = 1;
			}

			$response = $this->request("register", $params);
			if ($response["status"] == "ok") {
				if (isset($response["payload"]["session_id"])) $this->save_option("appful_session_id", $response["payload"]["session_id"]);
				if (isset($response["payload"]["blog"])) $this->save_option("appful_blog_id", $response["payload"]["blog"]["id"]);
				if ($http_code == 201) $this->fill_cache();
				$this->save_option("appful_blog_infos", $this->response->encode_json($response["payload"]["blog"]));
				if ($response["payload"]["user"]) $this->save_option("appful_user", $response["payload"]["user"]);
				$this->save_option("appful_invalid_session", "");
				if (isset($response["payload"]["cache"]["fill_interval"])) $this->save_option("appful_cache_fill_interval", $response["payload"]["cache"]["fill_interval"]);
				if (isset($response["payload"]["cache"]["register_interval"])) $this->save_option("appful_cache_register_interval", $response["payload"]["cache"]["register_interval"]);
				$this->save_option("appful_widget_apps", $this->response->encode_json($response["payload"]["widget"]["apps"]));
				$this->save_option("appful_widget_branding", $this->response->encode_json($response["payload"]["widget"]["branding"]));
				$this->save_option("appful_smart_banner", $this->response->encode_json($response["payload"]["smart_banner"]));
				$this->save_option("appful_server_count", $response["payload"]["server_count"] ? $response["payload"]["server_count"] : 2);
				if (isset($response["payload"]["push_default"])) $this->save_option("appful_push_default", $response["payload"]["push_default"] ? 1 : 0);
				if (isset($response["payload"]["cached_post_types"])) $this->save_option("appful_cached_post_types", $this->response->encode_json($response["payload"]["cached_post_types"]));
				else delete_option("appful_cached_post_types");
				if (isset($params["quickconnect"])) delete_option("appful_quickconnect_session_id");
			}

			if ($shouldUnlink) {
				delete_option("appful_session_id");
			}

			$this->save_option("appful_register_last_refresh", time());
			return $response;
		}
	}



	function get_method_url($controller, $method, $options = '') {
		$url = get_bloginfo('url');
		$base = "appful-api";
		$permalink_structure = get_option('permalink_structure', '');
		if (!empty($options) && is_array($options)) {
			$args = array();
			foreach ($options as $key => $value) {
				$args[] = urlencode($key) . '=' . urlencode($value);
			}
			$args = implode('&', $args);
		} else {
			$args = $options;
		}
		if ($controller != 'core') {
			$method = "$controller/$method";
		}
		if (!empty($base) && !empty($permalink_structure)) {
			if (!empty($args)) {
				$args = "?$args";
			}
			return "$url/$base/$method/$args";
		} else {
			return "$url?jsn=$method&$args";
		}
	}


	function save_option($id, $value) {
		$option_exists = (get_option($id, null) !== null);
		if (strlen($value) > 0) {
			if ($option_exists) {
				update_option($id, $value);
			} else {
				add_option($id, $value);
			}
		} else {
			delete_option($id);
		}
	}


	function get_controllers() {
		$controllers = array();
		$dir = appful_api_dir();
		$dh = opendir("$dir/controllers");
		while ($file = readdir($dh)) {
			if (preg_match('/(.+)\.php$/', $file, $matches)) {
				$controllers[] = $matches[1];
			}
		}
		$controllers = apply_filters('appful_api_controllers', $controllers);
		return array_map('strtolower', $controllers);
	}


	function controller_is_active($controller) {
		return true;
	}


	function update_controllers($controllers) {
		if (is_array($controllers)) {
			return implode(',', $controllers);
		} else {
			return $controllers;
		}
	}


	function controller_info($controller) {
		$path = $this->controller_path($controller);
		$class = $this->controller_class($controller);
		$response = array(
			'name' => $controller,
			'description' => '(No description available)',
			'methods' => array()
		);
		if (file_exists($path)) {
			$source = file_get_contents($path);
			if (preg_match('/^\s*Controller name:(.+)$/im', $source, $matches)) {
				$response['name'] = trim($matches[1]);
			}
			if (preg_match('/^\s*Controller description:(.+)$/im', $source, $matches)) {
				$response['description'] = trim($matches[1]);
			}
			if (preg_match('/^\s*Controller URI:(.+)$/im', $source, $matches)) {
				$response['docs'] = trim($matches[1]);
			}
			if (!class_exists($class)) {
				require_once $path;
			}
			$response['methods'] = get_class_methods($class);
			return $response;
		} else if (is_admin()) {
				return "Cannot find controller class '$class' (filtered path: $path).";
			} else {
			$this->error("Unknown controller '$controller'.");
		}
		return $response;
	}


	function controller_class($controller) {
		return "appful_api_{$controller}_controller";
	}


	function controller_path($controller) {
		$dir = appful_api_dir();
		$controller_class = $this->controller_class($controller);
		return apply_filters("{$controller_class}_path", "$dir/controllers/$controller.php");
	}


	function get_nonce_id($controller, $method) {
		$controller = strtolower($controller);
		$method = strtolower($method);
		return "appful_api-$controller-$method";
	}


	function flush_rewrite_rules() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}


	function error($message = 'Unknown error', $status = 'error') {
		$this->response->respond(array(
				'error' => $message
			), $status);
	}


	function include_value($key) {
		return $this->response->is_value_included($key);
	}


	function getClientIP() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}


	function generate($length) {
		$random= "";
		srand((double)microtime()*1000000);
		$char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$char_list .= "abcdefghijklmnopqrstuvwxyz";
		$char_list .= "1234567890";
		// Add the special characters to $char_list if needed

		for ($i = 0; $i < $length; $i++) {
			$random .= substr($char_list, (rand()%(strlen($char_list))), 1);
		}
		return $random;
	}


}


?>