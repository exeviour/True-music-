Feature: Ampache API
  In order to browse my music collection
  As a user
  I need to be able to list my music files

  Scenario: List all artists
    Given I am logged in with an auth token
    When I request the "artists" resource
    Then I should get:
      | name                   | albums | songs |
      | Diablo Swing Orchestra | 1      | 13    |
      | Pascalb                | 1      | 8     |
      | Simon Bowman           | 1      | 6     |
      | SimonBowman            | 7      | 64    |

  Scenario: List filtered artists
    Given I am logged in with an auth token
    When I specify the parameter "filter" with value "Pascalb"
    And I request the "artists" resource
    Then I should get:
      | name                   | albums | songs |
      | Pascalb                | 1      | 8     |

  Scenario: List exact filtered artists
    Given I am logged in with an auth token
    When I specify the parameter "filter" with value "Pascal"
    And I specify the parameter "exact" with value "true"
    And I request the "artists" resource
    Then I should get:
      | name                   | albums | songs |

  Scenario: List all albums
    Given I am logged in with an auth token
    When I request the "albums" resource
    Then I should get:
      | name                                                | artist                 | tracks | year |
      | Backwards Original Film Score                       | SimonBowman            | 14     | 2013 |
      | Francesco da Milano (1497-1543)                     | Simon Bowman           | 6      | 2013 |
      | Grace Original Film Score                           | SimonBowman            | 5      | 2013 |
      | Instrumental Film Music Vol 1                       | SimonBowman            | 10     | 2013 |
      | NDP Philo Cafe Original Film S                      | SimonBowman            | 12     | 2014 |
      | Nuance                                              | Pascalb                | 8      | 2006 |
      | Orchestral Film Music Vol 1                         | SimonBowman            | 10     | 2013 |
      | The Butcher s Ballroom                              | Diablo Swing Orchestra | 13     | 2009 |
      | The Crucible Original Theatre                       | SimonBowman            | 8      | 2014 |
      | The Visitor Original Film Scor                      | SimonBowman            | 5      | 2013 |

  Scenario: List filtered albums
    Given I am logged in with an auth token
    When I specify the parameter "filter" with value "Nuance"
    And I request the "albums" resource
    Then I should get:
      | name                                                | artist                 | tracks | year |
      | Nuance                                              | Pascalb                | 8      | 2006 |

  Scenario: List exact filtered albums
    Given I am logged in with an auth token
    When I specify the parameter "filter" with value "Nuance"
    And I specify the parameter "exact" with value "true"
    And I request the "albums" resource
    Then I should get:
      | name                                                | artist                 | tracks | year |
      | Nuance                                              | Pascalb                | 8      | 2006 |

  Scenario: List 10 songs
    Given I am logged in with an auth token
    When I specify the parameter "limit" with value "10"
    And I request the "songs" resource
    Then I should get:
      | title                          | artist      | album                             | time | track |
      | Adrift                         | SimonBowman | Orchestral Film Music Vol 1       | 114  | 4     |
      | Anniversary Meal               | SimonBowman | Backwards Original Film Score     | 101  | 3     |
      | Arrival and Transformation One | SimonBowman | The Visitor Original Film Scor    | 91   | 1     |
      | Ashes                          | SimonBowman | Instrumental Film Music Vol 1     | 122  | 4     |
      | Avaunt                         | SimonBowman | NDP Philo Cafe Original Film S    | 77   | 6     |
      | Aç                             | Pascalb     | Nuance                            | 187  | 7     |
      | Backwards                      | SimonBowman | Backwards Original Film Score     | 52   | 8     |
      | Bagatelle                      | SimonBowman | Instrumental Film Music Vol 1     | 179  | 7     |
      | Balrog Boogie                  | Diablo Swing Orchestra | The Butcher s Ballroom | 234  | 1     |
      | Barley Sky                     | SimonBowman | Instrumental Film Music Vol 1     | 155  | 6     |

  Scenario: List songs that contain "di"
    Given I am logged in with an auth token
    When I specify the parameter "filter" with value "di"
    And I request the "songs" resource
    Then I should get:
      | title                            | artist                 | album                          | time | track |
      | Divertimento                     | SimonBowman            | Instrumental Film Music Vol 1  | 180  | 8     |
      | Final Transformation and Credits | SimonBowman            | The Visitor Original Film Scor | 124  | 5     |
      | Médiane                          | Pascalb                | Nuance                         | 203  | 1     |
      | Wedding March for a Bullet       | Diablo Swing Orchestra | The Butcher s Ballroom         | 194  | 9     |
      | Zodiac Virtues                   | Diablo Swing Orchestra | The Butcher s Ballroom         | 288  | 11    |

  Scenario: List songs that contain "Mediane"
    Given I am logged in with an auth token
    When I specify the parameter "filter" with value "Médiane"
    And I specify the parameter "exact" with value "true"
    And I request the "songs" resource
    Then I should get:
      | title                            | artist                 | album                          | time | track |
      | Médiane                          | Pascalb                | Nuance                         | 203  | 1     |
