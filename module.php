<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2018 JustCarmen (http://justcarmen.nl)
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
namespace JustCarmen\WebtreesAddOns\JustBlack;

use Composer\Autoload\ClassLoader;
use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Html;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use JustCarmen\WebtreesAddOns\JustBlack\Template\AdminTemplate;

class JustBlackThemeOptionsModule extends AbstractModule implements ModuleConfigInterface {
	const CUSTOM_VERSION = '2.0.0-dev';
	const CUSTOM_WEBSITE = 'http://www.justcarmen.nl/themes/justblack/';
	// How to update the database schema for this module
	const SCHEMA_TARGET_VERSION   = 3;
	const SCHEMA_SETTING_NAME     = 'JB_SCHEMA_VERSION';
	const SCHEMA_MIGRATION_PREFIX = '\JustCarmen\WebtreesAddOns\JustBlack\Schema';

	/** @var string location of the JustBlack Theme Options module files */
	public $directory;

	public function __construct() {
		parent::__construct('justblack_theme_options');

		$this->directory = WT_MODULES_DIR . $this->getName();

		// register the namespace
		$loader = new ClassLoader();
		$loader->addPsr4('JustCarmen\\WebtreesAddOns\\JustBlack\\', $this->directory . '/app');
		$loader->register();
	}

	/**
	 * Get the module class.
	 * 
	 * Class functions are called with $this inside the source directory.
	 */
	private function module() {
		return new JustBlackThemeOptionsClass;
	}

	// Extend Module
	public function getTitle(): string {
		return /* I18N: Name of a module  */ I18N::translate('JustBlack Theme Options');
	}

	// Extend Module
	public function getDescription(): string {
		return /* I18N: Description of the module */ I18N::translate('Set options for the JustBlack theme within the admin interface');
	}

	// Extend ModuleConfigInterface
	public function modAction($mod_action) {
		Database::updateSchema(self::SCHEMA_MIGRATION_PREFIX, self::SCHEMA_SETTING_NAME, self::SCHEMA_TARGET_VERSION);

		switch ($mod_action) {
			case 'admin_config':
				if (Filter::postBool('save') && Filter::checkCsrf()) {
					$this->module()->saveOptions();
				}
				$template = new AdminTemplate;
				return $template->pageContent();
			case 'admin_reset':
				$this->module()->deleteImage();
				Database::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'JB%'")->execute();
				Log::addConfigurationLog($this->getTitle() . ' reset to default values');
				$template = new AdminTemplate;
				return $template->pageContent();
			case 'delete_image':
				$this->module()->deleteImage();
				break;
			default:
				http_response_code(404);
				break;
		}
	}

	/** {@inheritdoc} */
	public function getConfigLink(): string {
		return Html::url('module.php', [
			'mod'        => $this->getName(),
			'mod_action' => 'admin_config',
		]);
	}
}

return new JustBlackThemeOptionsModule;
