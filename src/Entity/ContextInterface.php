<?php

namespace Drupal\contexts\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Context entities.
 */
interface ContextInterface extends ConfigEntityInterface {

  /**
   * Getter for position.
   *
   * @return int
   *   Value.
   */
  public function getPosition();

  /**
   * Setter for position.
   *
   * @param int $position
   *   Position.
   *
   * @return $this
   */
  public function setPosition($position);

}
