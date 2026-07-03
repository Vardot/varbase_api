@varbase_api @jsonapi
Feature: Varbase API - the JSON:API entry point
  As an application integrating with the site
  I want the JSON:API entry point to respond
  So that I can ingest content over a standard API

  Scenario: The JSON:API entry point answers at the Varbase API path prefix
    # Varbase API ships a jsonapi_extras configuration that relocates the
    # JSON:API base path from /jsonapi to /api.
    Given I am a logged in user with the "Webmaster" user
    When I am on "/api"
    Then I should see "jsonapi"
    And I should see "version"
    And I should not see "Page not found"
    And I should not see "The website encountered an unexpected error"

  Scenario: The JSON:API entry point is publicly reachable for anonymous clients
    Given I am an anonymous visitor
    When I am on "/api"
    Then I should see "jsonapi"
