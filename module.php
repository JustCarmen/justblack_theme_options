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

// Update database for version 1.5.2.1
// Version 1 update only if the admin has logged in. A message will be shown to tell him all settings are reset to default. Old db-entries will be removed then.
if(WT_USER_IS_ADMIN) {
	try {
		WT_DB::updateSchema(WT_ROOT.WT_MODULES_DIR.'justblack_theme_options/db_schema/', 'JB_SCHEMA_VERSION', 1);
	} catch (PDOException $ex) {
		// The schema update scripts should never fail.  If they do, there is no clean recovery.
		die($ex);
	}
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

	// Set default module options
	private function setDefault($key) {
		$JB_DEFAULT = array(
			'TREETITLE'				=> '1',
			'TITLEPOS'				=> array(
											'V' => array('size'=>'110', 'fmt'=>'px'),
											'H' => array('size'=>'52', 'fmt'=>'%', 'pos'=>'left')
										),
			'TITLESIZE'				=> '20',
			'HEADER'				=> 'default',
			'IMAGE'					=> '',
			'HEADERHEIGHT'			=> '150',
			'FLAGS'					=> '0',
			'COMPACT_MENU'			=> '0',
			'COMPACT_MENU_REPORTS'	=> '1',
			'MEDIA_MENU'			=> '0',
			'MEDIA_LINK'			=> '',
			'SUBFOLDERS'			=> '1',
			'GVIEWER'				=> '0'
		);
		return $JB_DEFAULT[$key];
	}

	// Get module options
	public function options($key) {
		$JB_OPTIONS = unserialize(get_module_setting($this->getName(), 'JB_OPTIONS'));

		$key = strtoupper($key);
		if(empty($JB_OPTIONS) || (is_array($JB_OPTIONS) && !array_key_exists($key, $JB_OPTIONS))) {
			$key == 'MENU' ? $value = $this->getMenu() : $value = $this->setDefault($key);
			return $value;
		} else {
			return $JB_OPTIONS[$key];
		}
	}

	private function getMenu() {
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

		$modules = $this->getActiveMenu(8);
		if ($modules) {
			return array_merge($menulist, $modules);
		}
		else {
			return $menulist;
		}
	}

	// get our own Compact Menu
	public function getCompactMenu() {
		global $controller, $SEARCH_SPIDER;

		if ($SEARCH_SPIDER) return null;

		$indi_xref=$controller->getSignificantIndividual()->getXref();
		$menu = new WT_Menu(WT_I18N::translate('View'), 'pedigree.php?rootid='.$indi_xref.'&amp;ged='.WT_GEDURL, 'menu-view');

		$active_reports=WT_Module::getActiveReports();
		if ($this->options('compact_menu_reports') == 1 && $active_reports) {
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
		$mainfolder = $this->options('media_link') == $MEDIA_DIRECTORY ? '' : '&amp;folder='.rawurlencode($this->options('media_link'));
		$subfolders = $this->options('subfolders') ? '&amp;subdirs=on' : '';
		$menu = new WT_Menu(WT_I18N::translate('Media'), 'medialist.php?action=filter&amp;search=no'.$mainfolder.'&amp;sortby=title&amp;'.$subfolders.'&amp;max=20&amp;columns=2', 'menu-media');

		$folders = $this->getFolderList(); $i=0;
		foreach ($folders as $key => $folder) {
			if($key !== $MEDIA_DIRECTORY) {
				$submenu = new WT_Menu(ucfirst($folder), 'medialist.php?action=filter&amp;search=no&amp;folder='.rawurlencode($key).'&amp;sortby=title&amp;'.$subfolders.'&amp;max=20&amp;columns=2', 'menu-media-'.$i);
				$menu->addSubmenu($submenu);
			}
			$i++;
		};
		return $menu;
	}

	private function getActiveMenu($sort) {
		$modules=WT_Module::getActiveMenus();
		
		if ( count($modules) > 0) {
			$fakeMenus 	= array('custom_js', 'fancy_imagebar', 'fancy_branches');

			foreach ($modules as $module) {
				$msort = in_array($module->getName(), $fakeMenus) ? 99 : $sort;
				$menulist[] = array(
					'title'		=> $module->getTitle(),
					'label'		=> $module->getName(),
					'sort' 		=> $msort,
					'function' 	=> 'getModuleMenu'
				);
				$sort++;
			}
			return $this->sortArray($menulist, 'sort');
		}
	}

	// function to check if a module menu is still active (after options are set)
	public function checkModule($menulist) {
		$lastItem = end($menulist);
		$sort = $lastItem['sort'] + 1;
		$modules=$this->getActiveMenu($sort);
		
		// delete deactivated modules from the list
		foreach ($menulist as $menu) {
			if	($menu['function'] !== 'getModuleMenu') {
				$new_list[] = $menu;
			}
			if	($modules && $menu['function'] == 'getModuleMenu' && $this->searchArray($modules, 'label', $menu['label'])) {
				$new_list[] = $menu;
			}
		}

		// add newly activated modules to the list
		if($modules) {
			foreach ($modules as $module) {
				if(!$this->searchArray($menulist, 'label', $module['label'])) {
					$new_list[] = $module;
				}
			}
		}
		return $new_list;
	}

	private function getFolderList() {
		global $MEDIA_DIRECTORY;
		$folders = WT_Query_Media::folderList();
		foreach ($folders as $key => $value) {
			if($key == null && empty($value)) {
				$folderlist[$MEDIA_DIRECTORY] = strtoupper(WT_I18N::translate(substr($MEDIA_DIRECTORY,0,-1)));
			} else {
				if (count(glob(WT_DATA_DIR.$MEDIA_DIRECTORY.$value.'*')) > 0 ) {
					$folder = array_filter(explode("/", $value));
					// only list first level folders
					if(!array_search($folder[0], $folderlist)) $folderlist[$folder[0].'/'] = WT_I18N::translate($folder[0]);
				}
			}
		}
		return $folderlist;
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

	private function upload($image) {
		// Check if we are dealing with a valid image
		if (!empty($image['name']) && preg_match('/^image\/(png|gif|jpeg)/', $image['type'])){
			$serverFileName = WT_DATA_DIR.'justblack_'.$image['name'];
			if(WT_Filter::postBool('resize') == true)	$this->resize($image['tmp_name'], $image['type'], '800', '150');
			@move_uploaded_file($image['tmp_name'], $serverFileName);
			return true;
		} else{
			return false;
		}
	}

	private function resize($imgSrc, $type, $thumbwidth, $thumbheight) {
		//getting the image dimensions
		list($width_orig, $height_orig) = @getimagesize($imgSrc);
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
			case 'image/jpeg':
				$image = @imagecreatefromjpeg($imgSrc);
				$thumb = @imagecreatetruecolor(round($new_width), $thumbheight);

				@imagecopyresampled($thumb, $image, 0, 0, 0, ($y_mid-($thumbheight/2)), $new_width, $new_height, $width_orig, $height_orig);
				imagedestroy($image);
				return imagejpeg($thumb,$imgSrc,100);
				break;
			case 'image/gif':
				$image = @imagecreatefromgif($imgSrc);
				$thumb = @imagecreatetruecolor(round($new_width), $thumbheight);

				@imagecopyresampled($thumb, $image, 0, 0, 0, ($y_mid-($thumbheight/2)), $new_width, $new_height, $width_orig, $height_orig);
				@imagecolortransparent($thumb, @imagecolorallocate($thumb, 0, 0, 0));
				imagedestroy($image);

				return imagegif($thumb,$imgSrc,100);
				break;
			case 'image/png':
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

	private function delete() {
		foreach (glob(WT_DATA_DIR.'justblack*.*') as $file) {
			@unlink($file);
		}
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
		$this->delete();
		AddToLog($this->getTitle().' reset to default values', 'config');
	}

	private function config() {

		if (WT_Filter::postBool('save') && WT_Filter::checkCsrf()) {
			$NEW_JB_OPTIONS = WT_Filter::postArray('NEW_JB_OPTIONS');
			$NEW_JB_OPTIONS['MENU'] = $this->sortArray(WT_Filter::postArray('NEW_JB_MENU'), 'sort');
			$NEW_JB_OPTIONS['IMAGE'] = WT_Filter::post('JB_IMAGE');
			$error = false;
			if($NEW_JB_OPTIONS['HEADER'] == 1 && !empty($_FILES['NEW_JB_IMAGE']['name'])) {
				if($this->upload($_FILES['NEW_JB_IMAGE'])) {
					$NEW_JB_OPTIONS['IMAGE'] = 'justblack_'.$_FILES['NEW_JB_IMAGE']['name'];
					WT_FlashMessages::addMessage(WT_I18N::translate('Your custom header image is succesfully saved.'));
				}
				else {
					WT_FlashMessages::addMessage(WT_I18N::translate('Error: You have not uploaded an image or the image you have uploaded is not a valid image! Your settings are not saved.'));
					$error = true;
				}
			} else {
				if(WT_Filter::postBool('resize') == true) {
					$file = WT_DATA_DIR.$this->options('image');
					if($this->options('image') && file_exists($file)) {
						$image = @getimagesize($file);
						$this->resize($file, $image['mime'], '800', '150');
					}
				}
			}
			if(!$error) {
				set_module_setting($this->getName(), 'JB_OPTIONS',  serialize($NEW_JB_OPTIONS));
				AddToLog($this->getTitle().' config updated', 'config');
			}
		}

		require WT_ROOT.'includes/functions/functions_edit.php';
		$controller=new WT_Controller_Page;
		$controller
			->requireAdminLogin()
			->setPageTitle(WT_I18N::translate('Options for the JustBlack theme'))
			->pageHeader();

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
				var checkbox = jQuery(checkbox).find("input[type=checkbox]");
				var field = jQuery(field)
				if(!reverse) {
					if ((checkbox).is(":checked")) field.show("slow");
					else field.hide("slow");
					checkbox.click(function(){
						if (this.checked) field.show("slow");
						else field.hide("slow");
					});
				}
				else {
					if ((checkbox).is(":checked")) field.hide("slow");
					else field.show("slow");
					checkbox.click(function(){
						if (this.checked) field.hide("slow");
						else field.show("slow");
					});
				}
			}

			toggleFields("#treetitle", "#titlepos, #titlesize");
			toggleFields("#compact_menu", "#reports");
			toggleFields("#media_menu", "#media_link, #subfolders");

			jQuery("#header option").each(function() {
				if(jQuery(this).val() == "'.$this->options('header').'") {
					jQuery(this).prop("selected", true);
				}
			});

			jQuery("#upload").hide();
			jQuery("#header select").each(function(){
				if(jQuery(this).val() == 1) {
					if(jQuery("#header-image").length > 0) jQuery("#header-image, #resize").show();
					else jQuery("#upload, #resize").show();
				}
				else jQuery("#header-image, #upload, #resize").hide();
				if(jQuery(this).val() > 0) jQuery("#header_height").show();
				else jQuery("#header_height").hide();
			});
			jQuery("#header select").change(function(){
				if(jQuery(this).val() == 1) {
					if(jQuery("#header-image").length > 0) jQuery("#header-image, #resize").show();
					else jQuery("#upload, #resize").show();
				}
				else jQuery("#header-image, #upload, #resize").hide();
				if(jQuery(this).val() > 0) jQuery("#header_height").show();
				else jQuery("#header_height").hide();
			});

			jQuery("#edit-image").click(function(){
				jQuery("#upload").toggle();
			});

			jQuery("#upload").on("change", "input[type=file]", function(){
				jQuery("#resize input[type=checkbox]").prop("checked", true);
			});

			jQuery("#compact_menu input[type=checkbox]").click(function() {
				if (jQuery("#reports input[type=checkbox]").is(":checked")) var menu_extended = jQuery(".menu_extended");
				else var menu_extended = jQuery(".menu_extended:not(.menu_reports)");

				if (this.checked) {
					jQuery(".menu_compact").insertAfter(jQuery(".menu_extended:last")).show();
					jQuery(menu_extended).appendTo(jQuery("#trashMenu")).hide();
				}
				else {
					jQuery(menu_extended).insertAfter(jQuery(".menu_compact")).show();
					jQuery(".menu_compact").appendTo(jQuery("#trashMenu")).hide();

				}
				jQuery("#sortMenu, #trashMenu").trigger("sortupdate")
			});

			jQuery("#reports input[type=checkbox]").click(function() {
				if (this.checked) jQuery(".menu_reports").appendTo(jQuery("#trashMenu")).hide();
				else jQuery(".menu_reports").insertAfter(jQuery(".menu_compact")).show();
				jQuery("#sortMenu, #trashMenu").trigger("sortupdate")
			});

			jQuery("#media_menu input[type=checkbox]").click(function() {
				if (this.checked) {
					jQuery(".menu_media").appendTo(jQuery("#sortMenu")).show();
				}
				else {
					jQuery(".menu_media").appendTo(jQuery("#trashMenu")).hide();
				}
				jQuery("#sortMenu, #trashMenu").trigger("sortupdate")
			});

			jQuery("#media_link select").each(function() {
				if(jQuery(this).val() == "'.$this->options('media_link').'") {
					jQuery(this).prop("selected", true);
				}
			});

			 jQuery("#sortMenu").sortable({
				items: "li:not(.ui-state-disabled)"
			}).disableSelection();

			//-- update the order numbers after drag-n-drop sorting is complete
			jQuery("#sortMenu").bind("sortupdate", function(event, ui) {
				jQuery("#"+jQuery(this).attr("id")+" input[name*=sort]").each(
					function (index, value) {
						if(value.value < 99) value.value = index+1;
					}
				);
				jQuery("#trashMenu input[name*=sort]").attr("value", "0");
			});
		');

		// Admin page content
		$html = '<div id="jb_options"><div id="error" style="display:none"></div><h2>'.$this->getTitle().'</h2>
				<form method="post" name="configform" action="'.$this->getConfigLink().'" enctype="multipart/form-data">
					<input type="hidden" name="save" value="1">'.WT_Filter::getCsrf().'
					<div class="block_left">
						<div id="treetitle" class="field">
							<label>'.WT_I18N::translate('Use the Family tree title in the header?').help_link('treetitle', $this->getName()).'</label>'.
							two_state_checkbox('NEW_JB_OPTIONS[TREETITLE]', $this->options('treetitle')).'
						</div>
						<div id="titlepos" class="field">
							<label>'.WT_I18N::translate('Position of the Family tree title').help_link('treetitle_position', $this->getName()).'</label>';
							$titlepos = $this->options('titlepos');
			$html .= '		<div class="block_right">
								<div class="field">
									<span>'.WT_I18N::translate('top').' </span>
									<input type="text" name="NEW_JB_OPTIONS[TITLEPOS][V][size]" size="3" value="'.$titlepos['V']['size'].'">'.
									select_edit_control('NEW_JB_OPTIONS[TITLEPOS][V][fmt]', array('px'=>'px', '%'=>'%'), null, $titlepos['V']['fmt']).'
								</div>
								<div class="field">'.
									select_edit_control('NEW_JB_OPTIONS[TITLEPOS][H][pos]', array('left' => WT_I18N::translate('left'), 'right' => WT_I18N::translate('right')), null, $titlepos['H']['pos']).'
									<input type="text" name="NEW_JB_OPTIONS[TITLEPOS][H][size]" size="3" value="'.$titlepos['H']['size'].'">'.
									select_edit_control('NEW_JB_OPTIONS[TITLEPOS][H][fmt]',  array('px'=>'px', '%'=>'%'), null, $titlepos['H']['fmt']).'
								</div>
							</div>
						</div>
						<div id="titlesize" class="field clearfloat">
							<label>'.WT_I18N::translate('Size of the Family tree title').'</label>
							<input type="text" name="NEW_JB_OPTIONS[TITLESIZE]" size="2" value="'.$this->options('titlesize').'"> px
						</div>
						<div id="header" class="field">
							<label>'.WT_I18N::translate('Use header image?').'</label>'.
							select_edit_control('NEW_JB_OPTIONS[HEADER]', array(WT_I18N::translate('Default'), WT_I18N::translate('Custom'), WT_I18N::translate('None')), null, $this->options('header')).'
						</div>';
						$file = WT_DATA_DIR.$this->options('image');
						if($this->options('image') && file_exists($file)) {
							$image = @getimagesize($file);
							$bg = file_get_contents($file);
			$html .= '		<div id="header-image" class="field">
								<input type="hidden" name="JB_IMAGE" value="'.$this->options('image').'">
								<label class="label">'.WT_I18N::translate('Current header image').' ('.$image[0].' x '.$image[1].'px)</label>

								<a class="gallery" type="'.$image['mime'].'" href="data:'.$image['mime'].';base64,'.base64_encode($bg).'">
									<span class="image">'.$this->options('image').'</span>
								</a><i id="edit-image" class="icon-edit"></i><i class="icon-delete"></i>
							</div>';
						}
			$html .= '	<div id="upload" class="field">
							<label>'.WT_I18N::translate('Upload a (new) custom header image').'</label><input type="file" name="NEW_JB_IMAGE" />
						</div>
						<div id="resize" class="field">
							<label>'.WT_I18N::translate('Resize header image (800 x 150px)').'</label>'.checkbox('resize', false).'
						</div>
						<div id="header_height" class="field">
							<label>'.WT_I18N::translate('Height of the header area').'</label>
							<input type="text" name="NEW_JB_OPTIONS[HEADERHEIGHT]" size="2" value="'.$this->options('headerheight').'" /> px
						</div>
						<div class="field">
							<label>'.WT_I18N::translate('Use flags in header bar as language menu?').help_link('flags', $this->getName()).'</label>'.
							two_state_checkbox('NEW_JB_OPTIONS[FLAGS]', $this->options('flags')).'
						</div>
						<div id="compact_menu" class="field">
							<label>'.WT_I18N::translate('Use a compact menu?').'</label>'.
							two_state_checkbox('NEW_JB_OPTIONS[COMPACT_MENU]', $this->options('compact_menu')).'
						</div>
						<div id="reports" class="field">
							<label>'.WT_I18N::translate('Include the reports topmenu in the compact \'View\' topmenu?').'</label>'.
							two_state_checkbox('NEW_JB_OPTIONS[COMPACT_MENU_REPORTS]', $this->options('compact_menu_reports')).'
						</div>
						<div id="media_menu" class="field">
							<label>'.WT_I18N::translate('Media menu in topmenu?').help_link('media_menu', $this->getName()).'</label>'.
							two_state_checkbox('NEW_JB_OPTIONS[MEDIA_MENU]', $this->options('media_menu')).'
						</div>
						<div id="media_link" class="field">
							<label>'.WT_I18N::translate('Choose a folder as default for the main menu link').help_link('media_folder', $this->getName()).'</label>'.
							select_edit_control('NEW_JB_OPTIONS[MEDIA_LINK]', $this->getFolderList(), null, $this->options('media_link')).'
						</div>
						<div id="subfolders" class="field">
							<label>'.WT_I18N::translate('Include subfolders').help_link('subfolders', $this->getName()).'</label>'.
							two_state_checkbox('NEW_JB_OPTIONS[SUBFOLDERS]', $this->options('subfolders')).'
						</div>
						<div class="field">
							<label>'.WT_I18N::translate('Use Google Docs Viewer for pdf\'s?').help_link('gviewer', $this->getName()).'</label>'.
							two_state_checkbox('NEW_JB_OPTIONS[GVIEWER]', $this->options('gviewer')).'
						</div>
						<div id="buttons">
							<input type="submit" name="update" value="'.WT_I18N::translate('Save').'" />&nbsp;&nbsp;
							<input type="reset" value="'.WT_I18N::translate('Reset').'" onclick="if (confirm(\''.WT_I18N::translate('The settings will be reset to default. Are you sure you want to do this?').'\')) window.location.href=\'module.php?mod='.$this->getName().'&amp;mod_action=admin_reset\';">
						</div>
					</div>
					<div class="block_right">
						<h3>'.WT_I18N::translate('Sort Topmenu items').help_link('sort_topmenu', $this->getName()).'</h3>';
						$menulist 	= $this->checkModule($this->options('menu'));
						foreach($menulist as $menu) {
							$menu['sort'] == 0 ? $trashMenu[] = $menu : $activeMenu[] = $menu;
						}
						$i=0;
						if (isset($activeMenu)) {
		$html .= '			<ul id="sortMenu">';
							foreach ($activeMenu as $menu) {
								if($menu['sort'] < 99) $html .= '<li class="ui-state-default'.$this->getStatus($menu['label']).'">';
								foreach ($menu as $key => $val) {
									$html .= '<input type="hidden" name="NEW_JB_MENU['.$i.']['.$key.']" value="'.$val.'"/>';
								}
								if($menu['sort'] < 99) $html .= '<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'.$menu['title'].'</li>';
								$i++;
							}
		$html .= '			</ul>';
						}
						if (isset($trashMenu)) {
		$html .= '			<ul id="trashMenu">'; // trashcan for toggling the compact menu.
							foreach ($trashMenu as $menu) {
								$html .= '<li class="ui-state-default'.$this->getStatus($menu['label']).'">';
								foreach ($menu as $key => $val) {
									$html .= '<input type="hidden" name="NEW_JB_MENU['.$i.']['.$key.']" value="'.$val.'"/>';
								}
		$html .= '				<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>'.$menu['title'].'</li>';
								$i++;
							}
		$html .= '			</ul>';
						}
		$html .= '	</div>
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