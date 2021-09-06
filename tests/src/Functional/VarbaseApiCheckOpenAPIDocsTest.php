<?php

namespace Drupal\Tests\varbase_api\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests Varbase API OpenAPI Documentation test.
 *
 * @group varbase_api
 */
class VarbaseApiCheckOpenAPIDocsTest extends BrowserTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['varbase_api', 'node', 'taxonomy', 'media'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container->get('theme_installer')->install(['claro']);
    $this->config('system.theme')->set('default', 'claro')->save();

    $this->drupalLogin($this->rootUser);
  }

  /**
   * Check OpenAPI Documentation.
   */
  public function testCheckOpenAPIDocs() {
    $assert_session = $this->assertSession();

    // OpenAPI Documentation
    $this->drupalGet('/admin/config/services/openapi/swagger/jsonapi');

    $openapi_doc_text = $this->t('OpenAPI Documentation');
    $assert_session->pageTextContains($openapi_doc_text);

    // Content type.
    $content_type_text = $this->t('Content type');
    $assert_session->pageTextContains($content_type_text);

    // Content.
    $content_text = $this->t('Content -');
    $assert_session->pageTextContains($content_text);

    // Taxonomy vocabulary.
    $taxonomy_vocabulary_text = $this->t('Taxonomy vocabulary');
    $assert_session->pageTextContains($taxonomy_vocabulary_text);

    // Taxonomy term.
    $taxonomy_term_text = $this->t('Taxonomy term');
    $assert_session->pageTextContains($taxonomy_term_text);

    // Media type.
    $media_type_text = $this->t('Media type');
    $assert_session->pageTextContains($media_type_text);

    // Media.
    $media_text = $this->t('Media -');
    $assert_session->pageTextContains($media_text);

  }

}
