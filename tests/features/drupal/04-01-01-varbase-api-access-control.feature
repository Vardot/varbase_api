@varbase_api @access
Feature: Varbase API - access control on the settings pages
  As the site owner
  I want the Varbase API settings pages locked behind a permission
  So that only trusted administrators can change them or generate keys

  Scenario: An anonymous visitor cannot reach the settings form
    Given I am an anonymous visitor
    When I am on "/admin/config/system/varbase/api"
    Then I should see "Access denied"

  Scenario: An anonymous visitor cannot reach the Generate keys form
    Given I am an anonymous visitor
    When I am on "/admin/config/system/varbase/api/keys"
    Then I should see "Access denied"

  Scenario: The Webmaster can reach the settings form
    Given I am a logged in user with the "Webmaster" user
    When I am on "/admin/config/system/varbase/api"
    Then "#varbase-api-settings-form" should be visible
