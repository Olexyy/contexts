<?php

namespace Drupal\Tests\contexts\Kernel;

use Drupal\Core\Language\LanguageInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Plugin\migrate\source\Language;

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
    $this->assertEquals($expected[0], $this->storage->load([
        'source' => '/test-source-case',
        'contexts_path' => 'context1/context2']
    ));
    $this->assertEquals($expected[1], $this->storage->load([
        'alias' => '/test-alias-Case',
        'contexts_path' => 'context3/context4']
    ));
    $this->assertFalse($this->storage->load([
        'source' => '/test-source-case',
        'contexts_path' => 'context_not_matches']
    ));
  }

  /**
   * @covers ::save
   * @covers ::load
   */
  public function testUpdateWithContexts() {

    $this->storage->save(
      '/test-source-Case',
      '/test-alias-Case',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      NULL,
      'context1/context2'
    );
    $expected []= [
      'pid' => '1',
      'alias' => '/test-alias-Case',
      'source' => '/test-source-Case',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'contexts_path' => 'context1/context2',
    ];
    $expected []= [
      'pid' => '1',
      'alias' => '/test-alias-Case',
      'source' => '/test-source-Case',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'contexts_path' => 'context3/context4',
    ];
    $this->assertEquals($expected[0], $this->storage->load(['alias'=>'/test-alias-Case']), 'Url alias created.');
    $this->storage->save(
      '/test-source-Case',
      '/test-alias-Case',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      '1',
      'context3/context4',
      'context1/context2'
    );
    $this->assertEquals($expected[1], $this->storage->load(['alias'=>'/test-alias-Case']), 'Url alias updated.');
    $this->storage->save(
      '/test-source-Case',
      '/test-alias-Case',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      NULL,
      'context5/context6'
    );
    $pathAliass = $this->storage->loadAll(['alias'=>'/test-alias-Case']);
    $this->assertCount(2, $pathAliass, 'Added new alias instead of updating');
  }

  /**
   * @covers ::save
   * @covers ::delete
   */
  public function testDeleteWithContexts() {

    $this->storage->save(
      '/test-source-Case1',
      '/test-alias-Case1',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      NULL,
      'context1/context2'
    );
    $this->storage->save(
      '/test-source-Case1',
      '/test-alias-Case1',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      '1',
      'context3/context4'
    );
    $this->storage->save(
      '/test-source-Case2',
      '/test-alias-Case2',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      NULL,
      'context5/context6'
    );
    $deleted = $this->storage->delete(['alias' => '/test-alias-Case1']);
    $this->assertEqual($deleted, 1, 'Deleted 1 record');
    $this->assertFalse($this->storage->load(['contexts_path' => 'context1/context2']), 'No paths found');
    $this->assertFalse($this->storage->load(['contexts_path' => 'context3/context4']), 'No paths found');
    $this->assertEqual($this->storage->load(['contexts_path' => 'context5/context6'])['contexts_path'],
      'context5/context6', 'Found 1 path');
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
   * @covers ::lookupPathAlias
   */
  public function testLookupPathAliasContexts() {

    $this->storage->save(
      '/test-source-Case1',
      '/test-alias1',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      NULL,
      'context1/context2');
    $this->storage->save(
      '/test-source-Case2',
      '/test-alias2',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      NULL,
      'context3/context4');

    $alias = $this->storage->lookupPathAlias(
      '/test-source-Case1',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'context1/context2');
    $this->assertEquals('/test-alias1', $alias, 'Alias found');
    $alias = $this->storage->lookupPathAlias(
      '/test-source-Case1',
      LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'context3/context4');
    $this->assertFalse($alias, 'Alias not found');
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
   * @covers ::lookupPathSource
   */
  public function testLookupPathSourceContexts() {

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
