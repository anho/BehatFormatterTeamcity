# BehatFormatterTeamcity

## Load the extension
Add the extension to your behat.yml like this:

    default:
      extensions:
        Behat\TeamCityFormatter\TeamCityFormatterExtension: ~

## Use in behat.yml
Add the formatter to your suite:

    some_suite:
      formatters:
        teamcity:

## Use on command line
Just use the key ``teamcity`` as your formatter:

    behat -f teamcity