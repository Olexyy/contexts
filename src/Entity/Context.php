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

}
