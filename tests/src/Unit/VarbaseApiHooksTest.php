<?php

namespace Drupal\Tests\varbase_api\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\varbase_api\Hook\VarbaseApiHooks;

/**
 * Unit tests for the Varbase API object-oriented hooks.
 *
 * Functional and browser coverage lives in the varbase-e2e suite
 * (tests/features/drupal). These PHP tests only exercise unit-testable logic.
 *
 * @coversDefaultClass \Drupal\varbase_api\Hook\VarbaseApiHooks
 * @group varbase_api
 */
class VarbaseApiHooksTest extends UnitTestCase {

  /**
   * Builds the hook service with mocked dependencies.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user mock to inject.
   *
   * @return \Drupal\varbase_api\Hook\VarbaseApiHooks
   *   The hook service under test.
   */
  protected function buildHooks(AccountProxyInterface $current_user): VarbaseApiHooks {
    return new VarbaseApiHooks(
      $this->createMock(ConfigFactoryInterface::class),
      $this->createMock(RouteBuilderInterface::class),
      $this->createMock(RouteProviderInterface::class),
      $this->createMock(ConfigInstallerInterface::class),
      $current_user,
      $this->createMock(ResourceTypeRepositoryInterface::class),
      $this->createMock(EntityTypeManagerInterface::class),
      $this->createMock(LoggerChannelFactoryInterface::class),
    );
  }

  /**
   * The entity operations are empty when the user lacks the permissions.
   *
   * @covers ::entityOperation
   */
  public function testEntityOperationReturnsNoOperationsWithoutPermissions(): void {
    $current_user = $this->createMock(AccountProxyInterface::class);
    $current_user->method('hasPermission')->willReturn(FALSE);

    $hooks = $this->buildHooks($current_user);
    $entity = $this->createMock(EntityInterface::class);

    $this->assertSame([], $hooks->entityOperation($entity));
  }

}
