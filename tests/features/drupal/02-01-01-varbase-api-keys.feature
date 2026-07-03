@varbase_api @keys
Feature: Varbase API - the Generate keys form
  As a site administrator
  I want the OAuth Generate keys form to render
  So that I can create the asymmetric key pair Simple OAuth needs

  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: The keys page loads with its heading and form
    When I am on "/admin/config/system/varbase/api/keys"
    Then "#oauth-key-form" should be visible
    And I should see "Generate keys"
    And I should not see "Page not found"
    And I should not see "The website encountered an unexpected error"
    And there should be no JavaScript errors

  Scenario: The keys form exposes its destination field and advanced options
    When I am on "/admin/config/system/varbase/api/keys"
    Then "#edit-dir" should be visible
    And "#edit-advanced" should be visible
    And the "Generate keys" button should be visible

  Scenario: The Generate keys task is linked from the settings page
    When I am on "/admin/config/system/varbase/api"
    Then I should see "Generate keys"
