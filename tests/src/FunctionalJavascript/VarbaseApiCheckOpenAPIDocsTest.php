<?php

namespace Drupal\Tests\varbase_api\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests Varbase API OpenAPI Documentation test.
 *
 * @group varbase_api
 */
class VarbaseApiCheckOpenAPIDocsTest extends WebDriverTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'olivero';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['varbase_api', 'node', 'taxonomy', 'media', 'user', 'block', 'block_content'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container->get('theme_installer')->install(['claro']);
    $this->config('system.theme')->set('admin', 'claro')->save();

    $this->drupalLogin($this->rootUser);
  }

  /**
   * Check OpenAPI Documentation.
   */
  public function testCheckOpenAPIDocs() {
    $assert_session = $this->assertSession();

    // OpenAPI Documentation
    $this->drupalGet('/admin/config/services/openapi/swagger/jsonapi');

    $page = $this->getSession()->getPage();

    $page->waitFor(10, function () use ($page) {
      return $page->find('css', "section.models.is-open");
    });

    $openapi_doc_text = $this->t('OpenAPI Documentation');
    $assert_session->pageTextContains($openapi_doc_text);

    // Content type.
    $content_type_text = $this->t('Content type');
    $assert_session->pageTextContains($content_type_text);

    // Content.
    $content_text = $this->t('Content - Basic page');
    $assert_session->pageTextContains($content_text);

    // Taxonomy vocabulary.
    $taxonomy_vocabulary_text = $this->t('Taxonomy vocabulary');
    $assert_session->pageTextContains($taxonomy_vocabulary_text);

    // Taxonomy term.
    $taxonomy_term_text = $this->t('Taxonomy term - Tags');
    $assert_session->pageTextContains($taxonomy_term_text);

    // Media type.
    $media_type_text = $this->t('Media type');
    $assert_session->pageTextContains($media_type_text);

    // Media - Image.
    $media_image_text = $this->t('Media - Image');
    $assert_session->pageTextContains($media_image_text);

    // Media - Video.
    $media_video_text = $this->t('Media - Video');
    $assert_session->pageTextContains($media_video_text);

    // Media - Remote video.
    $media_remote_video_text = $this->t('Media - Remote video');
    $assert_session->pageTextContains($media_remote_video_text);

    // Media - Audio.
    $media_audio_text = $this->t('Media - Audio');
    $assert_session->pageTextContains($media_audio_text);

  }

}
