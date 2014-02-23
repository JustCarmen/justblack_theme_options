<?php
// Update the Fancy Tree View module database schema from version 0 to 1
// - add key 'LINK' to FTV_SETTINGS
// - Change options to multidimeninal array with array key = tree id.
//
// The script should assume that it can be interrupted at
// any point, and be able to continue by re-running the script.
// Fatal errors, however, should be allowed to throw exceptions,
// which will be caught by the framework.
// It shouldn't do anything that might take more than a few
// seconds, for systems with low timeout values.
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

WT_DB::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'JB%'")->execute();
AddToLog(WT_I18N::translate('JustBlack Theme Options').' reset to default values due to changes in database scheme', 'config');
WT_FlashMessages::addMessage(WT_I18N::translate('Your settings are reset to default due to changes in this version of the module. Please update the settings to your needs.'));

// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);
