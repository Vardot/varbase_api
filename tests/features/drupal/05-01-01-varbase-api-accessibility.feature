@varbase_api @a11y
Feature: Varbase API - accessibility of the settings form
  As an administrator using assistive technology
  I want the Varbase API settings page to be a well-structured, labelled page
  So that I can configure the module with a screen reader

  Background:
    Given I am a logged in user with the "Webmaster" user
    And I am on "/admin/config/system/varbase/api"

  Scenario: The settings page has the expected landmarks and heading
    Then the page should have a main landmark
    And the page should have exactly one h1
    And the page should declare a language

  Scenario: Every settings control has an accessible label
    Then every form field should have an accessible label

  Scenario: The settings page passes an accessibility audit
    Then the page should have no critical accessibility violations
    And the page should have no serious accessibility violations
