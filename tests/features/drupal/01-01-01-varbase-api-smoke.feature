@smoke @varbase_api
Feature: Smoke - the Varbase API settings form
  As a site administrator
  I want the Varbase API settings form to render and save
  So that I can control how Varbase API exposes content over JSON:API

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: The settings page loads with its heading and form
    When I am on "/admin/config/system/varbase/api"
    Then "#varbase-api-settings-form" should be visible
    And I should see "Varbase API settings"
    And I should not see "Page not found"
    And I should not see "The website encountered an unexpected error"
    And there should be no JavaScript errors

  Scenario: The settings form exposes its controls
    When I am on "/admin/config/system/varbase/api"
    Then "#edit-entity-json" should be visible
    And "#edit-bundle-docs" should be visible
    And "#edit-auto-enabled-entity-types-node" should be visible
    And the "Save configuration" button should be visible

  Scenario: Saving the settings reports success
    When I am on "/admin/config/system/varbase/api"
    And I check the checkbox "#edit-entity-json"
    And I check the checkbox "#edit-bundle-docs"
    And I check the checkbox "#edit-auto-enabled-entity-types-node"
    And I press "Save configuration"
    Then I should see "The configuration options have been saved."
    And there should be no JavaScript errors
