<?php
namespace Fisharebest\Webtrees;

/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * Copyright (C) 2015 JustCarmen
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

use Zend_Translate;

class JustBlackThemeOptionsModule extends Module implements ModuleConfigInterface {

	public function __construct() {
		parent::__construct('justblack_theme_options');
		
		// update the database if neccessary
		self::updateSchema();
		
		// Load any local user translations
		if (is_dir(WT_MODULES_DIR . $this->getName() . '/language')) {
			if (file_exists(WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.mo')) {
				I18N::addTranslation(
					new Zend_Translate('gettext', WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.mo', WT_LOCALE)
				);
			}
			if (file_exists(WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.php')) {
				I18N::addTranslation(
					new Zend_Translate('array', WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.php', WT_LOCALE)
				);
			}
			if (file_exists(WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.csv')) {
				I18N::addTranslation(
					new Zend_Translate('csv', WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.csv', WT_LOCALE)
				);
			}
		}
	}

	// Extend Module
	public function getTitle() {
		return /* I18N: Name of a module  */ I18N::translate('JustBlack Theme Options');
	}

	// Extend Module
	public function getDescription() {
		return /* I18N: Description of the module */ I18N::translate('Set options for the JustBlack theme within the admin interface');
	}

	// Set default module options
	private function setDefault($key) {
		$JB_DEFAULT = array(
			'TREETITLE'				 => '1',
			'TITLEPOS'				 => array(
				'V'	 => array('size' => '110', 'fmt' => 'px', 'pos' => 'top'),
				'H'	 => array('size' => '52', 'fmt' => '%', 'pos' => 'left')
			),
			'TITLESIZE'				 => '20',
			'HEADER'				 => 'default',
			'IMAGE'					 => '',
			'HEADERHEIGHT'			 => '150',
			'FLAGS'					 => '0',
			'COMPACT_MENU'			 => '0',
			'COMPACT_MENU_REPORTS'	 => '1',
			'MEDIA_MENU'			 => '0',
			'MEDIA_LINK'			 => '',
			'SHOW_SUBFOLDERS'		 => '1',
			'SQUARE_THUMBS'			 => '1'
		);
		return $JB_DEFAULT[$key];
	}

	// Get module options
	public function options($key) {
		if ($key === 'css') {
			return WT_MODULES_DIR . $this->getName() . '/css/style.css';
		} elseif ($key === 'mediafolders') {
			return $this->listMediaFolders();
		} else {
			$JB_OPTIONS = unserialize($this->getSetting('JB_OPTIONS'));
			$key = strtoupper($key);
			if (empty($JB_OPTIONS) || (is_array($JB_OPTIONS) && !array_key_exists($key, $JB_OPTIONS))) {
				return $key === 'MENU' ? $this->getDefaultMenu() : $this->setDefault($key);
			} else {
				return $key === 'MENU' ? $this->menuJustBlack($JB_OPTIONS['MENU']) : $JB_OPTIONS[$key];
			}
		}
	}

	private function getDefaultMenu() {
		$menulist = array(
			'compact'	 => array(
				'title'		 => I18N::translate('View'),
				'label'		 => 'compact',
				'sort'		 => '0',
				'function'	 => 'menuCompact'
			),
			'media'		 => array(
				'title'		 => I18N::translate('Media'),
				'label'		 => 'media',
				'sort'		 => '0',
				'function'	 => 'menuMedia'
			),
			'homepage'	 => array(
				'title'		 => I18N::translate('Home page'),
				'label'		 => 'homepage',
				'sort'		 => '1',
				'function'	 => 'menuHomePage'
			),
			'charts'	 => array(
				'title'		 => I18N::translate('Charts'),
				'label'		 => 'charts',
				'sort'		 => '3',
				'function'	 => 'menuChart'
			),
			'lists'		 => array(
				'title'		 => I18N::translate('Lists'),
				'label'		 => 'lists',
				'sort'		 => '4',
				'function'	 => 'menuLists'
			),
			'calendar'	 => array(
				'title'		 => I18N::translate('Calendar'),
				'label'		 => 'calendar',
				'sort'		 => '5',
				'function'	 => 'menuCalendar'
			),
			'reports'	 => array(
				'title'		 => I18N::translate('Reports'),
				'label'		 => 'reports',
				'sort'		 => '6',
				'function'	 => 'menuReports'
			),
			'search'	 => array(
				'title'		 => I18N::translate('Search'),
				'label'		 => 'search',
				'sort'		 => '7',
				'function'	 => 'menuSearch'
			),
		);
		return $this->menuJustBlack($menulist);
	}

	public function menuJustBlack($menulist) {
		$modules = Module::getActiveMenus();
		// add newly activated modules to the menu
		$sort = count($menulist) + 1;
		foreach ($modules as $module) {
			if ($module->getMenu() && !array_key_exists($module->getName(), $menulist)) {
				$menulist[$module->getName()] = array(
					'title'		 => $module->getTitle(),
					'label'		 => $module->getName(),
					'sort'		 => $sort++,
					'function'	 => 'menuModules'
				);
			}
		}
		// delete deactivated modules from the menu
		foreach ($menulist as $label => $menu) {
			if ($menu['function'] === 'menuModules' && !array_key_exists($label, $modules)) {
				unset($menulist[$label]);
			}
		}
		return $menulist;
	}

	private function listMenuJustBlack($menulist) {
		$html = '';
		foreach ($menulist as $label => $menu) {
			$html .= '<li class="list-group-item' . $this->getStatus($label) . '">';
			foreach ($menu as $key => $val) {
				$html .= '<input type="hidden" name="NEW_JB_MENU[' . $label . '][' . $key . ']" value="' . $val . '"/>';
			}
			$html .= $menu['title'] . '</li>';
		}
		return $html;
	}

	private function listMediaFolders() {
		global $WT_TREE;

		$MEDIA_DIRECTORY = $WT_TREE->getPreference('MEDIA_DIRECTORY');
		$folders = QueryMedia::folderList();

		foreach ($folders as $key => $value) {
			if ($key == null && empty($value)) {
				$folderlist[$MEDIA_DIRECTORY] = strtoupper(I18N::translate(substr($MEDIA_DIRECTORY, 0, -1)));
			} else {
				if (count(glob(WT_DATA_DIR . $MEDIA_DIRECTORY . $value . '*')) > 0) {
					$folder = array_filter(explode("/", $value));
					// only list first level folders
					if (!empty($folder) && !array_search($folder[0], $folderlist)) {
						$folderlist[$folder[0] . '/'] = I18N::translate($folder[0]);
					}
				}
			}
		}
		return $folderlist;
	}

	// Sort the array according to the $key['SORT'] input.
	private function sortArray($array, $sort_by) {
		foreach ($array as $pos => $val) {
			$tmp_array[$pos] = $val[$sort_by];
		}
		asort($tmp_array);

		$return_array = array();
		foreach ($tmp_array as $pos => $val) {
			$return_array[$pos]['title'] = $array[$pos]['title'];
			$return_array[$pos]['label'] = $array[$pos]['label'];
			$return_array[$pos]['sort'] = $array[$pos]['sort'];
			$return_array[$pos]['function'] = $array[$pos]['function'];
		}
		return $return_array;
	}

	// set an extra class for some menuitems
	private function getStatus($label) {
		if ($label == 'homepage') {
			$status = ' disabled';
		} elseif ($label == 'charts' || $label == 'lists' || $label == 'calendar') {
			$status = ' menu-extended';
		} elseif ($label == 'reports') {
			$status = ' menu-extended menu-reports';
		} elseif ($label == 'compact') {
			$status = ' menu-compact';
		} elseif ($label == 'media') {
			$status = ' menu-media';
		} else {
			$status = '';
		}
		return $status;
	}

	private function upload($image) {
		// Check if we are dealing with a valid image
		if (!empty($image['name']) && preg_match('/^image\/(png|gif|jpeg)/', $image['type'])) {
			$serverFileName = WT_DATA_DIR . 'justblack_' . $image['name'];
			if (Filter::postBool('resize') == true) {
				$this->resize($image['tmp_name'], $image['type'], '800', '150');
			}
			$this->deleteImage(); // delete the old image from the server.
			move_uploaded_file($image['tmp_name'], $serverFileName);
			return true;
		} else {
			return false;
		}
	}

	private function resize($imgSrc, $type, $thumbwidth, $thumbheight) {
		//getting the image dimensions
		list($width_orig, $height_orig) = @getimagesize($imgSrc);
		$ratio_orig = $width_orig / $height_orig;

		if (($width_orig > $height_orig && $width_orig < $thumbwidth) || ($height_orig > $width_orig && $height_orig < $thumbheight)) {
			return false;
		}

		if ($thumbwidth / $thumbheight > $ratio_orig) {
			$new_height = $thumbwidth / $ratio_orig;
			$new_width = $thumbwidth;
		} else {
			$new_width = $thumbheight * $ratio_orig;
			$new_height = $thumbheight;
		}

		$y_mid = $new_height / 2;

		// return resized header image
		switch ($type) {
			case 'image/jpeg':
				$image = @imagecreatefromjpeg($imgSrc);
				$thumb = @imagecreatetruecolor(round($new_width), $thumbheight);

				@imagecopyresampled($thumb, $image, 0, 0, 0, ($y_mid - ($thumbheight / 2)), $new_width, $new_height, $width_orig, $height_orig);
				@imagedestroy($image);
				return @imagejpeg($thumb, $imgSrc, 100);
			case 'image/gif':
				$image = @imagecreatefromgif($imgSrc);
				$thumb = @imagecreatetruecolor(round($new_width), $thumbheight);

				@imagecopyresampled($thumb, $image, 0, 0, 0, ($y_mid - ($thumbheight / 2)), $new_width, $new_height, $width_orig, $height_orig);
				@imagecolortransparent($thumb, @imagecolorallocate($thumb, 0, 0, 0));
				@imagedestroy($image);

				return @imagegif($thumb, $imgSrc, 100);
			case 'image/png':
				$image = @imagecreatefrompng($imgSrc);
				@imagealphablending($image, false);

				$thumb = @imagecreatetruecolor(round($new_width), $thumbheight);
				@imagealphablending($thumb, false);
				@imagesavealpha($thumb, true);

				@imagecopyresampled($thumb, $image, 0, 0, 0, ($y_mid - ($thumbheight / 2)), $new_width, $new_height, $width_orig, $height_orig);
				@imagedestroy($image);

				return @imagepng($thumb, $imgSrc, 0);
		}
	}

	private function deleteImage() {
		foreach (glob(WT_DATA_DIR . 'justblack*.*') as $file) {
			@unlink($file);
		}
	}

	// Extend ModuleConfigInterface
	public function modAction($mod_action) {
		switch ($mod_action) {
		case 'admin_config':
			$this->config();
			break;
		case 'admin_reset':
			$this->deleteImage();
			$this->resetAll();
			$this->config();
			break;
		case 'delete_image':
			$this->deleteImage();
			break;
		default:
			default:
			http_response_code(404);
			break;
		}
	}

	// Reset all settings to default
	private function resetAll() {
		Database::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'JB%'")->execute();
		Log::addConfigurationLog($this->getTitle() . ' reset to default values');
	}

	// Radio buttons
	private function radioButtons($name, $selected) {
		$values = array(
			0	 => I18N::translate('no'),
			1	 => I18N::translate('yes'),
		);

		return radio_buttons($name, $values, $selected, 'class="radio-inline"');
	}

	private function config() {

		if (Filter::postBool('save') && Filter::checkCsrf()) {
			$NEW_JB_OPTIONS = Filter::postArray('NEW_JB_OPTIONS');
			$NEW_JB_OPTIONS['MENU'] = $this->sortArray(Filter::postArray('NEW_JB_MENU'), 'sort');
			$NEW_JB_OPTIONS['IMAGE'] = Filter::post('JB_IMAGE');
			$error = false;
			if ($NEW_JB_OPTIONS['HEADER'] == 1) {
				if (!empty($_FILES['NEW_JB_IMAGE']['name'])) {
					if ($this->upload($_FILES['NEW_JB_IMAGE'])) {
						$NEW_JB_OPTIONS['IMAGE'] = 'justblack_' . $_FILES['NEW_JB_IMAGE']['name'];
					} else {
						FlashMessages::addMessage(I18N::translate('Error: The image you have uploaded is not a valid image! Your settings are not saved.'), 'warning');
						$error = true;
					}
				}
				if (Filter::postBool('resize') == true) {
					$file = WT_DATA_DIR . $this->options('image');
					if ($this->options('image') && file_exists($file)) {
						$image = getimagesize($file);
						$this->resize($file, $image['mime'], '800', '150');
					}
				}
			}
			if (!$error) {
				$this->setSetting('JB_OPTIONS', serialize($NEW_JB_OPTIONS));
				FlashMessages::addMessage(I18N::translate('Your settings are successfully saved.'), 'success');
				Log::addConfigurationLog($this->getTitle() . ' config updated');
			}
		}

		$controller = new PageController;
		$controller
			->restrictAccess(Auth::isAdmin())
			->setPageTitle(I18N::translate('Options for the JustBlack theme'))
			->pageHeader();

		$controller->addInlineJavaScript('
			function include_css(css_file) {
				var html_doc = document.getElementsByTagName("head")[0];
				var css = document.createElement("link");
				css.setAttribute("rel", "stylesheet");
				css.setAttribute("type", "text/css");
				css.setAttribute("href", css_file);
				html_doc.appendChild(css);
			}
			include_css("' . WT_MODULES_DIR . $this->getName() . '/css/admin.css");

			function toggleFields(id, target) {
				var selected = jQuery(id).find("input[type=radio]:checked");
				var field = jQuery(target)
				if (selected.val() == "1") {
					field.show();
				} else {
					field.hide();
				}
				jQuery(id).on("change", "input[type=radio]", function(){
					if (jQuery(this).val() == "1") {
						field.show();
					} else {
						field.hide();
					}
				});
			}

			toggleFields("#tree-title", "#title-pos, #title-size");
			toggleFields("#compact-menu", "#reports");
			toggleFields("#media-menu", "#medialist, #subfolders");


			jQuery("#header-image option").each(function() {
				if(jQuery(this).val() == "' . $this->options('header') . '") {
					jQuery(this).prop("selected", true);
				}
			});

			jQuery("#upload-image").hide();
			jQuery("#header-image select").each(function(){
				if(jQuery(this).val() == 1) {
					jQuery("#upload-image, #resize-image").show();
				} else {
					jQuery("#upload-image, #resize-image").hide();
					if(jQuery(this).val() > 0) {
						jQuery("#header-height").show();
					} else {
						jQuery("#header-height").hide();
					}
				}
			});

			jQuery("#header-image select").change(function(){
				if(jQuery(this).val() == 1) {
					jQuery("#upload-image, #resize-image").show();
				} else {
					jQuery("#upload-image, #resize-image").hide();
				}
				if(jQuery(this).val() > 0) {
					jQuery("#header-height").show();
				} else {
					jQuery("#header-height").hide();
				}
			});

			jQuery("#upload-image").on("click", "#file-input-btn, #file-input-text", function(){
				jQuery("input[id=file-input]").trigger("click");
			});

			jQuery("input[id=file-input]").change(function() {
				jQuery("#file-input-text").val(jQuery(this).val());
				jQuery("#file-delete").show();
			});

			if(!jQuery.trim(jQuery("#file-input-text").val()).length) {
				jQuery("#file-delete").hide();
			} else {
				jQuery("#file-delete").show();
			}

			jQuery("#file-delete").click(function(){
				jQuery.get("module.php?mod=' . $this->getName() . '&mod_action=delete_image", function(){
					jQuery("input[id=file-input-text]").attr("value", "");
					jQuery("#header-image select").val(0);
					jQuery("#file-delete").hide();
				});
			});

			jQuery("#compact-menu").on("change", "input[type=radio]", function() {
				var reports = jQuery("#reports").find("input[type=radio]:checked");
				if (reports.val() == "1") {
					var menuExtended = jQuery(".menu-extended");
				} else {
					var menuExtended = jQuery(".menu-extended:not(.menu-reports)");
				}

				if (jQuery(this).val() == "1") {
					jQuery(".menu-compact").insertAfter(jQuery(".menu-extended:last"));
					jQuery(menuExtended).appendTo(jQuery("#trash-menu"));
				} else {
					jQuery(menuExtended).insertAfter(jQuery(".menu-compact"));
					jQuery(".menu-compact").appendTo(jQuery("#trash-menu"));
				}
				jQuery("#sort-menu, #trash-menu").trigger("sortupdate")
			});

			jQuery("#reports").on("change", "input[type=radio]", function() {
				if (jQuery(this).val() == "1") {
					jQuery(".menu-reports").appendTo(jQuery("#trash-menu"));
				} else {
					jQuery(".menu-reports").insertAfter(jQuery(".menu-compact"));
				}
				jQuery("#sort-menu, #trash-menu").trigger("sortupdate")
			});

			jQuery("#media-menu").on("change", "input[type=radio]", function() {
				if (jQuery(this).val() == "1") {
					jQuery(".menu-media").appendTo(jQuery("#sort-menu"));
				} else {
					jQuery(".menu-media").appendTo(jQuery("#trash-menu"));
				}
				jQuery("#sort-menu, #trash-menu").trigger("sortupdate")
			});

			jQuery("#medialist select").each(function() {
				if(jQuery(this).val() == "' . $this->options('media_link') . '") {
					jQuery(this).prop("selected", true);
				}
			});

			 jQuery("#sort-menu").sortable({
				items: "li:not(.disabled)",
				cursor: "move",
				update: function(event, ui) {
					jQuery("#sort-menu, #trash-menu").trigger("sortupdate")
				}
			});
			jQuery("#sort-menu li, #trash-menu li").not(".disabled").css("cursor", "move");

			//-- update the order numbers after drag-n-drop sorting is complete
			jQuery("#sort-menu").bind("sortupdate", function(event, ui) {
				jQuery("#"+jQuery(this).attr("id")+" input[name*=sort]").each(
					function (index, element) {
						element.value = index + 1;
					}
				);
				jQuery("#trash-menu input[name*=sort]").attr("value", "0");
			});
		');
		?>

		<!-- ADMIN PAGE CONTENT -->
		<ol class="breadcrumb small">
			<li><a href="admin.php"><?php echo I18N::translate('Control panel'); ?></a></li>
			<li><a href="admin_modules.php"><?php echo I18N::translate('Module administration'); ?></a></li>
			<li class="active"><?php echo $this->getTitle(); ?></li>
		</ol>
		<h2><?php echo $this->getTitle(); ?></h2>
		<form action="<?php echo $this->getConfigLink(); ?>" enctype="multipart/form-data" name="configform" method="post" class="form-horizontal">
			<input type="hidden" value="1" name="save">
			<?php echo Filter::getCsrf(); ?>
			<input type="hidden" value="0" name="remove-image">
			<div id="accordion" class="panel-group">
				<div id="panel1" class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a href="#collapseOne" data-target="#collapseOne" data-toggle="collapse"><?php echo I18N::translate('Options'); ?></a>
						</h4>
					</div>
					<div class="panel-collapse collapse in" id="collapseOne">
						<div class="panel-body">
							<!-- TREE TITLE -->
							<div id="tree-title" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Use the Family tree title in the header?'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radioButtons('NEW_JB_OPTIONS[TREETITLE]', $this->options('treetitle')); ?>
									<p class="small text-muted"><?php echo I18N::translate('Choose “no” if you have used the Family tree title in your custom header image. Otherwise leave value to “yes”.'); ?></p>
								</div>
							</div>
							<!-- TITLE POSITION -->
							<?php $titlepos = $this->options('titlepos'); ?>
							<div id="title-pos" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Position of the Family tree title'); ?>
								</label>
								<div class="col-sm-8">
									<div class="row">
										<div class="col-xs-2">
											<?php echo select_edit_control('NEW_JB_OPTIONS[TITLEPOS][V][pos]', array('top' => I18N::translate('top'), 'bottom' => I18N::translate('bottom')), null, $titlepos['V']['pos'], 'class="form-control"'); ?>
										</div>
										<div class="col-xs-2">
											<input
												type="text"
												value="<?php echo $titlepos['V']['size']; ?>"
												size="3"
												name="NEW_JB_OPTIONS[TITLEPOS][V][size]"
												class="form-control"
												>
										</div>
										<div class="col-xs-2">
											<?php echo select_edit_control('NEW_JB_OPTIONS[TITLEPOS][V][fmt]', array('px' => 'px', '%' => '%'), null, $titlepos['V']['fmt'], 'class="form-control"'); ?>
										</div>
									</div>
									<div class="row">
										<div class="col-xs-2">
											<?php echo select_edit_control('NEW_JB_OPTIONS[TITLEPOS][H][pos]', array('left' => I18N::translate('left'), 'right' => I18N::translate('right')), null, $titlepos['H']['pos'], 'class="form-control"'); ?>
										</div>
										<div class="col-xs-2">
											<input
												type="text"
												value="<?php echo $titlepos['H']['size']; ?>"
												size="3"
												name="NEW_JB_OPTIONS[TITLEPOS][H][size]"
												class="form-control"
												>
										</div>
										<div class="col-xs-2">
											<?php echo select_edit_control('NEW_JB_OPTIONS[TITLEPOS][H][fmt]', array('px' => 'px', '%' => '%'), null, $titlepos['H']['fmt'], 'class="form-control"'); ?>
										</div>
									</div>
									<p class="small text-muted"><?php echo I18N::translate('Here you can set the location of the family tree title. Adjust the values to your needs. If you want the tree title appear in the header image, the correct values depend on the length of the tree title. The position is the absolute position of the title, relative to the header area. For example: choose “Top: 0px; Left: 0px”  for the top left corner of the header area or “Top: 50%%; Right: 10px” to place the title at the right side in the middle of the header area with a 10px margin.'); ?></p>
								</div>
							</div>
							<!-- TITLE SIZE -->
							<div id="title-size" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Size of the Family tree title'); ?>
								</label>
								<div class="col-sm-2">
									<div class="input-group">
										<input
											type="text"
											value="<?php echo $this->options('titlesize'); ?>"
											size="2"
											name="NEW_JB_OPTIONS[TITLESIZE]"
											class="form-control"
											>
										<span class="input-group-addon">px</span>
									</div>
								</div>
							</div>
							<!-- HEADER IMAGE -->
							<div id="header-image" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Use header image?'); ?>
								</label>
								<div class="col-sm-2">
									<?php echo select_edit_control('NEW_JB_OPTIONS[HEADER]', array(I18N::translate('Default'), I18N::translate('Custom'), I18N::translate('None')), null, $this->options('header'), 'class="form-control"'); ?>
								</div>
							</div>
							<!-- IMAGE UPLOAD FIELD -->
							<div id="upload-image" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Upload a custom header image'); ?>
								</label>
								<div class="col-sm-4">
									<input
										id="file-input"
										name="NEW_JB_IMAGE"
										type="file"
										class="sr-only"
										>
									<div class="input-group">
										<input
											id="file-input-text"
											class="form-control"
											name="JB_IMAGE"
											type="text"
											value="<?php echo $this->options('image'); ?>"
											readonly
											onfocus="this.blur()"
											>
										<span id="file-input-btn" class="btn btn-default input-group-addon">
											<?php echo I18N::translate('Browse'); ?>
										</span>
										<span id="file-delete" class="btn input-group-addon">
											<i class="fa fa-trash"></i>
										</span>
									</div>
								</div>
							</div>
							<!-- RESIZE IMAGE -->
							<div id="resize-image" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Resize image (800 x 150px)'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radioButtons('resize', '0'); ?>
								</div>
							</div>
							<!-- HEADER HEIGHT -->
							<div id="header-height" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Height of the header area'); ?>
								</label>
								<div class="col-sm-2">
									<div class="input-group">
										<input
											type="text"
											value="<?php echo $this->options('headerheight'); ?>"
											size="2"
											name="NEW_JB_OPTIONS[HEADERHEIGHT]"
											class="form-control"
											>
										<span class="input-group-addon">px</span>
									</div>

								</div>
							</div>
							<!-- FLAGS -->
							<div id="flags" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Use flags in header bar as language menu?'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radioButtons('NEW_JB_OPTIONS[FLAGS]', $this->options('flags')); ?>
									<p class="small text-muted"><?php echo I18N::translate('You can use flags in the bar above the topmenu bar in the header. These flags replaces the default dropdown menu. We advice you not to use this option if you have more then ten languages installed. You can remove unused languages from the folder languages in your webtrees installation.'); ?></p>
								</div>
							</div>
							<!-- COMPACT MENU -->
							<div id="compact-menu" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Use a compact menu?'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radioButtons('NEW_JB_OPTIONS[COMPACT_MENU]', $this->options('compact_menu')); ?>
									<p class="small text-muted"><?php echo I18N::translate('In the compact “View”-menu the menus for Charts, Lists, Calendar and (optionally) Reports will be merged together.'); ?></p>
								</div>
							</div>
							<!-- REPORTS -->
							<div id="reports" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Include the reports topmenu in the compact \'View\' topmenu?'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radioButtons('NEW_JB_OPTIONS[COMPACT_MENU_REPORTS]', $this->options('compact_menu_reports')); ?>
								</div>
							</div>
							<!-- MEDIA MENU -->
							<?php $folders = $this->options('mediafolders'); ?>
							<div id="media-menu" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Media menu in topmenu'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radioButtons('NEW_JB_OPTIONS[MEDIA_MENU]', $this->options('media_menu')); ?>
									<p class="small text-muted"><?php echo I18N::translate('If this option is set the media menu will be moved to the topmenu.'); ?></p>
									<?php if (count($folders) > 1): // add extra information about subfolders ?>
									<p class="small text-muted"><?php echo I18N::translate('The names of first level media folders from your media folder on the server will be used as submenu items of the new media menu. Warning: these submenu items are not translated automatically. Use a custom language file to translate your menu items. Read the webrees WIKI for more information.'); ?></p>
									<?php endif; ?>
								</div>
							</div>
							<?php if (count($folders) > 1): // only show these options if we have subfolders ?>
							<!-- MEDIA FOLDER LIST -->
							<div id="medialist" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Choose a folder as default for the main menu link'); ?>
								</label>
								<div class="col-sm-2">
									<?php echo select_edit_control('NEW_JB_OPTIONS[MEDIA_LINK]', $folders, null, $this->options('media_link'), 'class="form-control"'); ?>
								</div>
								<div class="col-sm-8"><p class="small text-muted"><?php echo I18N::translate('The media folder you choose here will be used as default folder for media menu link of the main menu. If you click on the media link or icon in the main menu, the page opens with the media items from this folder.'); ?></p></div>
							</div>
							<!-- SHOW SUBFOLDERS -->
							<div id="subfolders" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Include subfolders'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radioButtons('NEW_JB_OPTIONS[SHOW_SUBFOLDERS]', $this->options('show_subfolders')); ?>
									<p class="small text-muted"><?php echo I18N::translate('If you set this option the results on the media list page will include subfolders.'); ?></p>
								</div>
							</div>
							<?php endif; ?>
							<!-- SQUARE THUMBS -->
							<div id="square_thumbs" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Use square thumbs'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radioButtons('NEW_JB_OPTIONS[SQUARE_THUMBS]', $this->options('square_thumbs')); ?>
									<p class="small text-muted"><?php echo I18N::translate('Set this option to “yes” to use square thumbnails in individual boxes and charts. If you choose “no” the default webtrees thumbnails will be used'); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="panel2" class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a class="collapsed" href="#collapseTwo" data-target="#collapseTwo" data-toggle="collapse">
								<?php echo I18N::translate('Sort Topmenu items'); ?>
							</a>
						</h4>
					</div>
					<div class="panel-collapse collapse" id="collapseTwo">
						<div class="panel-heading">
							<?php echo I18N::translate('Click a row, then drag-and-drop to re-order the topmenu items. Then click the “save” button.'); ?>
						</div>
						<div class="panel-body">
							<?php
							$menulist = $this->options('menu');
							foreach ($menulist as $label => $menu) {
								$menu['sort'] == 0 ? $trashMenu[$label] = $menu : $activeMenu[$label] = $menu;
							}
							?>
							<?php if (isset($activeMenu)): ?>
								<ul id="sort-menu" class="list-group"><?php echo $this->listMenuJustBlack($activeMenu); ?></ul>
							<?php endif; ?>
							<?php if (isset($trashMenu)): // trashcan for toggling the compact menu.  ?>
								<ul id="trash-menu" class="sr-only"><?php echo $this->listMenuJustBlack($trashMenu); ?></ul>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<button class="btn btn-primary" type="submit">
				<i class="fa fa-check"></i>
				<?php echo I18N::translate('Save'); ?>
			</button>
			<button class="btn btn-primary" type="reset" onclick="if (confirm('<?php echo I18N::translate('The settings will be reset to default. Are you sure you want to do this?'); ?>'))
								window.location.href = 'module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_reset';">
				<i class="fa fa-recycle"></i>
				<?php echo I18N::translate('Reset'); ?>
			</button>
		</form>
		<?php
	}

	// Implement ModuleConfigInterface
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}
	
	/**
	 * Make sure the database structure is up-to-date.
	 * Update database when updating from a version prior then version 1.5.2.1
	 * Version 1 update if the admin has logged in. A message will be shown to tell him all settings are reset to default.
	 * Old db-entries will be removed.
	 * 
	 */
	protected static function updateSchema() {		
		if (Auth::isAdmin()) {
			try {
				Database::updateSchema(WT_ROOT . WT_MODULES_DIR . 'justblack_theme_options/db_schema/', 'JB_SCHEMA_VERSION', 1);
			} catch (PDOException $ex) {
				// The schema update scripts should never fail.  If they do, there is no clean recovery.
				FlashMessages::addMessage($ex->getMessage(), 'danger');
				header('Location: ' . WT_BASE_URL . 'site-unavailable.php');
				throw $ex;
			}
		}		
	}

}

return new JustBlackThemeOptionsModule;
