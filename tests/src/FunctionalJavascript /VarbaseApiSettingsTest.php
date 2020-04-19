<?php

namespace Drupal\Tests\varbase_api\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests Varbase API Settings test.
 *
 * @group varbase_api
 */
class VarbaseApiSettingsTest extends WebDriverTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['varbase_api'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->config('varbase_api.settings')
      ->set('entity_json', TRUE)
      ->set('bundle_docs', TRUE)
      ->save();

    $this->drupalCreateContentType(['type' => 'test']);
    $this->drupalCreateNode(['type' => 'test']);

    $this->container->get('entity_type.bundle.info')->clearCachedBundles();
  }

  /**
   * Check Varbase API Settings.
   */
  public function testCheckVarbaseApiSettings() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    // Varbase API settings.
    $this->drupalGet('/admin/config/system/varbase/api');
    $assert_session->waitForElementVisible('css', '.varbase-api-settings-form');

    $varbase_api_settings_text = $this->t('Varbase API settings');
    $assert_session->pageTextContains($varbase_api_settings_text);

    $expose_view_json_text = $this->t('Expose a "View JSON" link in entity operations');
    $assert_session->pageTextContains($expose_view_json_text);

    $expose_view_api_doc_text = $this->t('Expose a "View API Documentation" link in bundle entity operations');
    $assert_session->pageTextContains($expose_view_api_doc_text);

    $save_config_text = $this->t('Save configuration');
    $page->clickLink($save_config_text);

    $save_config_message_text = $this->t('The configuration options have been saved');
    $assert_session->pageTextContains($save_config_message_text);

    // Generate keys.
    $this->drupalGet('/admin/config/system/varbase/api/keys');
    $assert_session->waitForElementVisible('css', '.oauth-key-form');

    $generate_keys_text = $this->t('Generate keys');
    $assert_session->pageTextContains($generate_keys_text);

    $destination_text = $this->t('Destination');
    $assert_session->pageTextContains($destination_text);

  }

  /**
   * Tests API documentation and JSON representations are exposed for entities.
   */
  public function testBasicUsage() {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/content');
    $page->clickLink('View JSON');
    $assert_session->statusCodeEquals(200);

    $this->drupalGet('/admin/structure/types');
    $this->clickLink('View JSON');
    $assert_session->statusCodeEquals(200);

    $this->drupalGet('/api-docs');
    $assert_session->statusCodeEquals(200);

    $this->drupalGet('/admin/structure/types');
    $this->clickLink('View API documentation');
    $assert_session->statusCodeEquals(200);
  }

}
