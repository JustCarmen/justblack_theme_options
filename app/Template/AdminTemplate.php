<?php
/*
 * webtrees: online genealogy
 * Copyright (C) 2016 webtrees development team
 * Copyright (C) 2016 JustCarmen
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace JustCarmen\WebtreesAddOns\JustBlack\Template;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\I18N;
use JustCarmen\WebtreesAddOns\JustBlack\JustBlackThemeOptionsClass;

class AdminTemplate extends JustBlackThemeOptionsClass {

	protected function pageContent() {
		$controller = new PageController;
		return
			$this->pageHeader($controller) .
			$this->pageBody($controller);
	}

	private function pageHeader(PageController $controller) {
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
	}

	private function pageBody(PageController $controller) {
		?>
		<!-- ADMIN PAGE CONTENT -->
		<ol class="breadcrumb small">
			<li><a href="admin.php"><?php echo I18N::translate('Control panel') ?></a></li>
			<li><a href="admin_modules.php"><?php echo I18N::translate('Module administration') ?></a></li>
			<li class="active"><?php echo $this->getTitle() ?></li>
		</ol>
		<h2><?php echo $this->getTitle() ?></h2>
		<form action="<?php echo $this->getConfigLink() ?>" enctype="multipart/form-data" name="configform" method="post" class="form-horizontal">
			<input type="hidden" value="1" name="save">
			<?php echo Filter::getCsrf() ?>
			<input type="hidden" value="0" name="remove-image">
			<div id="accordion" class="panel-group">
				<div id="panel1" class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a href="#collapseOne" data-target="#collapseOne" data-toggle="collapse"><?php echo I18N::translate('Options') ?></a>
						</h4>
					</div>
					<div class="panel-collapse collapse in" id="collapseOne">
						<div class="panel-body">
							<!-- TREE TITLE -->
							<div id="tree-title" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Use the family tree title in the header') ?>
								</label>
								<div class="col-sm-8">
									<?php echo FunctionsEdit::editFieldYesNo('NEW_JB_OPTIONS[TREETITLE]', $this->options('treetitle'), 'class="radio-inline"') ?>
									<p class="small text-muted"><?php echo I18N::translate('Choose “no” if you have used the family tree title in your custom header image. Otherwise choose “yes”.') ?></p>
								</div>
							</div>
							<!-- TITLE POSITION -->
							<?php $titlepos	 = $this->options('titlepos'); ?>
							<div id="title-pos" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Position of the family tree title') ?>
								</label>
								<div class="col-sm-8">
									<div class="row">
										<div class="col-xs-2">
											<?php echo FunctionsEdit::selectEditControl('NEW_JB_OPTIONS[TITLEPOS][V][pos]', array('top' => I18N::translate('top'), 'bottom' => I18N::translate('bottom')), null, $titlepos['V']['pos'], 'class="form-control"') ?>
										</div>
										<div class="col-xs-2">
											<input
												type="text"
												value="<?php echo $titlepos['V']['size'] ?>"
												size="3"
												name="NEW_JB_OPTIONS[TITLEPOS][V][size]"
												class="form-control"
												>
										</div>
										<div class="col-xs-2">
											<?php echo FunctionsEdit::selectEditControl('NEW_JB_OPTIONS[TITLEPOS][V][fmt]', array('px' => 'px', '%' => '%'), null, $titlepos['V']['fmt'], 'class="form-control"') ?>
										</div>
									</div>
									<div class="row">
										<div class="col-xs-2">
											<?php echo FunctionsEdit::selectEditControl('NEW_JB_OPTIONS[TITLEPOS][H][pos]', array('left' => I18N::translate('left'), 'right' => I18N::translate('right')), null, $titlepos['H']['pos'], 'class="form-control"') ?>
										</div>
										<div class="col-xs-2">
											<input
												type="text"
												value="<?php echo $titlepos['H']['size'] ?>"
												size="3"
												name="NEW_JB_OPTIONS[TITLEPOS][H][size]"
												class="form-control"
												>
										</div>
										<div class="col-xs-2">
											<?php echo FunctionsEdit::selectEditControl('NEW_JB_OPTIONS[TITLEPOS][H][fmt]', array('px' => 'px', '%' => '%'), null, $titlepos['H']['fmt'], 'class="form-control"') ?>
										</div>
									</div>
									<p class="small text-muted"><?php echo I18N::translate('Here you can set the location of the family tree title. Adjust the values to your needs. If you want the tree title appear in the header image, the correct values depend on the length of the tree title. The position is the absolute position of the title, relative to the header area. For example: choose “Top: 0px; Left: 0px”  for the top left corner of the header area or “Top: 50%%; Right: 10px” to place the title at the right side in the middle of the header area with a 10px margin.') ?></p>
								</div>
							</div>
							<!-- TITLE SIZE -->
							<div id="title-size" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Size of the family tree title') ?>
								</label>
								<div class="col-sm-2">
									<div class="input-group">
										<input
											type="text"
											value="<?php echo $this->options('titlesize') ?>"
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
									<?php echo I18N::translate('Use header image') ?>
								</label>
								<div class="col-sm-2">
									<?php echo FunctionsEdit::selectEditControl('NEW_JB_OPTIONS[HEADER]', array(I18N::translate('Default'), I18N::translate('Custom'), I18N::translate('None')), null, $this->options('header'), 'class="form-control"') ?>
								</div>
							</div>
							<!-- IMAGE UPLOAD FIELD -->
							<div id="upload-image" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Upload a custom header image') ?>
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
											value="<?php echo $this->options('image') ?>"
											readonly
											onfocus="this.blur()"
											>
										<span id="file-input-btn" class="btn btn-default input-group-addon">
											<?php echo I18N::translate('Browse') ?>
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
									<?php echo I18N::translate('Resize image (800 x 150px)') ?>
								</label>
								<div class="col-sm-8">
									<?php echo FunctionsEdit::editFieldYesNo('resize', '0', 'class="radio-inline"') ?>
								</div>
							</div>
							<!-- HEADER HEIGHT -->
							<div id="header-height" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Height of the header area') ?>
								</label>
								<div class="col-sm-2">
									<div class="input-group">
										<input
											type="text"
											value="<?php echo $this->options('headerheight') ?>"
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
									<?php echo I18N::translate('Use flags in header bar as language menu') ?>
								</label>
								<div class="col-sm-8">
									<?php echo FunctionsEdit::editFieldYesNo('NEW_JB_OPTIONS[FLAGS]', $this->options('flags'), 'class="radio-inline"') ?>
									<p class="small text-muted"><?php echo I18N::translate('You can use flags in the bar above the main menu bar in the header. These flags replaces the default dropdown menu. We advise you not to use this option if you have more then ten languages installed. You can remove unused languages from the folder languages in your webtrees installation.') ?></p>
								</div>
							</div>
							<!-- COMPACT MENU -->
							<div id="compact-menu" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Use a compact menu') ?>
								</label>
								<div class="col-sm-8">
									<?php echo FunctionsEdit::editFieldYesNo('NEW_JB_OPTIONS[COMPACT_MENU]', $this->options('compact_menu'), 'class="radio-inline"') ?>
									<p class="small text-muted"><?php echo I18N::translate('In the compact “View”-menu the menus for Charts, Lists, Calendar and (optionally) Reports will be merged together.') ?></p>
								</div>
							</div>
							<!-- REPORTS -->
							<div id="reports" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Include the reports menu in the compact “View” menu') ?>
								</label>
								<div class="col-sm-8">
									<?php echo FunctionsEdit::editFieldYesNo('NEW_JB_OPTIONS[COMPACT_MENU_REPORTS]', $this->options('compact_menu_reports'), 'class="radio-inline"') ?>
								</div>
							</div>
							<!-- MEDIA MENU -->
							<?php $folders	 = $this->options('mediafolders'); ?>
							<div id="media-menu" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Media menu in main menu') ?>
								</label>
								<div class="col-sm-8">
									<?php echo FunctionsEdit::editFieldYesNo('NEW_JB_OPTIONS[MEDIA_MENU]', $this->options('media_menu'), 'class="radio-inline"') ?>
									<p class="small text-muted"><?php echo I18N::translate('If this option is set the media menu will be moved to the main menu.') ?></p>
									<?php if (count($folders) > 1): // add extra information about subfolders ?>
										<p class="small text-muted"><?php echo I18N::translate('The names of first level media folders from your media folder on the server will be used as submenu items of the new media menu. Warning: these submenu items are not translated automatically. Use a custom language file to translate your menu items. Read the webrees WIKI for more information.') ?></p>
									<?php endif; ?>
								</div>
							</div>
							<?php if (count($folders) > 1): // only show these options if we have subfolders  ?>
								<!-- MEDIA FOLDER LIST -->
								<div id="medialist" class="form-group form-group-sm">
									<label class="control-label col-sm-4">
										<?php echo I18N::translate('Choose a folder as default for the main menu link') ?>
									</label>
									<div class="col-sm-2">
										<?php echo FunctionsEdit::selectEditControl('NEW_JB_OPTIONS[MEDIA_LINK]', $folders, null, $this->options('media_link'), 'class="form-control"') ?>
									</div>
									<div class="col-sm-8 col-sm-offset-4"><p class="small text-muted"><?php echo I18N::translate('The media folder you choose here will be used as default folder for media menu link of the main menu. If you click on the media link or icon in the main menu, the page opens with the media items from this folder.') ?></p></div>
								</div>
								<!-- SHOW SUBFOLDERS -->
								<div id="subfolders" class="form-group form-group-sm">
									<label class="control-label col-sm-4">
										<?php echo I18N::translate('Include subfolders') ?>
									</label>
									<div class="col-sm-8">
										<?php echo FunctionsEdit::editFieldYesNo('NEW_JB_OPTIONS[SHOW_SUBFOLDERS]', $this->options('show_subfolders'), 'class="radio-inline"') ?>
										<p class="small text-muted"><?php echo I18N::translate('If you set this option the results on the media list page will include subfolders.') ?></p>
									</div>
								</div>
							<?php endif; ?>
							<!-- SQUARE THUMBS -->
							<div id="square_thumbs" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Use square thumbs') ?>
								</label>
								<div class="col-sm-8">
									<?php echo FunctionsEdit::editFieldYesNo('NEW_JB_OPTIONS[SQUARE_THUMBS]', $this->options('square_thumbs'), 'class="radio-inline"') ?>
									<p class="small text-muted"><?php echo I18N::translate('Set this option to “yes” to use square thumbnails in individual boxes and charts. If you choose “no” the default webtrees thumbnails will be used.') ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="panel2" class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a class="collapsed" href="#collapseTwo" data-target="#collapseTwo" data-toggle="collapse">
								<?php echo I18N::translate('Sort menu items') ?>
							</a>
						</h4>
					</div>
					<div class="panel-collapse collapse" id="collapseTwo">
						<div class="panel-heading">
							<?php echo I18N::translate('Click a row, then drag-and-drop to re-order the menu items. Then click the “save” button.') ?>
						</div>
						<div class="panel-body">
							<?php
							$menulist = $this->options('menu');
							foreach ($menulist as $label => $menu) {
								if ($this->isMenu($label)) {
									$menu['sort'] == 0 ? $trashMenu[$label]	 = $menu : $activeMenu[$label]	 = $menu;
								}
							}
							?>
							<?php if (isset($activeMenu)): ?>
								<ul id="sort-menu" class="list-group"><?php echo $this->listMenuJustBlack($activeMenu) ?></ul>
							<?php endif; ?>
							<?php if (isset($trashMenu)): // trashcan for toggling the compact menu.   ?>
								<ul id="trash-menu" class="sr-only"><?php echo $this->listMenuJustBlack($trashMenu) ?></ul>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<button class="btn btn-primary" type="submit">
				<i class="fa fa-check"></i>
				<?php echo I18N::translate('save') ?>
			</button>
			<button class="btn btn-primary" type="reset" onclick="if (confirm('<?php echo I18N::translate('The settings will be reset to default. Are you sure you want to do this?') ?>'))
						window.location.href = 'module.php?mod=<?php echo $this->getName() ?>&amp;mod_action=admin_reset';">
				<i class="fa fa-recycle"></i>
				<?php echo I18N::translate('reset') ?>
			</button>
		</form>
		<?php
	}

}
