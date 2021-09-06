<?php

namespace Drupal\Tests\varbase_api\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests Varbase API Settings test.
 *
 * @group varbase_api
 */
class VarbaseApiSettingsTest extends BrowserTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['varbase_api'];

  protected function setUp(): void {
    parent::setUp();
    $this->container->get('theme_installer')->install(['claro']);
    $this->config('system.theme')->set('default', 'claro')->save();

    $this->drupalLogin($this->rootUser);
  }

  /**
   * Check Varbase API Settings.
   */
  public function testCheckVarbaseApiSettings() {
    $assert_session = $this->assertSession();

    // Varbase API settings.
    $this->drupalGet('/admin/config/system/varbase/api');

    $varbase_api_settings_text = $this->t('Varbase API settings');
    $assert_session->pageTextContains($varbase_api_settings_text);

    $expose_view_json_text = $this->t('Expose a "View JSON" link in entity operations');
    $assert_session->pageTextContains($expose_view_json_text);

    $expose_view_api_doc_text = $this->t('Expose a "View API Documentation" link in bundle entity operations');
    $assert_session->pageTextContains($expose_view_api_doc_text);

    // Generate keys.
    $this->drupalGet('/admin/config/system/varbase/api/keys');

    $generate_keys_text = $this->t('Generate keys');
    $assert_session->pageTextContains($generate_keys_text);

    $destination_text = $this->t('Destination');
    $assert_session->pageTextContains($destination_text);

    $auto_enable = $this->t('Auto Enabled JSON:API Endpoints for Entity Types');

    $assert_session->pageTextContains($destination_text);

  }

  /**
   * Check Varbase API Generate keys.
   */
  public function testCheckVarbaseApiGenerateKeys() {
    $assert_session = $this->assertSession();

    // Generate keys.
    $this->drupalGet('/admin/config/system/varbase/api/keys');

    $generate_keys_text = $this->t('Generate keys');
    $assert_session->pageTextContains($generate_keys_text);

    $destination_text = $this->t('Destination');
    $assert_session->pageTextContains($destination_text);

    $auto_enable = $this->t('Auto Enabled JSON:API Endpoints for Entity Types');

    $assert_session->pageTextContains($destination_text);

  }

  /**
   * Check Varbase API Auto Enabled JSON:API Endpoints for Entity Types.
   */
  public function testCheckVarbaseApiAutoEnabledJsonApiEndpoints() {
    $assert_session = $this->assertSession();
    $auto_enable = $this->t('Auto Enabled JSON:API Endpoints for Entity Types');
    $assert_session->pageTextContains($auto_enable);

    $page = $this->getSession()->getPage();

    // Content type.
    $auto_enabled_entity_types_node_type = $page->findField('auto_enabled_entity_types[node_type]');
    $this->assertNotEmpty($auto_enabled_entity_types_node_type);
    $this->assertTrue($auto_enabled_entity_types_node_type->isChecked());

    // Content.
    $auto_enabled_entity_types_node = $page->findField('auto_enabled_entity_types[node]');
    $this->assertNotEmpty($auto_enabled_entity_types_node);
    $this->assertTrue($auto_enabled_entity_types_node->isChecked());

    // Taxonomy vocabulary.
    $auto_enabled_entity_types_taxonomy_vocabulary = $page->findField('auto_enabled_entity_types[taxonomy_vocabulary]');
    $this->assertNotEmpty($auto_enabled_entity_types_taxonomy_vocabulary);
    $this->assertTrue($auto_enabled_entity_types_taxonomy_vocabulary->isChecked());

    // Taxonomy term.
    $auto_enabled_entity_types_taxonomy_term = $page->findField('auto_enabled_entity_types[taxonomy_term]');
    $this->assertNotEmpty($auto_enabled_entity_types_taxonomy_term);
    $this->assertTrue($auto_enabled_entity_types_taxonomy_term->isChecked());

    // Media type.
    $auto_enabled_entity_types_media_type = $page->findField('auto_enabled_entity_types[media_type]');
    $this->assertNotEmpty($auto_enabled_entity_types_media_type);
    $this->assertTrue($auto_enabled_entity_types_media_type->isChecked());

    // Media.
    $auto_enabled_entity_types_media = $page->findField('auto_enabled_entity_types[media]');
    $this->assertNotEmpty($auto_enabled_entity_types_media);
    $this->assertTrue($auto_enabled_entity_types_media->isChecked());

  }

}
