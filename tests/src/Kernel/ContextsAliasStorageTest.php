<?php

namespace Drupal\Tests\contexts\Kernel;

use Drupal\Core\Language\LanguageInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\contexts\Path\ContextsAliasStorage
 * @group path
 * @group contexts
 */
class ContextsAliasStorageTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'contexts'];

  /**
   * @var \Drupal\contexts\Path\ContextsAliasStorage
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {

    parent::setUp();
    $this->installSchema('system', ['url_alias']);
    $this->installSchema('contexts', ['url_alias_contexts']);
    $this->storage = $this->container->get('path.alias_storage');
  }

  /**
   * @covers ::load
   * @covers ::save
   */
  public function testLoad() {
    $this->storage->save('/test-source-Case', '/test-alias-Case');

    $expected = [
      'pid' => '1',
      'alias' => '/test-alias-Case',
      'source' => '/test-source-Case',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'contexts_path' => NULL,
    ];

    $this->assertEquals($expected, $this->storage->load(['alias' => '/test-alias-Case']));
    $this->assertEquals($expected, $this->storage->load(['alias' => '/test-alias-case']));
    $this->assertEquals($expected, $this->storage->load(['source' => '/test-source-Case']));
    $this->assertEquals($expected, $this->storage->load(['source' => '/test-source-case']));
  }

  /**
   * @covers ::save
   * @covers ::load
   */
  public function testLoadContexts() {
    
    $this->storage->save(
      '/test-source-Case',
      '/test-alias-Case',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      NULL,
      'context1/context2'
    );

    $expected = [
      'pid' => '1',
      'alias' => '/test-alias-Case',
      'source' => '/test-source-Case',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'contexts_path' => 'context1/context2',
    ];

    $this->assertEquals($expected, $this->storage->load(['alias' => '/test-alias-Case']));
    $this->assertEquals($expected, $this->storage->load(['alias' => '/test-alias-case']));
    $this->assertEquals($expected, $this->storage->load(['source' => '/test-source-Case']));
    $this->assertEquals($expected, $this->storage->load(['source' => '/test-source-case']));
    $this->assertEquals($expected, $this->storage->load([
      'source' => '/test-source-case',
      'contexts_path' => 'context1/context2']
    ));
    $this->assertEquals($expected, $this->storage->load([
      'alias' => '/test-alias-Case',
      'contexts_path' => 'context1/context2']
    ));
    $this->assertFalse($this->storage->load([
      'source' => '/test-source-case',
      'contexts_path' => 'context_not_matches']
    ));
    $this->assertFalse($this->storage->load([
      'alias' => '/test-alias-case',
      'contexts_path' => 'context_not_matches']
    ));
  }

  /**
   * @covers ::save
   * @covers ::load
   * @covers ::loadAll
   */
  public function testLoadContextsContextsMultiple() {

    $this->storage->save(
      '/test-source-Case',
      '/test-alias-Case',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      NULL,
      'context1/context2'
    );
    $this->storage->save(
      '/test-source-Case',
      '/test-alias-Case',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      NULL,
      'context3/context4'
    );
    $expected = [];
    $expected []= [
      'pid' => '1',
      'alias' => '/test-alias-Case',
      'source' => '/test-source-Case',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'contexts_path' => 'context1/context2',
    ];
    $expected []= [
      'pid' => '2',
      'alias' => '/test-alias-Case',
      'source' => '/test-source-Case',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'contexts_path' => 'context3/context4',
    ];
    $aliases = $this->storage->loadAll(['alias' => '/test-alias-Case']);
    $this->assertCount(2, $aliases, 'Created two aliases');
    foreach (array_values($aliases) as $index => $alias) {
      $this->assertEquals($expected[$index], $alias, 'Alias matches expected.');
    }
    /*
    $this->assertEquals($expected, $this->storage->load([
        'source' => '/test-source-case',
        'contexts_path' => 'context1/context2']
    ));
    $this->assertEquals($expected, $this->storage->load([
        'alias' => '/test-alias-Case',
        'contexts_path' => 'context1/context2']
    ));
    $this->assertFalse($this->storage->load([
        'source' => '/test-source-case',
        'contexts_path' => 'context_not_matches']
    ));
    $this->assertFalse($this->storage->load([
        'alias' => '/test-alias-case',
        'contexts_path' => 'context_not_matches']
    ));
    */
  }

  /**
   * @covers ::lookupPathAlias
   */
  public function testLookupPathAlias() {
    $this->storage->save('/test-source-Case', '/test-alias');

    $this->assertEquals('/test-alias', $this->storage->lookupPathAlias('/test-source-Case', LanguageInterface::LANGCODE_NOT_SPECIFIED));
    $this->assertEquals('/test-alias', $this->storage->lookupPathAlias('/test-source-case', LanguageInterface::LANGCODE_NOT_SPECIFIED));
  }

  /**
   * @covers ::lookupPathSource
   */
  public function testLookupPathSource() {
    $this->storage->save('/test-source', '/test-alias-Case');

    $this->assertEquals('/test-source', $this->storage->lookupPathSource('/test-alias-Case', LanguageInterface::LANGCODE_NOT_SPECIFIED));
    $this->assertEquals('/test-source', $this->storage->lookupPathSource('/test-alias-case', LanguageInterface::LANGCODE_NOT_SPECIFIED));
  }

  /**
   * @covers ::aliasExists
   */
  public function testAliasExists() {
    $this->storage->save('/test-source-Case', '/test-alias-Case');

    $this->assertTrue($this->storage->aliasExists('/test-alias-Case', LanguageInterface::LANGCODE_NOT_SPECIFIED));
    $this->assertTrue($this->storage->aliasExists('/test-alias-case', LanguageInterface::LANGCODE_NOT_SPECIFIED));
  }

}
