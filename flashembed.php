<?php
if (!class_exists('cfct_module_image')) {
	require_once(CFCT_BUILD_DIR.'modules/image/image.php');
}
if (!class_exists('cfct_module_flashembed') && class_exists('cfct_module_image')) {
	class cfct_module_flashembed extends cfct_module_image {
	
		protected $default_flash_height = 150;	
		protected $default_flash_width = 150;	
	
		/**
		 * Set up the module
		 */
		 
		public function __construct() {
			$this->pluginDir		= basename(dirname(__FILE__));
			$this->pluginPath		= WP_PLUGIN_DIR . '/' . $this->pluginDir;
			$this->pluginUrl 		= WP_PLUGIN_URL.'/'.$this->pluginDir;		
	
			add_action('get_header', array(&$this, 'flashembed_get_header'));	
			$opts = array(
				'url' => $this->pluginUrl, 
				'view' => 'wp-cb-flashembed/view.php',
				'description' => __('Allows to embed flash into pages', 'carrington-build'),
				'icon' => 'wp-cb-flashembed/icon.png'
			);
			cfct_build_module::__construct('cfct-module-flashembed', __('Flash', 'carrington-build'), $opts);
			
		}

		public function flashembed_get_header() {
			wp_enqueue_script('flashembed', $this->pluginUrl.'/js/toolbox.flashembed.js', array('jquery'), '1.0');
		}

		/**
		 * Display the module content in the Post-Content
		 * 
		 * @param array $data - saved module data
		 * @return array string HTML
		 */
		public function display($data) {
			$flash = '';
			if (!empty($data[$this->get_field_name('image_id')])) {
				$size = (!empty($data[$this->get_field_name('image_id').'-size']) ? $data[$this->get_field_name('image_id').'-size'] : 'thumbnail');
				$flash = wp_get_attachment_url($data[$this->get_field_name('image_id')]);				
				$url = $this->get_link_url($data);
				$info = getimagesize($flash);
				$width = $info[0];
				$height = $info[1];
				$width = (!empty($data[$this->get_field_name('flash-width')]) ? intval($data[$this->get_field_name('flash-width')]) : $width);
				$height = (!empty($data[$this->get_field_name('flash-height')]) ? intval($data[$this->get_field_name('flash-height')]) : $height);
			}
			
			global $cfct_build;			
	
			$cfct_build->loaded_modules[$this->basename] = $this->pluginPath;
			$cfct_build->module_paths[$this->basename] = $this->pluginPath;
			$cfct_build->module_urls[$this->basename] = $this->pluginUrl;
			
			return $this->load_view($data, compact('flash', 'url', 'width', 'height'));
		}

	    private function img_size($data = array()) {
			return (!empty($data[$this->get_field_id('link_img_size')]) ? $data[$this->get_field_id('link_img_size')] : 'large');
		}		
	    private function link_target($data = array()) {
			return (!empty($data[$this->get_field_id('link_target')]) ? $data[$this->get_field_id('link_target')] : 'none');
		}

		/**
		 * Show a select list of available image sizes as defined in WordPress
		 *
		 * @param array $args - see this::image_selector() for args definition
		 * @return string HTML 
		 */
		protected function _image_selector_size_select($args) {
			$_sizes = get_intermediate_image_sizes();
			$image_sizes = array();
			foreach ($_sizes as $size) {
				$image_sizes[$size] = $size;
			}
			$image_sizes = apply_filters('cfct-build-image-size-select-sizes', $image_sizes, $this->id_base);
			
			$html = '
				<div class="cfct-image-select-size">
					<label for="'.$this->id_base.'-'.$args['field_name'].'-image-select-size">'.__('Flash Size', 'carrington-build').'</label>
					<select name="'.$this->get_field_name($args['field_name']).'-size" id="'.$this->id_base.'-'.$args['field_name'].'-image-select-size">';
			foreach ($image_sizes as $size => $name) {
				$html .= '
						<option value="'.$size.'"'.selected($size, $args['selected_size'], false).'>'.($size == $name ? $this->humanize($name, true, array('-')) : esc_html($name)).'</option>';
			}			
			$html .= '
					</select>
				</div>
				<div class="clear"></div>';
			return $html;
		}
		
		/**
		 * Build the admin form
		 * 
		 * @param array $data - saved module data
		 * @return string HTML
		 */
		public function admin_form($data) {
			// tabs
			$image_selector_tabs = array(
				$this->id_base.'-post-image-wrap' => __("Post Media", 'carrington-build'),
				$this->id_base.'-global-image-wrap' => __('All Media', 'carrington-build')
			);
			
			// set active tab
			$active_tab = $this->id_base.'-post-image-wrap';
			if (!empty($data[$this->get_field_name('global_image')])) {
				$active_tab = $this->id_base.'-global-image-wrap';
			}
			
			// set default link target
			$link_target = $this->link_target($data);
			
			// set default image size
			$link_img_size = $this->img_size($data);
			
			$html = '
				<fieldset>
					<!-- image selector tabs -->
					<div id="'.$this->id_base.'-image-selectors">
						<!-- tabs -->
						'.$this->cfct_module_tabs($this->id_base.'-image-selector-tabs', $image_selector_tabs, $active_tab).'
						<!-- /tabs -->
					
						<div class="cfct-module-tab-contents">
							<!-- select an image from this post -->
							<div id="'.$this->id_base.'-post-image-wrap" '.($active_tab == $this->id_base.'-post-image-wrap' ? ' class="active"' : null).'>
								'.$this->post_image_selector($data).'
							</div>
							<!-- / select an image from this post -->
					
							<!-- select an image from media gallery -->
							<div id="'.$this->id_base.'-global-image-wrap" '.($active_tab == $this->id_base.'-global-image-wrap' ? ' class="active"' : null).'>
								'.$this->global_image_selector($data).'
							</div>
							<!-- /select an image from media gallery -->
						</div>

						<fieldset class="cfct-ftl-border">
							<legend>Flash Size</legend>
							<table class="'.$this->id_base.'-flash-size">
								<tr>
									<td align="right">
										<label for="'.$this->get_field_id('flash-width').'">'.__('Width', 'carrington-build').'</label>
									</td>
									<td>
										<input type="text" name="'.$this->get_field_name('flash-width').'" id="'.$this->get_field_id('flash-width').'" value="'.(!empty($data[$this->get_field_name('flash-width')]) ? esc_attr($data[$this->get_field_name('flash-width')]) : $this->default_flash_width).'" />
										<span>pixels</span>										
									</td>
								</tr>
								<tr>
									<td align="right">
										<label for="'.$this->get_field_id('flash-height').'">'.__('Height', 'carrington-build').'</label>
									</td>
									<td>
										<input type="text" name="'.$this->get_field_name('flash-height').'" id="'.$this->get_field_id('flash-height').'" value="'.(!empty($data[$this->get_field_name('flash-height')]) ? esc_attr($data[$this->get_field_name('flash-height')]) : $this->default_flash_height).'" />
										<span>pixels</span>										
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
					<!-- / image selector tabs -->
				</fieldset>
				';
				
			return $html;
		}

		
		/**
		 * Method to output a simple "post" image selector
		 * Image selector shows images attached to a particular post
		 *
		 * @see image_selector() for $args descriptions
		 * @param array $args
		 * @return string HTML
		 */
		public function _post_image_selector($args) {
			if (empty($args['post_id'])) {
				return false;
			}

			$attachment_args = array(
				'post_type' => 'attachment',
				'post_mime_type' => 'application/x-shockwave-flash',
				'numberposts' => -1,
				'post_status' => 'inherit',
				'post_parent' => $args['post_id'],
				'order' => 'ASC'
			); 

			$attachments = get_posts($attachment_args); 

			
			if (count($attachments)) {
				$id = $this->id_base.'-'.$args['field_name'].'-image-select-items-list';
				
				$class = 'cfct-post-image-select cfct-image-select-items-list '.$this->_image_list_dir_class($args);
				if (!empty($args['allow_multiple']) && $args['allow_multiple'] == true) {
					$class .= ' cfct-post-image-select-multiple';
					$note = __('Select one or more Flashes', 'carrington-build');
				}
				else {
					$class .= ' cfct-post-image-select-single';
					$note = __('Select a Flash', 'carrington-build');
				}
				
				$html = '
					<p class="cfct-image-select-note">'.$note.'</p>
					<div id="'.$id.'" class="'.$class.'">
						<div>
							'.$this->_image_list($attachments, $args).'
							<input type="hidden" name="'.$this->get_field_name($args['field_name']).'" id="'.$this->get_field_id($args['field_name']).'" value="'.$args['selected_image'].'" />
						</div>
					</div>
					'/*.$this->_image_selector_size_select($args)*/;
			}
			else {
				$html = '<div class="cfct-image-select-no-images">'.__('No images found for the selected post.', 'carrington-build').'</div>';
			}
			return apply_filters($this->id_base.'-image-select-html', $html, $args);
		}

		/**
		 * Method to output a "global" image selector for searching the entire media gallery
		 * Image selector is loaded via ajax based on a search term entered by user
		 *
		 * @see image_selector() for $args descriptions
		 * @param array $args
		 * @return string HTML
		 */
		public function _global_image_selector($args) {
			$value = null;
			
			if (!empty($args['selected_image'])) {
				$image = get_post($args['selected_image']);
				$selected_image = '<div class="cfct-image-select-items-list-item active">'.$this->_image_list_item($image, $args['image_size']).'</div>';
			}
			else {
				$selected_image = '<div class="cfct-image-select-items-list-item cfct-image-select-items-no-image"><div><div class="cfct-image-list-item-title">'.__('No flash selected', 'carrington-build').'</div></div></div>';
			}
			$html = '
				<div id="'.$this->id_base.'-'.$args['field_name'].'-global-image-search" class="cfct-global-image-select cfct-image-select-b">
					<div class="'.$this->id_base.'-global-image-select-search">
						<input type="text" name="'.$this->id_base.'-'.$args['field_name'].'-image-search" title="'.__('Search', 'carrington-build').'&hellip;" value="" id="'.$this->id_base.'-'.$args['field_name'].'-image-search" class="cfct-global-image-search-field" data-image-size="'.$args['image_size'].'" />
						<input type="hidden" id="'.$this->get_field_id($args['field_name']).'" class="cfct-global-image-select-value" name="'.$this->get_field_name($args['field_name']).'" value="'.$args['selected_image'].'" />
						
						<div class="cfct-image-scroller-group">
							<div class="cfct-global-image-search-current-image cfct-image-select-current-image cfct-image-select-items-list-item">
								'.$selected_image.'
								<p>'.__('Current Selection', 'carrington-build').'</p>
							</div><div class="cfct-global-image-search-results cfct-image-select-items-list '.$this->_image_list_dir_class($args).' cfct-image-select-items-list-b" id="'.$this->id_base.'-'.$args['field_name'].'-live-search-results"></div>
						</div>
					</div>
					'.''/*$this->_image_selector_size_select($args)*/.'
				</div>
				';
			return apply_filters($this->id_base.'-global-image-select-html', $html, $args);
		}
		
		
		protected function _global_image_search() {
			$term = trim(stripslashes($_POST['term']));
			
			$images = query_posts(array(
				's' => $term,
				'posts_per_page' => 20,
				'post_type' => 'application/x-shockwave-flash', 
				'post_mime_type' => 'image',
				'post_status' => 'inherit',
				'order' => 'ASC'
			));

			$args = array(
				'image_size' => (!empty($_POST['image_size']) ? esc_attr($_POST['image_size']) : 'thumbnail')
			);

			$html = '<div>';
			if (count($images)) {
				$html .= $this->_image_list($images, $args);
			}
			else {
				$html .= '
					<ul class="'.$this->id_base.'-image-select-items">
						<li class="cfct-image-select-items-list-item cfct-image-select-items-no-image" data-image-id="0">
							'.sprintf(__('No flashes found<br />for term "%s"', 'carrington-build'), esc_html($_POST['term'])).'
						</li>
					</ul>';
			}
			$html .= '</div>';
			
			$ret = array(
				'success' => (count($images) ? true : false),
				'term' => esc_html($_POST['term']),
				'html' => $html
			);
			
			header('content-type: text/javascript charset=utf8');
			echo cf_json_encode($ret);
			exit;
		}

		public function admin_css() {
			$css = parent::admin_css();
			$css .= '
				#'.$this->id_base.'-edit-form fieldset .'.$this->id_base.'-flash-size input[type=text] {
					width: 50px;
					height: 29px;
					text-align: right;
				}
			';
			return $css;
		}		
		
	}
	
	// register the module with Carrington Build
	cfct_build_register_module('cfct-module-flashembed', 'cfct_module_flashembed');
}

?>