<?php

namespace Drupal\contexts\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Context entity.
 *
 * @ConfigEntityType(
 *   id = "context",
 *   label = @Translation("Context"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\contexts\ContextListBuilder",
 *     "form" = {
 *       "add" = "Drupal\contexts\Form\ContextForm",
 *       "edit" = "Drupal\contexts\Form\ContextForm",
 *       "delete" = "Drupal\contexts\Form\ContextDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\contexts\ContextHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "context",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/context/{context}",
 *     "add-form" = "/admin/structure/context/add",
 *     "edit-form" = "/admin/structure/context/{context}/edit",
 *     "delete-form" = "/admin/structure/context/{context}/delete",
 *     "collection" = "/admin/structure/context"
 *   }
 * )
 */
class Context extends ConfigEntityBase implements ContextInterface {

  /**
   * The Context ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Context label.
   *
   * @var string
   */
  protected $label;

  /**
   * Position in path.
   *
   * @var int
   */
  protected $position;

  /**
   * Getter for position.
   *
   * @return int
   *   Value.
   */
  public function getPosition() {

    return (int) $this->position;
  }

  /**
   * Setter for position.
   *
   * @param int $position
   *   Position.
   *
   * @return $this
   */
  public function setPosition($position) {

    $this->position = (int) $position;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {

    if ($this->isNew()) {

      return [];
    }

    return [$this->entityTypeId . ':' . $this->id()];
  }

}
