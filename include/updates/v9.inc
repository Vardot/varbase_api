<?php

/**
 * @file
 * Contains varbase_api_update_9###(s) hook updates.
 */

use Vardot\Installer\ModuleInstallerFactory;

/**
 * Issue #3272182: Switch to use the Module Installer Factory.
 *
 * In the Varbase API module.
 */
function varbase_api_update_90001() {
  // Set the weight of the module after installation of list of modules.
  // Make sure that any hook or event subscriber workers after all used modules.
  ModuleInstallerFactory::setModuleWeightAfterInstallation('varbase_api');
}
