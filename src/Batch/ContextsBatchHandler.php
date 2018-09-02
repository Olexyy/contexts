<?php

namespace Drupal\contexts\Batch;


use Drupal\contexts\Service\ContextsService;

/**
 * Class BlockchainBatchHelper.
 *
 * @package Drupal\blockchain\Utils
 */
class ContextsBatchHandler {

  /**
   * Initializes and starts mining batch.
   *
   * @param string $storageIdentifier
   *   Array of storage identifiers.
   * @param int $totalItems
   *   Total items to process.
   * @param int $batchSize
   *   Number of items, processed in batch.
   * @param string|\Drupal\Core\Url $redirect
   *   Redirect location.
   *
   * @return null|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Return redirect.
   */
  public static function setFieldPurgeBatch($storageIdentifier, $totalItems, $batchSize = 10, $redirect = NULL) {

    static::set(static::getFieldPurgeBatchDefinition($storageIdentifier, $totalItems, $batchSize));

    return static::process($redirect);
  }

  /**
   * Setup handler for batch.
   *
   * @param array $definition
   *   Definition for batch.
   */
  public static function set(array $definition) {

    batch_set($definition);
  }

  /**
   * Starts batch processing.
   *
   * @param string|\Drupal\Core\Url $redirect
   *   Redirect location.
   *
   * @return null|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Return redirect.
   */
  public static function process($redirect = NULL) {

    return batch_process($redirect);
  }

  /**
   * Mining batch definition.
   *
   * @param string $storageIdentifier
   *   Storage identifier.
   * @param $totalItems
   *   Total items to  process.
   * @param int $batchSize
   *   Batch size.
   *
   * @return array
   *   Definition for batch.
   */
  public static function getFieldPurgeBatchDefinition($storageIdentifier, $totalItems, $batchSize = 10) {

    $stages = $totalItems / $batchSize? $totalItems / $batchSize : 1;
    $batch = [
      'title' => t('Deleting field data ...'),
      'operations' => [],
      'finished' => static::class . '::finalizeFieldPurgeBatch',
      'total' => $totalItems,
      'results' => [],
      'storage_id' => $storageIdentifier,
      'batch_size' => $batchSize,
    ];
    while ($stages) {
      $batch['operations'][] = [static::class . '::processFieldPurgeBatch', []];
      $stages--;
    }

    return $batch;
  }

  /**
   * Batch processor.
   *
   * @param array $context
   *   Batch context.
   */
  public static function processFieldPurgeBatch(array &$context) {

    ContextsService::instance()
      ->getHelperFieldService()
      ->fieldPurgeBatch($context['batch_size'], $context['storage_id']);
    $context['results'][] = TRUE;
    $context['message'] = t('Processing...(@count/@total)', [
      '@count' => count($context['results']),
      '@total' => $context['total'],
    ]);
  }

  /**
   * Batch finalizer.
   *
   * {@inheritdoc}
   */
  public static function finalizeFieldPurgeBatch($success, $results, $operations) {

    if ($success) {
      $message = t('@count items processed.', [
        '@count' => count($results),
      ]);
    }
    else {
      $message = t('Finished with an error.');
    }
    \Drupal::messenger()->addStatus($message);
  }

}
