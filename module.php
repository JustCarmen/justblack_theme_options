<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2014 webtrees development team.
// Copyright (C) 2014 JustCarmen.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class justblack_theme_options_WT_Module extends WT_Module implements WT_Module_Config {
	
	public function __construct() {
		parent::__construct();
		// Load any local user translations
		if (is_dir(WT_MODULES_DIR.$this->getName().'/language')) {
			if (file_exists(WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.mo')) {
				WT_I18N::addTranslation(
					new Zend_Translate('gettext', WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.mo', WT_LOCALE)
				);
			}
			if (file_exists(WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.php')) {
				WT_I18N::addTranslation(
					new Zend_Translate('array', WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.php', WT_LOCALE)
				);
			}
			if (file_exists(WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.csv')) {
				WT_I18N::addTranslation(
					new Zend_Translate('csv', WT_MODULES_DIR.$this->getName().'/language/'.WT_LOCALE.'.csv', WT_LOCALE)
				);
			}
		}
	}
		
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module  */ WT_I18N::translate('JustBlack Theme Options');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the module */ WT_I18N::translate('Set options for the JustBlack theme within the admin interface');
	}
	
	// Get module options
	private function options($value = '') {
		$JB_OPTIONS = unserialize(get_module_setting($this->getName(), 'JB_OPTIONS'));

		if (empty($JB_OPTIONS)) {
			$JB_OPTIONS = array(
				'TREETITLE'				=> '1',
				'TITLEPOS'				=> '110px,0,0,52%',
				'TITLESIZE'				=> '20',
				'HEADER'				=> 'default',
				'HEADERIMG'				=> WT_I18N::translate('no custom header image set'),
				'HEADERHEIGHT'			=> '150',
				'FLAGS'					=> '0',
				'COMPACT_MENU'			=> '0',
				'COMPACT_MENU_REPORTS'	=> '1',
				'MEDIA_MENU'			=> '0',
				'MEDIA_MENU_LINK'		=> '',
				'GVIEWER_PDF'			=> '0',
				'MENU_ORDER'			=> $this->getMenuOrder()
			);
		};

		if($value) return($JB_OPTIONS[strtoupper($value)]);
		else return $JB_OPTIONS;
	}
	
	private function getOptionValue($key, $type) {			
		$pkey = 'JB_'.strtoupper($key);
		switch($type) {
			case('checkbox'):
				isset($_POST[$pkey]) ? $value = '1' : $value = '0';
			break;
			case('textbox'):
				is_array($_POST[$pkey]) ? $value = serialize($_POST[$pkey]) : $value = $_POST[$pkey];
			break;
			case ('selectbox'):
				$current = $this->getSettings($key);
				isset($_POST[$pkey]) ? $value = $_POST[$pkey] : $value = $current;
			break;
			case('sortable'):
				if ($key == 'menu_order') {		
					$MENU_ORDER = $this->sortArray($_POST[$pkey], 'sort');					
					$value = serialize($MENU_ORDER);
				}				
			break;			
		}		
		return $value;	
	}
	
	// Search within a multiple dimensional array	
	private function searchArray($array, $key, $value) {
		$results = array();
		if (is_array($array)) {
			if (isset($array[$key]) && $array[$key] == $value)
				$results[] = $array;	
			foreach ($array as $subarray)
				$results = array_merge($results, $this->searchArray($subarray, $key, $value));
		}	
		return $results;
	}
	
	// Sort the array according to the $key['SORT'] input.
	private function sortArray($array, $sort_by){
		foreach ($array as $pos =>  $val) {
			$tmp_array[$pos] = $val[$sort_by];
		}
		asort($tmp_array);
	   
		foreach ($tmp_array as $pos =>  $val){
			$return_array[$pos]['title'] = $array[$pos]['title'];
			$return_array[$pos]['label'] = $array[$pos]['label'];
			$return_array[$pos]['sort'] = $array[$pos]['sort'];
			$return_array[$pos]['function'] = $array[$pos]['function'];
		}
		return $return_array;
    }
	
	private function getChecked($value) {
		$value == 1 ? $checked = 'checked="checked"' : $checked = "";	
		return $checked;
	}
	
	private function getMenuOrder() {
		$menulist = array(
			array(
				'title'		=> WT_I18N::translate('View'),
				'label'		=> 'compact',
				'sort' 		=> '0',
				'function' 	=> 'getCompactMenu'
			),
			array(
				'title'		=> WT_I18N::translate('Media'),
				'label'		=> 'media',
				'sort' 		=> '0',
				'function' 	=> 'getMediaMenu'
			),
			array(			
				'title'		=> WT_I18N::translate('Home page'),
				'label'		=> 'homepage',
				'sort' 		=> '1',
				'function' 	=> 'getGedcomMenu'
			),
			array(
				'title'		=> WT_I18N::translate('My page'),
				'label'		=> 'mypage',
				'sort' 		=> '2',
				'function' 	=> 'getMyPageMenu'
			),
			array(
				'title'		=> WT_I18N::translate('Charts'),
				'label'		=> 'charts',
				'sort' 		=> '3',
				'function' 	=> 'getChartsMenu'
			),
			array(
				'title'		=> WT_I18N::translate('Lists'),
				'label'		=> 'lists',
				'sort' 		=> '4',
				'function' 	=> 'getListsMenu'
			),
			array(
				'title'		=>	WT_I18N::translate('Calendar'),
				'label'		=> 'calendar',
				'sort' 		=> '5',
				'function' 	=> 'getCalendarMenu'
			),
			array(
				'title'		=> WT_I18N::translate('Reports'),
				'label'		=> 'reports',
				'sort' 		=> '6',
				'function' 	=> 'getReportsMenu'
			),
			array(
				'title'		=> WT_I18N::translate('Search'),
				'label'		=> 'search',
				'sort' 		=> '7',
				'function' 	=> 'getSearchMenu'
			),
		);
		
		$modules=WT_Module::getActiveMenus();
		// don't list known fakemenus but put them in the database with a sort-order of 99 
		$fakeMenus 	= array('custom_js', 'fancy_imagebar', 'fancy_branches');
		$i = 9;
		foreach ($modules as $module) {
			$sort = in_array($module->getName(), $fakeMenus) ? '99' : $i;		
			$menulist[] = array(					
				'title'		=> $module->getTitle(),
				'label'		=> $module->getName(),
				'sort' 		=> $sort,
				'function' 	=> 'getModuleMenu'
			);
			$i++;	
		}
		return $menulist;
	}	
	
	// get our own Compact Menu
	public function getCompactMenu() {
		global $controller, $SEARCH_SPIDER;
		
		if ($SEARCH_SPIDER) return null;
		
		$indi_xref=$controller->getSignificantIndividual()->getXref();		
		$menu = new WT_Menu(WT_I18N::translate('View'), 'pedigree.php?rootid='.$indi_xref.'&amp;ged='.WT_GEDURL, 'menu-view');
		
		$active_reports=WT_Module::getActiveReports();
		if ($this->getSettings('compact_menu_reports') == 1 && $active_reports) {
			$submenu_items = array(
				WT_MenuBar::getChartsMenu(),
				WT_MenuBar::getListsMenu(),
				WT_MenuBar::getReportsMenu(),
				WT_MenuBar::getCalendarMenu()
			);		
		}
		else {	
			$submenu_items = array(
				WT_MenuBar::getChartsMenu(),
				WT_MenuBar::getListsMenu(),
				WT_MenuBar::getCalendarMenu()
			);
		}
		
		foreach ($submenu_items as $submenu) {
			$id = explode("-", $submenu->id);
			$new_id = implode("-", array($id[0], 'view', $id[1]));		
			$submenu->id = $new_id;	
			$submenu->label = '<span>'.$submenu->label.'</span>';	
			$menu->addSubmenu($submenu);		
		};	
		return $menu;
	}
	
	// get the media Menu as Main menu item with folders as submenu-items
	public function getMediaMenu() {
		global $controller, $SEARCH_SPIDER, $MEDIA_DIRECTORY;
		
		if ($SEARCH_SPIDER) return null;
		
		$menulink = $this->getSettings('media_menu_link');
		$menu = new WT_Menu(WT_I18N::translate('Media'), 'medialist.php?action=filter&amp;search=no&amp;folder='.rawurlencode($menulink), 'menu-media');		
		
		$folders = array_values(WT_Query_Media::folderList());
		foreach ($folders as $key => $folder) {
			$medialist = WT_Query_Media::mediaList($folder, 'exclude', 'file', '');
			if(count($medialist) > 0) {
				$name = substr($folder, 0, -1);
				if(empty($name)) $name = WT_I18N::translate('Media');
				$title = ucfirst(WT_I18N::translate($name));
				$submenu = new WT_Menu($title, 'medialist.php?action=filter&amp;search=no&amp;folder='.rawurlencode($folder), 'menu-media-folder-'.$key);
				$menu->addSubmenu($submenu);
			}
		};	
		return $menu;
	}
	
	// function to check if a module menu is still active (after options are set)
	public function checkModule($menulist) {
		$modules=WT_Module::getActiveMenus();		
		
		// delete deactivated modules from the list
		foreach ($menulist as $menu) {
			if	($menu['function'] == 'getModuleMenu') {
				if (array_key_exists($menu['label'], $modules)) {
					$new_list[] = $menu;
				}
			}							
			else {
				$new_list[] = $menu;
			}
		}	
		
		// add newly activated modules to the list
		foreach ($modules as $module) {			
			if(!$this->searchArray($menulist, 'label', $module->getName())) {
				$new_list[] = array(					
					'title'		=> $module->getTitle(),
					'label'		=> $module->getName(),
					'sort' 		=> '49', // can not be 0 (=trashmenu), can not be 99 (=fakemenu)
					'function' 	=> 'getModuleMenu'
				);	
			}
		}		
		return $new_list;
	}
	
	// set an extra class for some menuitems
	private function getStatus($label) {
		if ($label == 'homepage' || $label == 'mypage') {
		 	$status = ' ui-state-disabled';
		} elseif ($label == 'charts' || $label == 'lists' || $label == 'calendar') {
			$status = ' menu_extended';
		} elseif ($label == 'reports') {
			$status = ' menu_extended menu_reports';
		} elseif ($label == 'compact') {
			$status = ' menu_compact';
		} elseif ($label == 'media') {
			$status = ' menu_media';
		} else {
			$status = '';
		}
		return $status;
	}
	
	private function resizeHeader($imgSrc, $type, $thumbwidth, $thumbheight) {
		//getting the image dimensions 
		list($width_orig, $height_orig) = getimagesize($imgSrc);  		
		$ratio_orig = $width_orig/$height_orig;
		
		if (($width_orig > $height_orig && $width_orig < $thumbwidth) || ($height_orig > $width_orig && $height_orig < $thumbheight)) return false;
	   
		if ($thumbwidth/$thumbheight > $ratio_orig) {
		   $new_height = $thumbwidth/$ratio_orig;
		   $new_width = $thumbwidth;
		   
		} else {
		   $new_width = $thumbheight*$ratio_orig;
		   $new_height = $thumbheight;
		}
		
		$y_mid = $new_height/2;
		
		// return resized header image	
		switch ($type) {		
			case 'jpg':
			case 'jpeg':
				$image = @imagecreatefromjpeg($imgSrc);
				$thumb = @imagecreatetruecolor(round($new_width), $thumbheight);	   
				
				@imagecopyresampled($thumb, $image, 0, 0, 0, ($y_mid-($thumbheight/2)), $new_width, $new_height, $width_orig, $height_orig);
				imagedestroy($image);
				
				return imagejpeg($thumb,$imgSrc,100);
				break;
			case 'gif':
				$image = @imagecreatefromgif($imgSrc);
				$thumb = @imagecreatetruecolor(round($new_width), $thumbheight);					
				
				@imagecopyresampled($thumb, $image, 0, 0, 0, ($y_mid-($thumbheight/2)), $new_width, $new_height, $width_orig, $height_orig); 
				@imagecolortransparent($thumb, @imagecolorallocate($thumb, 0, 0, 0));
				imagedestroy($image);
				
				return imagegif($thumb,$imgSrc,100);
				break;
			case 'png':
				$image = @imagecreatefrompng($imgSrc);
				@imagealphablending($image, false);				
				
				$thumb = @imagecreatetruecolor(round($new_width), $thumbheight);	
				@imagealphablending($thumb, false);
				@imagesavealpha($thumb, true);
				
				@imagecopyresampled($thumb, $image, 0, 0, 0, ($y_mid-($thumbheight/2)), $new_width, $new_height, $width_orig, $height_orig); 				
				imagedestroy($image);
				
				return imagepng($thumb,$imgSrc,0);
				break;
		}
	}	
	
	private function uploadHeader() {
		$path = WT_STATIC_URL.'themes/justblack/'.basename(WT_CSS_URL).'/images/';
		// Check if the custom header option is set and if we are dealing with a valid image
		if ($this->getOptionValue('header', 'selectbox') == 'custom') {
			if (empty($_FILES['JB_HEADERIMG']['name']) || !preg_match('/^image\/(png|gif|jpeg)/', $_FILES['JB_HEADERIMG']['type'])){
				// suppress error message if there is already a header set
				if($this->getSettings('header') != 'custom') {
					$error = true;
					$this->addMessage($controller, 'error', WT_I18N::translate('Error: You have not uploaded an image or the image you have uploaded is not a valid image! Your settings are not saved.'));
				}
			}
			else { // process image
				$type = strtolower(substr(strrchr($_FILES['JB_HEADERIMG']['name'], '.'), 1));
				$serverFileName = $path.'custom_header.'.$type;
				if(WT_Filter::postBool('resize') == true)	$this->resizeHeader($_FILES['JB_HEADERIMG']['tmp_name'], $type, '800', '150');
				
				if (move_uploaded_file($_FILES['JB_HEADERIMG']['tmp_name'], $serverFileName)) {
					chmod($serverFileName, WT_PERM_FILE);							
					$this->addMessage($controller, 'success', WT_I18N::translate('Your custom header image is succesfully saved.'));
					
					// remove old header images from the server							
					$this->deleteCustomHeader($path, $type); //$type here is the extension to keep.
				} 
				set_module_setting($this->getName(), 'JB_HEADERIMG', $_FILES['JB_HEADERIMG']['name']);	
			}
		}
		else { // no custom header
			$this->deleteCustomHeader($path);
			WT_DB::prepare("DELETE FROM `##module_setting` WHERE setting_name = 'JB_HEADERIMG'")->execute();
		}
	}
		
	private function deleteCustomHeader($path, $kExt = '') { // $kExt = extension to keep. If not set delete all custom headers regardless extension.		
		$exts = array('png','jpg', 'gif');		
		
		foreach($exts as $ext) {
			if($ext != $kExt && file_exists($path.'custom_header.'.$ext)){
				@unlink($path.'custom_header.'.$ext);									
			}
		}	
	}
	
	private function addMessage($controller, $type, $msg) {
		if ($type == "success") $class = "ui-state-highlight";
		if ($type == "error") $class = "ui-state-error";		
		$controller->addInlineJavaScript('
			jQuery("#error").text("'.$msg.'").addClass("'.$class.'").show("normal");
			setTimeout(function() {
				jQuery("#error").hide("normal");
			}, 10000);		
		');	
	}
	
	// Extend WT_Module_Config
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_config':
			$this->config();
			break;
		case 'admin_reset':
			$this->jb_reset();
			$this->config();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Reset all settings to default
	private function jb_reset() {
		WT_DB::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'JB%'")->execute();
		AddToLog($this->getTitle().' reset to default values', 'config');
		$controller->addInlineJavascript('jQuery("option.default").prop("selected", true); jQuery(".upload").hide()');
	}
	
	private function config() {
		require WT_ROOT.'includes/functions/functions_edit.php';				
		$controller=new WT_Controller_Page;
		$controller
			->requireAdminLogin()
			->setPageTitle(WT_I18N::translate('Options for the JustBlack theme'))
			->pageHeader();
		
		if (WT_Filter::postBool('save')) {
			$NEW_JB_OPTIONS = WT_Filter::postArray('NEW_JB_OPTIONS');
			set_module_setting($this->getName(), 'JB_OPTIONS',  serialize($NEW_JB_OPTIONS));
			AddToLog($this->getTitle().' config updated', 'config');
		}
		
		$controller->addInlineJavaScript ('
			function include_css(css_file) {
				var html_doc = document.getElementsByTagName("head")[0];
				var css = document.createElement("link");
				css.setAttribute("rel", "stylesheet");
				css.setAttribute("type", "text/css");
				css.setAttribute("href", css_file);
				html_doc.appendChild(css);
			}
			include_css("'.WT_MODULES_DIR.$this->getName().'/style.css");
			
			function toggleFields(checkbox, field, reverse) {
				var checkbox = jQuery(checkbox)
				var field = jQuery(field)
				if(!reverse) {
					if ((checkbox).is(":checked")) field.show();
					else field.hide();							
					checkbox.click(function(){
						if (this.checked) field.show();
						else field.hide();															    
					});	
				}
				else {
					if ((checkbox).is(":checked")) field.hide();
					else field.show();							
					checkbox.click(function(){
						if (this.checked) field.hide();
						else field.show();															    
					});	
				}
			}						
			
			toggleFields("#treetitle", ".titlepos, .titlesize");
			toggleFields("#resize", ".headerheight", true);
			toggleFields("#compact_menu", ".reports");
			toggleFields("#media_menu", ".media_link");
								
			jQuery("#header option").each(function() {
				if(jQuery(this).val() == "'.$this->getOptionValue('header', 'selectbox').'") {
					jQuery(this).prop("selected", true);
				}						
			});
			
			jQuery("#header").each(function(){
				if(jQuery(this).val() == "custom") jQuery(".upload").show();
				else jQuery(".upload").hide();
				if(jQuery(this).val() !== "default") jQuery(".headerheight").show();
				else jQuery(".headerheight").hide();		
			});
			jQuery("#header").change(function(){
				if(jQuery(this).val() == "custom") jQuery(".upload").show();
				else jQuery(".upload").hide();
				if(jQuery(this).val() !== "default") jQuery(".headerheight").show();
				else jQuery(".headerheight").hide();						
			});
				
			jQuery("#compact_menu").click(function() {
				if (jQuery("#compact_menu_reports").is(":checked")) var menu_extended = jQuery(".menu_extended");
				else var menu_extended = jQuery(".menu_extended:not(.menu_reports)");
				
				if (this.checked) {
					jQuery(menu_extended).appendTo(jQuery("#trashMenu")).hide();
					jQuery(".menu_compact").insertAfter(jQuery(".ui-state-disabled:last")).show();
				}
				else {
					jQuery(".menu_compact").appendTo(jQuery("#trashMenu")).hide();
					jQuery(menu_extended).insertAfter(jQuery(".ui-state-disabled:last")).show();
				}
				jQuery("#sortMenu, #trashMenu").trigger("sortupdate")					
			});
			
			jQuery("#compact_menu_reports").click(function() {
				if (this.checked) jQuery(".menu_reports").appendTo(jQuery("#trashMenu")).hide();
				else jQuery(".menu_reports").insertAfter(jQuery(".menu_compact")).show();
				jQuery("#sortMenu, #trashMenu").trigger("sortupdate")					
			});
			
			jQuery("#media_menu").click(function() {						
				if (this.checked) {
					jQuery(".menu_media").appendTo(jQuery("#sortMenu")).show();
				}
				else {
					jQuery(".menu_media").appendTo(jQuery("#trashMenu")).hide();
				}
				jQuery("#sortMenu, #trashMenu").trigger("sortupdate")					
			});
			
			jQuery("#media_menu_link option").each(function() {
				if(jQuery(this).val() == "'.$this->getOptionValue('media_menu_link', 'selectbox').'") {
					jQuery(this).prop("selected", true);
				}						
			});
			
			 jQuery("#sortMenu").sortable({
				items: "li:not(.ui-state-disabled)"
			}).disableSelection();
			
			//-- update the order numbers after drag-n-drop sorting is complete
			jQuery("#sortMenu").bind("sortupdate", function(event, ui) {
				jQuery("#"+jQuery(this).attr("id")+" input[id^=menu_order_sort]").each(
					function (index, value) {
						value.value = index+1;
					}
				);
				jQuery("#trashMenu input[id^=menu_order_sort]").attr("value", "0");
			}); 
		');
		
		$JB_SETTINGS = $this->getSettings();
		$error = '';	
		
		// Admin page content
		$html = '<div id="jb_options"><div id="error" style="display:none"></div><h2>'.$this->getTitle().'</h2>
				<form method="post" name="configform" action="'.$this->getConfigLink().'">
					<input type="hidden" name="save" value="1">
					<div class="block_left">
						<div class="field">
							<label for="treetitle">'.WT_I18N::translate('Use the Family tree title in the header?').help_link('treetitle', $this->getName()).'</label>
							<input type="checkbox" id="treetitle" name="JB_TREETITLE" '.$this->getChecked($JB_SETTINGS['TREETITLE']).' />
						</div>
						<div class="field titlepos">
							<label for="titlepos">'.WT_I18N::translate('Position of the Family tree title').help_link('treetitle_position', $this->getName()).'</label>
							<input type="textbox" id="titlepos" name="JB_TITLEPOS" value="'.$JB_SETTINGS['TITLEPOS'].'" />								
						</div>
						<div class="field titlesize">
							<label for="titlesize">'.WT_I18N::translate('Size of the Family tree title').'</label>
							<input type="textbox" id="titlesize" name="JB_TITLESIZE" size="2" value="'.$JB_SETTINGS['TITLESIZE'].'" /> px								
						</div>
						<div class="field">
							<label for="header">'.WT_I18N::translate('Use header image?').'</label>
							<select id="header" name="JB_HEADER">
								<option class="default" value="default">'.WT_I18N::translate('Default').'</option>
								<option value="custom">'.WT_I18N::translate('Custom').'</option>
								<option value="none">'.WT_I18N::translate('None').'</option>
							</select>
						</div>
						<div class="field upload title">
							<label for="current_headerimg">'.WT_I18N::translate('Current custom header-image').'</label>';
							$ext = strtolower(substr(strrchr($JB_SETTINGS['HEADERIMG'], '.'), 1));
							if(file_exists(WT_STATIC_URL.'themes/justblack/css/images/custom_header.'.$ext)){
									$ext == 'jpg' ? $type = 'image/jpeg' : $type = 'image/'.$ext;
									$html .= '	<a class="gallery" type="'.$type.'" href="'.WT_STATIC_URL.'themes/justblack/css/images/custom_header.'.$ext.'">
													<span class="current_headerimg">'.$JB_SETTINGS['HEADERIMG'].'</span>
												</a>';																			
							}	
							else {
									$html .= '	<span class="current_headerimg">'.$JB_SETTINGS['HEADERIMG'].'</span>';
							}
			$html .= '	</div>
						<div class="field upload">
							<label for="headerimg">'.WT_I18N::translate('Upload a (new) custom header image').'</label>
							<input type="file" id="headerimg" name="JB_HEADERIMG" /><br/>'.
							checkbox('resize', false, 'id="resize"').'<label for="resize">'.WT_I18N::translate('Resize (800x150px)').'</label>
						</div>
						<div class="field headerheight">
							<label for="headerheight">'.WT_I18N::translate('Height of the header area').'</label>
							<input type="textbox" id="headerheight" name="JB_HEADERHEIGHT" size="2" value="'.$JB_SETTINGS['HEADERHEIGHT'].'" /> px
						</div>
						<div class="field">
							<label for="flags">'.WT_I18N::translate('Use flags in header bar as language menu?').help_link('flags', $this->getName()).'</label>
							<input type="checkbox" id="flags" name="JB_FLAGS" '.$this->getChecked($JB_SETTINGS['FLAGS']).' />
						</div>
						<div class="field">
							<label for="compact_menu">'.WT_I18N::translate('Use a compact menu?').'</label>
							<input type="checkbox" id="compact_menu" name="JB_COMPACT_MENU" '.$this->getChecked($JB_SETTINGS['COMPACT_MENU']).' />
						</div>
						<div class="field reports">
							<label for="compact_menu_reports">'.WT_I18N::translate('Include the reports topmenu in the compact \'View\' topmenu?').'</label>
							<input type="checkbox" id="compact_menu_reports" name="JB_COMPACT_MENU_REPORTS" '.$this->getChecked($JB_SETTINGS['COMPACT_MENU_REPORTS']).' />
						</div>	
						<div class="field">
							<label for="media_menu">'.WT_I18N::translate('Media menu in topmenu?').help_link('media_menu', $this->getName()).'</label>
							<input type="checkbox" id="media_menu" name="JB_MEDIA_MENU" '.$this->getChecked($JB_SETTINGS['MEDIA_MENU']).' />
						</div>	
						<div class="field media_link">								
							<label for="media_menu_link">'.WT_I18N::translate('Choose a folder as default for the main menu link').help_link('media_folder', $this->getName()).'</label>								
							<select id="media_menu_link" name="JB_MEDIA_MENU_LINK">';
							$folders = WT_Query_Media::folderList();
								foreach ($folders as $folder) {
									if(empty($folder)) $folder = WT_I18N::translate('Media').'/';
			$html .=				'<option value="'.$folder.'">'.ucfirst($folder).'</option>';
								}
			$html .=		'</select>
						</div>	
						<div class="field">
							<label for="gviewer_pdf">'.WT_I18N::translate('Use Google Docs Viewer for pdf\'s?').help_link('gviewer', $this->getName()).'</label>
							<input type="checkbox" id="gviewer_pdf" name="JB_GVIEWER_PDF" '.$this->getChecked($JB_SETTINGS['GVIEWER_PDF']).' />
						</div>														
						<div id="buttons">
							<input type="submit" name="update" value="'.WT_I18N::translate('Save').'" />&nbsp;&nbsp;
							<input type="submit" name="reset" value="'.WT_I18N::translate('Reset').'" />
						</div>
					</div>
					<div class="block_right">';							
			$html .= '	<div class="block_left">
							<h3>'.WT_I18N::translate('Sort Topmenu items').help_link('sort_topmenu', $this->getName()).'</h3>';
							$menulist 	= $this->checkModule($JB_SETTINGS['MENU_ORDER']);
							foreach($menulist as $menu) {																		
								if($menu['sort'] == 0) $trashMenu[] = $menu;
								elseif ($menu['sort'] == 99) $fakeMenu[] = $menu;
								else $activeMenu[] = $menu;
							}
							$i=1;
							if (isset($activeMenu)) {
								$html .= '
								<ul id="sortMenu">';										
									foreach ($activeMenu as $menu) {
										$html .= '<li class="ui-state-default'.$this->getStatus($menu['label']).'">';
										foreach ($menu as $key => $val) {
											$html .= '<input type="hidden" id="menu_order_'.$key.'_'.$i.'" name="JB_MENU_ORDER['.$i.']['.$key.']" value="'.$val.'"/>';
										}
										$html .= '<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'.$menu['title'].'</li>';
										$i++;
									}								
			$html .= '			</ul>';
							}
							if (isset($trashMenu)) {
			$html .= '			<ul id="trashMenu">'; // trashcan for toggling the compact menu.
									foreach ($trashMenu as $menu) {
										$html .= '<li class="ui-state-default'.$this->getStatus($menu['label']).'">';
										foreach ($menu as $key => $val) {
											$html .= '<input type="hidden" id="menu_order_'.$key.'_'.$i.'" name="JB_MENU_ORDER['.$i.']['.$key.']" value="'.$val.'"/>';
										}
										$html .= '<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'.$menu['title'].'</li>';										
										$i++;
									}			
			$html .= '			</ul>';
							}
							if (isset($fakeMenu)) {
			$html .= '			<div id="fakeMenu">';
									foreach ($fakeMenu as $menu) {
										foreach ($menu as $key => $val) {
											$html .= '<input type="hidden" id="menu_order_'.$key.'_'.$i.'" name="JB_MENU_ORDER['.$i.']['.$key.']" value="'.$val.'"/>';
										}									
										$i++;
									}			
			$html .= '			</div>';
							}
			$html .= '</div>				
				</form>
			</div>';               
			
		// output
		ob_start();			
		$html .= ob_get_clean();
		echo $html;	
	}
	
	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}		
}