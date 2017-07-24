<?php
/*
 * webtrees: online genealogy
 * Copyright (C) 2017 webtrees development team
 * Copyright (C) 2017 JustCarmen
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
use Fisharebest\Webtrees\Bootstrap4;
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
				var filename = jQuery(this)[0].files[0].name;
				jQuery("#file-input-text").val(filename);
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
		
		echo Bootstrap4::breadcrumbs([
			'admin.php'			 => I18N::translate('Control panel'),
			'admin_modules.php'	 => I18N::translate('Module administration'),
			], $controller->getPageTitle());
		?>

		<h1><?= $controller->getPageTitle() ?></h1>
		<form action="<?= $this->getConfigLink() ?>" enctype="multipart/form-data" name="configform" method="post" class="form-horizontal">
			<input type="hidden" value="1" name="save">
			<?= Filter::getCsrf() ?>
			<input type="hidden" value="0" name="remove-image">
			<div id="accordion" role="tablist" aria-multiselectable="true">
				<div class="card">
					<div class="card-header" role="tab" id="card-options-header">
						<h5 class="mb-0">
							<a data-toggle="collapse" data-parent="#accordion" href="#card-options-content" aria-expanded="true" aria-controls="card-options-content">
								<?= I18N::translate('Options') ?>
							</a>
						</h5>
					</div>
					<div id="card-options-content" class="collapse show" role="tabpanel" aria-labelledby="card-options-header">
						<div class="card-block">
							<!-- TREE TITLE -->
							<div id="tree-title" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Use the family tree title in the header') ?>
								</label>
								<div class="col-sm-8">
									<?= Bootstrap4::radioButtons('NEW_JB_OPTIONS[TREETITLE]', FunctionsEdit::optionsNoYes(), $this->options('treetitle'), true) ?>
									<p class="small text-muted"><?= I18N::translate('Choose “no” if you have used the family tree title in your custom header image. Otherwise choose “yes”.') ?></p>
								</div>
							</div>
							<!-- TITLE POSITION -->
							<?php $titlepos	 = $this->options('titlepos'); ?>
							<div id="title-pos" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Position of the family tree title') ?>
								</label>
								<div class="col-sm-8">
									<div class="row">
										<div class="col-sm-2">
											<?= Bootstrap4::select(['top' => I18N::translate('top'), 'bottom' => I18N::translate('bottom')], $titlepos['V']['pos'], ['name' => 'NEW_JB_OPTIONS[TITLEPOS][V][pos]']) ?>
										</div>
										<div class="col-sm-2">
											<input
												type="text"
												value="<?= $titlepos['V']['size'] ?>"
												size="3"
												name="NEW_JB_OPTIONS[TITLEPOS][V][size]"
												class="form-control"
												>
										</div>
										<div class="col-sm-2">
											<?= Bootstrap4::select(['px' => 'px', '%' => '%'], $titlepos['V']['fmt'], ['name' => 'NEW_JB_OPTIONS[TITLEPOS][V][fmt]']) ?>
										</div>
									</div>
									<div class="row form-group mt-2">
										<div class="col-sm-2">
											<?= Bootstrap4::select(['left' => I18N::translate('left'), 'right' => I18N::translate('right')], $titlepos['H']['pos'], ['name' => 'NEW_JB_OPTIONS[TITLEPOS][H][pos]']) ?>
										</div>
										<div class="col-sm-2">
											<input
												type="text"
												value="<?= $titlepos['H']['size'] ?>"
												size="3"
												name="NEW_JB_OPTIONS[TITLEPOS][H][size]"
												class="form-control"
												>
										</div>
										<div class="col-sm-2">
											<?= Bootstrap4::select(['px' => 'px', '%' => '%'], $titlepos['H']['fmt'], ['name' => 'NEW_JB_OPTIONS[TITLEPOS][H][fmt]']) ?>
										</div>
									</div>
									<p class="small text-muted"><?= I18N::translate('Here you can set the location of the family tree title. Adjust the values to your needs. If you want the tree title appear in the header image, the correct values depend on the length of the tree title. The position is the absolute position of the title, relative to the header area. For example: choose “Top: 0px; Left: 0px”  for the top left corner of the header area or “Top: 50%%; Right: 10px” to place the title at the right side in the middle of the header area with a 10px margin.') ?></p>
								</div>
							</div>
							<!-- TITLE SIZE -->
							<div id="title-size" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Size of the family tree title') ?>
								</label>
								<div class="col-sm-2">
									<div class="input-group">
										<input
											type="text"
											value="<?= $this->options('titlesize') ?>"
											size="2"
											name="NEW_JB_OPTIONS[TITLESIZE]"
											class="form-control"
											>
										<span class="input-group-addon">px</span>
									</div>
								</div>
							</div>
							<!-- HEADER IMAGE -->
							<div id="header-image" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Use header image') ?>
								</label>
								<div class="col-sm-2">
									<?= Bootstrap4::select([I18N::translate('Default'), I18N::translate('Custom'), I18N::translate('None')], $this->options('header'), ['name' => 'NEW_JB_OPTIONS[HEADER]']) ?>
								</div>
							</div>
							<!-- IMAGE UPLOAD FIELD -->
							<div id="upload-image" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Upload a custom header image') ?>
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
											value="<?= $this->options('image') ?>"
											readonly
											onfocus="this.blur()"
											>
										<span id="file-input-btn" class="btn btn-default input-group-addon">
											<?= I18N::translate('Browse') ?>
										</span>
										<span id="file-delete" class="btn input-group-addon">
											<i class="fa fa-trash"></i>
										</span>
									</div>
								</div>
							</div>
							<!-- RESIZE IMAGE -->
							<div id="resize-image" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Resize image (800 x 150px)') ?>
								</label>
								<div class="col-sm-8">
									<?= Bootstrap4::radioButtons('resize', FunctionsEdit::optionsNoYes(), '0', true) ?>
								</div>
							</div>
							<!-- HEADER HEIGHT -->
							<div id="header-height" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Height of the header area') ?>
								</label>
								<div class="col-sm-2">
									<div class="input-group">
										<input
											type="text"
											value="<?= $this->options('headerheight') ?>"
											size="2"
											name="NEW_JB_OPTIONS[HEADERHEIGHT]"
											class="form-control"
											>
										<span class="input-group-addon">px</span>
									</div>

								</div>
							</div>
							<!-- FLAGS -->
							<div id="flags" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Use flags in header bar as language menu') ?>
								</label>
								<div class="col-sm-8">
									<?= Bootstrap4::radioButtons('NEW_JB_OPTIONS[FLAGS]', FunctionsEdit::optionsNoYes(), $this->options('flags'), true) ?>
									<p class="small text-muted"><?= I18N::translate('You can use flags in the bar above the main menu. These flags replaces the default dropdown menu. We advise you not to use this option if you have more then ten languages installed. <a href="admin_site_config.php?action=languages" target="_blank">Disable languages</a>.') ?></p>
								</div>
							</div>
							<!-- COMPACT MENU -->
							<div id="compact-menu" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Use a compact menu') ?>
								</label>
								<div class="col-sm-8">
									<?= Bootstrap4::radioButtons('NEW_JB_OPTIONS[COMPACT_MENU]', FunctionsEdit::optionsNoYes(), $this->options('compact_menu'), true) ?>
									<p class="small text-muted"><?= I18N::translate('In the compact “View”-menu the menus for Charts, Lists, Calendar and (optionally) Reports will be merged together.') ?></p>
								</div>
							</div>
							<!-- REPORTS -->
							<div id="reports" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Include the reports menu in the compact “View” menu') ?>
								</label>
								<div class="col-sm-8">
									<?= Bootstrap4::radioButtons('NEW_JB_OPTIONS[COMPACT_MENU_REPORTS]', FunctionsEdit::optionsNoYes(), $this->options('compact_menu_reports'), true) ?>
								</div>
							</div>
							<!-- MEDIA MENU -->
							<?php $folders	 = $this->options('mediafolders'); ?>
							<div id="media-menu" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Media menu in main menu') ?>
								</label>
								<div class="col-sm-8">
									<?= Bootstrap4::radioButtons('NEW_JB_OPTIONS[MEDIA_MENU]', FunctionsEdit::optionsNoYes(), $this->options('media_menu'), true) ?>
									<p class="small text-muted"><?= I18N::translate('If this option is set the media menu will be moved to the main menu.') ?></p>
									<?php if (count($folders) > 1): // add extra information about subfolders ?>
										<p class="small text-muted"><?= I18N::translate('The names of first level media folders from your media folder on the server will be used as submenu items of the new media menu. Warning: these submenu items are not translated automatically. Use a custom language file to translate your menu items. Read the webrees WIKI for more information.') ?></p>
									<?php endif; ?>
								</div>
							</div>
							<?php if (count($folders) > 1): // only show these options if we have subfolders  ?>
								<!-- MEDIA FOLDER LIST -->
								<div id="medialist" class="row form-group">
									<label class="col-form-label col-sm-4">
										<?= I18N::translate('Choose a folder as default for the main menu link') ?>
									</label>
									<div class="col-sm-2">
										<?= Bootstrap4::select($folders, $this->options('media_link'), ['name' => 'NEW_JB_OPTIONS[MEDIA_LINK]']) ?>
									</div>
									<div class="col-sm-8 offset-sm-4"><p class="small text-muted"><?= I18N::translate('The media folder you choose here will be used as default folder for media menu link of the main menu. If you click on the media link or icon in the main menu, the page opens with the media items from this folder.') ?></p></div>
								</div>
								<!-- SHOW SUBFOLDERS -->
								<div id="subfolders" class="row form-group">
									<label class="col-form-label col-sm-4">
										<?= I18N::translate('Include subfolders') ?>
									</label>
									<div class="col-sm-8">
										<?= Bootstrap4::radioButtons('NEW_JB_OPTIONS[SHOW_SUBFOLDERS]', FunctionsEdit::optionsNoYes(), $this->options('show_subfolders'), true) ?>
										<p class="small text-muted"><?= I18N::translate('If you set this option the results on the media list page will include subfolders.') ?></p>
									</div>
								</div>
							<?php endif; ?>
							<!-- SQUARE THUMBS -->
							<div id="square_thumbs" class="row form-group">
								<label class="col-form-label col-sm-4">
									<?= I18N::translate('Use square thumbs') ?>
								</label>
								<div class="col-sm-8">
									<?= Bootstrap4::radioButtons('NEW_JB_OPTIONS[SQUARE_THUMBS]', FunctionsEdit::optionsNoYes(), $this->options('square_thumbs'), true) ?>
									<p class="small text-muted"><?= I18N::translate('Set this option to “yes” to use square thumbnails in individual boxes and charts. If you choose “no” the default webtrees thumbnails will be used.') ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" role="tab" id="card-menulist-header">
						<h5 class="mb-0">
							<a data-toggle="collapse" data-parent="#accordion" href="#card-menulist-content" aria-expanded="true" aria-controls="card-menulist-content">
								<?= I18N::translate('Sort menu items') ?>
							</a>
						</h5>
					</div>
					<div id="card-menulist-content" class="collapse" role="tabpanel" aria-labelledby="card-menulist-header">
						<div class="card-block">
							<h6><?= I18N::translate('Click a row, then drag-and-drop to re-order the menu items. Then click the “save” button.') ?></h6>
							<?php
							$menulist = $this->options('menu');
							foreach ($menulist as $label => $menu) {
								if ($this->isMenu($label)) {
									$menu['sort'] == 0 ? $trashMenu[$label]	 = $menu : $activeMenu[$label]	 = $menu;
								}
							}
							?>
							<?php if (isset($activeMenu)): ?>
								<ul id="sort-menu" class="list-group"><?= $this->listMenuJustBlack($activeMenu) ?></ul>
							<?php endif; ?>
							<?php if (isset($trashMenu)): // trashcan for toggling the compact menu.   ?>
								<ul id="trash-menu" class="sr-only"><?= $this->listMenuJustBlack($trashMenu) ?></ul>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="mt-3">
				<button class="btn btn-primary" type="submit">
					<i class="fa fa-check"></i>
					<?= I18N::translate('save') ?>
				</button>
				<button class="btn btn-primary" type="reset" onclick="if (confirm('<?= I18N::translate('The settings will be reset to default. Are you sure you want to do this?') ?>'))
							window.location.href = 'module.php?mod=<?= $this->getName() ?>&amp;mod_action=admin_reset';">
					<i class="fa fa-recycle"></i>
					<?= I18N::translate('reset') ?>
				</button>
			</div>
		</form>
		<?php
	}

}
