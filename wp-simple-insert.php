<?php
/*
   Plugin Name: WP Simple Insert
   PLugin URI: http://kbhaskar.in/wp-simple-insert/
   Version: 1.1.3
   Author: Bhaskar Kandiyal
   Description: Allows you to add custom HTML and PHP to your posts
   License: GPL2
*/

/*  
       Copyright 2012  Bhaskar Kandiyal  (email : bkandiyal@gmail.com, website: http://kbhaskar.in/)

       This program is free software; you can redistribute it and/or modify
       it under the terms of the GNU General Public License, version 2, as 
       published by the Free Software Foundation.

       This program is distributed in the hope that it will be useful,
       but WITHOUT ANY WARRANTY; without even the implied warranty of
       MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
       GNU General Public License for more details.

       You should have received a copy of the GNU General Public License
       along with this program; if not, write to the Free Software
       Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if(!class_exists("WPSimpleInsert"))
{
	register_activation_hook(__FILE__, array('WPSimpleInsert', 'install'));
	//register_deactivation_hook(__FILE__, array('WPSimpleInsert', 'remove'));
	
	if(is_admin())
	{
		add_action('admin_menu', array('WPSimpleInsert', 'add_admin_menu'));
		add_action('admin_init', array('WPSimpleInsert', 'options_init'));
	}
	
	add_filter('the_content', array('WPSimpleInsert', 'filter_the_post'));
	add_action('init', array('WPSimpleInsert', 'init'));
	
	class WPSimpleInsert {
		
		function init() {
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('admin-widgets');
		}
		function install() {
			$arr = array('before_text' => array('enabled'=> 0, 'text' => '', 'dont_show_home'=>0, 'dont_show_category'=>0, 'dont_show_archive'=>0), 'after_text' => array('enabled' => 0, 'text' => '', 'dont_show_home' => 0, 'dont_show_category'=>0, 'dont_show_archive'=>0), 'eval_php' => 0);
			update_option('wp_simple_insert_options', $arr);
		}
		
		function filter_the_post($content = '')	{
			$options = get_option('wp_simple_insert_options');
			$show_before = false;
			$show_after = false;

			if($options['before_text']['enabled'] == 1)
			{
				if(is_home() && is_front_page())
				{
					if($options['before_text']['dont_show_home'] == 1)
						$show_before = false;
					else
						$show_before = true;
				}
				else if(is_category())
				{
					if($options['before_text']['dont_show_category'] == 1)
						$show_before = false;
					else
						$show_before = true;
				}
				else if(is_archive())
				{
					if($options['before_text']['dont_show_archive'] == 1)
						$show_before = false;
					else
						$show_before = true;
				}
				else
					$show_before = true;
			}

			if($options['after_text']['enabled'] == 1)
			{
				if(is_home() && is_front_page())
				{
					if($options['after_text']['dont_show_home'] == 1)
						$show_after = false;
					else
						$show_after = true;
				}
				else if(is_category())
				{
					if($options['after_text']['dont_show_category'] == 1)
						$show_after = false;
					else
						$show_after = true;
				}
				else if(is_archive())
				{
					if($options['after_text']['dont_show_archive'] == 1)
						$show_after = false;
					else
						$show_after = true;
				}
				else
					$show_after = true;
			}

			$before_content = $options['before_text']['text'];
			$after_content = $options['after_text']['text'];
			
			$temp = $content;
			if($show_before && !empty($before_content)) {
				if(!$options['eval_php']) $temp = $before_content . $temp;
				else {
					ob_start();
					eval('?>'.$before_content);
					$t = ob_get_contents();
					ob_end_clean();
					$temp = $t . $temp;
				}
			}
			if($show_after && !empty($after_content)) {
				if(!$options['eval_php']) $temp = $temp . $after_content;
				else {
					ob_start();
					eval('?>'.$after_content);
					$t = ob_get_contents();
					ob_end_clean();
					$temp = $temp . $t;
				}
			}
			return $temp;
		}
		
		function add_admin_menu() {
			add_options_page('WPSimpleInsert Settings', 'WPSimpleInsert', 'administrator', basename(__FILE__), array('WPSimpleInsert', 'show_options_page'));
		}
		
		function options_init() {
			register_setting('wp_simple_insert_options', 'wp_simple_insert_options', array('WPSimpleInsert', 'validate_options'));
			
			add_settings_section('wp_simple_insert_section_before', 'Before Post Content', array('WPSimpleInsert', 'wp_simple_insert_section_before_cb'), __FILE__);
			
			add_settings_field('wp_simple_insert_before_text', 'Before Code:', array('WPSimpleInsert', 'settings_before_cb'), __FILE__, 'wp_simple_insert_section_before', array('type'=>'textarea'));
			
			add_settings_field('wp_simple_insert_before_text_dont_show_home', 'Hide from home page?', array('WPSimpleInsert', 'settings_before_cb'), __FILE__, 'wp_simple_insert_section_before', array('type'=>'checkbox', 'id'=>'wp_simple_insert_before_text_dont_show_home'));
			
			add_settings_field('wp_simple_insert_before_text_dont_show_category', 'Hide from category page?', array('WPSimpleInsert', 'settings_before_cb'), __FILE__, 'wp_simple_insert_section_before', array('type'=>'checkbox', 'id'=>'wp_simple_insert_before_text_dont_show_category'));
			
			add_settings_field('wp_simple_insert_before_text_dont_show_archive', 'Hide from archive page?', array('WPSimpleInsert', 'settings_before_cb'), __FILE__, 'wp_simple_insert_section_before', array('type'=>'checkbox', 'id'=>'wp_simple_insert_before_text_dont_show_archive'));
			
			add_settings_field('wp_simple_insert_before_text_enabled', 'Enabled', array('WPSimpleInsert', 'settings_before_cb'), __FILE__, 'wp_simple_insert_section_before', array('type'=>'button'));
			
			add_settings_section('wp_simple_insert_section_after', 'After Post Content', array('WPSimpleInsert', 'wp_simple_insert_section_after_cb'), __FILE__);
			
			add_settings_field('wp_simple_insert_after_text', 'Code:', array('WPSimpleInsert', 'settings_after_cb'), __FILE__, 'wp_simple_insert_section_after', array('type'=>'textarea'));
			
			add_settings_field('wp_simple_insert_after_text_dont_show_home', 'Hide from home page?', array('WPSimpleInsert', 'settings_after_cb'), __FILE__, 'wp_simple_insert_section_after', array('type'=>'checkbox', 'id'=>'wp_simple_insert_after_text_dont_show_home'));
			
			add_settings_field('wp_simple_insert_after_text_dont_show_category', 'Hide from category page?', array('WPSimpleInsert', 'settings_after_cb'), __FILE__, 'wp_simple_insert_section_after', array('type'=>'checkbox', 'id'=>'wp_simple_insert_after_text_dont_show_category'));
			
			add_settings_field('wp_simple_insert_after_text_dont_show_archive', 'Hide from archive page?', array('WPSimpleInsert', 'settings_after_cb'), __FILE__, 'wp_simple_insert_section_after', array('type'=>'checkbox', 'id'=>'wp_simple_insert_after_text_dont_show_archive'));
			
			add_settings_field('wp_simple_insert_after_text_enabled', 'Enabled', array('WPSimpleInsert', 'settings_after_cb'), __FILE__, 'wp_simple_insert_section_after', array('type'=>'button'));
			
			add_settings_section('wp_simple_insert_section_misc', 'Misc', array('WPSimpleInsert', 'wp_simple_insert_section_misc_cb'), __FILE__);
			
			add_settings_field('wp_simple_insert_eval_php', 'Evaluate PHP?', array('WPSimpleInsert', 'settings_misc_cb'), __FILE__, 'wp_simple_insert_section_misc', array('type'=>'eval-php'));
		}
		
		function settings_before_cb($args = array())
		{
			$options = get_option('wp_simple_insert_options');
			switch($args['type'])
			{
				case 'textarea':
					echo "<textarea id='wp_simple_insert_before_text' name='wp_simple_insert_options[before_text][text]' type='text' rows='10' cols='50'>".$options['before_text']['text']."</textarea>";
				break;
				
				case 'button':
					$val = (($options['before_text']['enabled']==0) || (empty($options['before_text']['enabled'])))?'Disabled':'Enabled';
					?>
					
					<input id="before_enabled" type="button" value="<?php echo $val;?>" onclick="toggleEnabled(this, '#wp_simple_insert_before_enabled')" style="<?php echo ($val == "Enabled")?"color:green":"color:red"?>"/>
					
					<?php
					$val = $options['before_text']['enabled'];
					?>
					<input type="hidden" id="wp_simple_insert_before_enabled" name="wp_simple_insert_options[before_text][enabled]" value="<?php echo $val;?>"/>
					<?php
				break;
				case 'checkbox':
					if($args['id'] == 'wp_simple_insert_before_text_dont_show_home')
					{
						$val = (empty($options['before_text']['dont_show_home']))?0:1;
						?>
							<input type="checkbox" id="wp_simple_insert_before_text_dont_show_home" name="wp_simple_insert_options[before_text][dont_show_home]" value="<?php echo $val;?>" onclick="toggleCheckbox(this);" <?php checked($options['before_text']['dont_show_home'],1)?>/>
						<?php
					}
					else if($args['id'] == 'wp_simple_insert_before_text_dont_show_category')
					{
						$val = (empty($options['before_text']['dont_show_category']))?0:1;
						?>
							<input type="checkbox" id="wp_simple_insert_before_text_dont_show_category" name="wp_simple_insert_options[before_text][dont_show_category]" value="<?php echo $val;?>" onclick="toggleCheckbox(this);" <?php checked($options['before_text']['dont_show_category'],1)?>/>
						<?php
					}
					else if($args['id'] == 'wp_simple_insert_before_text_dont_show_archive')
					{
						$val = (empty($options['before_text']['dont_show_archive']))?0:1;
						?>
							<input type="checkbox" id="wp_simple_insert_before_text_dont_show_archive" name="wp_simple_insert_options[before_text][dont_show_archive]" value="<?php echo $val;?>" onclick="toggleCheckbox(this);" <?php checked($options['before_text']['dont_show_archive'],1)?>/>
						<?php
					}
				break;
			}
		}
			
		function settings_after_cb($args = array())
		{
			$options = get_option('wp_simple_insert_options');
			
			switch($args['type'])
			{
				case 'textarea':
					echo "<textarea id='wp_simple_insert_after_text' name='wp_simple_insert_options[after_text][text]' type='text' rows='10' cols='50'>".$options['after_text']['text']."</textarea>";
				break;
				
				case 'button':
					$val = (($options['after_text']['enabled']==0) || (empty($options['after_text']['enabled'])))?'Disabled':'Enabled';
					?>
						<input id="after_enabled" type="button" value="<?php echo $val;?>" onclick="toggleEnabled(this, '#wp_simple_insert_after_enabled')" style="<?php echo ($val == "Enabled")?"color:green":"color:red"?>"/>
					<?php
					$val = $options['after_text']['enabled'];
					?>
					<input type="hidden" id="wp_simple_insert_after_enabled" name="wp_simple_insert_options[after_text][enabled]" value="<?php echo $val;?>"/>	
				<?php
				break;
				case 'checkbox':
					if($args['id'] == 'wp_simple_insert_after_text_dont_show_home')
					{
						$val = (empty($options['after_text']['dont_show_home']))?0:1;
						?>
							<input type="checkbox" id="wp_simple_insert_after_text_dont_show_home" name="wp_simple_insert_options[after_text][dont_show_home]" value="<?php echo $val;?>" onclick="toggleCheckbox(this);" <?php checked($options['after_text']['dont_show_home'],1)?>/>
						<?php
					}
					else if($args['id'] == 'wp_simple_insert_after_text_dont_show_category')
					{
						$val = (empty($options['after_text']['dont_show_category']))?0:1;
						?>
							<input type="checkbox" id="wp_simple_insert_after_text_dont_show_category" name="wp_simple_insert_options[after_text][dont_show_category]" value="<?php echo $val;?>" onclick="toggleCheckbox(this);" <?php checked($options['after_text']['dont_show_category'],1)?>/>
						<?php
					}
					else if($args['id'] == 'wp_simple_insert_after_text_dont_show_archive')
					{
						$val = (empty($options['after_text']['dont_show_archive']))?0:1;
						?>
							<input type="checkbox" id="wp_simple_insert_after_text_dont_show_archive" name="wp_simple_insert_options[after_text][dont_show_archive]" value="<?php echo $val;?>" onclick="toggleCheckbox(this);" <?php checked($options['after_text']['dont_show_archive'],1)?>/>
						<?php
					}
				break;

			}
		}

		function settings_misc_cb($args = array())
		{
			$options = get_option('wp_simple_insert_options');
			switch($args['type'])
			{
				case 'eval-php':
		?>
			<input id='wp_simple_insert_eval_php' name='wp_simple_insert_options[eval_php]' value='<?php echo (empty($options['eval_php']))?0:1; ?>' type='checkbox' <?php checked($options['eval_php'], 1); ?> onclick="toggleCheckbox(this)"/>
			
		<?php
				break;
			}
		}
		
		function validate_options($input) {
			return $input;
		}
		
		function wp_simple_insert_section_before_cb($args = array()) {
			echo "<p>Code to be inserted before post content</p>";
		}
		
		function wp_simple_insert_section_after_cb($args = array()) {
			echo "<p>Code to be inserted after post content</p>";
		}
		
		function wp_simple_insert_section_misc_cb() {
			echo "<p>Miscellaneous settings</p>";
		}
		
		function show_options_page() {
		?>
		<script type="text/javascript">
			<!--
			function toggleEnabled(button, input)
			{
					jQuery(document).ready(function() {								
						var inp = jQuery(input);
						var btn = jQuery(button);
						
						var val = inp.attr('value');
						
						if(val == 0)
						{
							btn.attr('value','Enabled');
							inp.attr('value','1');
							btn.attr('style','color:green');
						}
						else
						{
							btn.attr('value','Disabled');
							inp.attr('value','0');
							btn.attr('style','color:red');
						}
					});
			}

			function toggleCheckbox(checkbox)
			{
				jQuery(document).ready(function() {
					
					var val = jQuery(checkbox).attr('value');
					if(val == 0) jQuery(checkbox).attr('value',1);
					else jQuery(checkbox).attr('value',0);
				});
			}
			-->
	    </script>
					
			<div class="wrap">
				
				<h2>WPSimpleInsert</h2>
				<h4>By: <a href='http://kbhaskar.in/'>Bhaskar Kandiyal</a> (<a href='mailto:bkandiyal@gmail.com'>bkandiyal@gmail.com</a>)</h4>
				<form method="post" action="options.php">
					<div id="option-sections">
						<?php settings_fields('wp_simple_insert_options'); ?>
						<?php do_settings_sections(__FILE__); ?>
						<p class="submit">
							<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes');?>"/>
						</p>
					</div>
				</form>
				
			</div>
		<?php		
		
		}
	}
}


?>
