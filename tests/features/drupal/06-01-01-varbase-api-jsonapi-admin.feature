@varbase_api @jsonapi @admin
Feature: Varbase API - JSON:API admin interface and services
  As a site administrator
  I want to review the JSON:API, JSON:API Extras, OpenAPI and Varbase API
  settings pages
  So that I can confirm the API services Varbase API wires up are available

  # Ported from the canonical Varbase suite
  # (tests/features/varbase/03-admin-management/03-05-json-api-admin-checks-...),
  # adapted to a plain Drupal Standard site provisioned by the
  # tests/varbase_api_test recipe.

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: JSON:API appears in the admin configuration index
    When I am on "/admin/config"
    Then I should see "JSON:API"
    And I should not see "Page not found"

  Scenario: The JSON:API configuration page lists the allowed operations
    When I am on "/admin/config/services/jsonapi"
    Then I should see "JSON:API"
    And I should see "Allowed operations"
    And I should not see "Page not found"

  Scenario: JSON:API Extras relocates the base path to "api"
    When I am on "/admin/config/services/jsonapi/extras"
    Then I should see "JSON:API Extras"
    And "#edit-path-prefix" should have value "api"
    And I should not see "Page not found"

  Scenario: The JSON:API resource overrides page renders
    When I am on "/admin/config/services/jsonapi/resource_types"
    Then I should see "JSON:API Resource overrides"
    And I should see "The following table shows the list of JSON:API resource types available."
    And I should not see "Page not found"

  Scenario: The Varbase API settings keep both operation links enabled
    When I am on "/admin/config/system/varbase/api"
    Then I should see "Varbase API settings"
    And the "#edit-entity-json" checkbox should be checked
    And the "#edit-bundle-docs" checkbox should be checked
    When I am on "/admin/config/system/varbase/api/keys"
    Then I should see "Path to the directory in which to store the generated keys."
    And I should not see "Page not found"

  Scenario: The OpenAPI resources page lists the REST and JSON:API documentation
    When I am on "/admin/config/services/openapi"
    Then I should see "OpenAPI Resources"
    And I should see "REST"
    And I should see "JSON:API"
    And I should not see "Page not found"

  Scenario: The JSON:API entry point answers at "/api"
    When I am on "/api"
    Then I should see "jsonapi"
    And I should not see "Page not found"

  Scenario: Varbase API adds the "View API Docs" operation to content
    Given I create an article titled "API Docs Article"
    When I am on "/admin/content"
    Then I should see "Content"
    And I should see "API Docs Article"
    And I should see "View API Docs"
    And I should not see "Page not found"
