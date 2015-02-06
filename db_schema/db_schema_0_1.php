<?php
namespace Fisharebest\Webtrees;

/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

$rows = Database::prepare("SELECT * FROM `##module_setting` WHERE setting_name LIKE 'JB%'")->execute()->fetchAll();
if(count($rows) > 0) {
	Database::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'JB%'")->execute();
	Log::addConfigurationLog(I18N::translate('JustBlack Theme Options').' reset to default values due to changes in database scheme');
	FlashMessages::addMessage(I18N::translate('All JustBlack Theme options are reset to default due to changes in this version of the module. Click <a href="module.php?mod=justblack_theme_options&mod_action=admin_config">HERE</a> to update the settings to your needs.'));
}

// Update the version to indicate success
WT_Site::setPreference($schema_name, $next_version);
