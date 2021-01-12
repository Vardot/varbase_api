<?php

namespace Drupal\varbase_api_vmp;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a varbase mobile pages entity.
 * @ingroup varbase_api_vmp
 */
interface VarbaseMobilePagesInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
