/**
 * Typeahead
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const isRtl = document.documentElement.dir === 'rtl';

  // String Matcher function
  const substringMatcher = strs => {
    return (q, cb) => {
      const matches = [];
      const substrRegex = new RegExp(q, 'i');

      strs.forEach(str => {
        if (substrRegex.test(str)) {
          matches.push(str);
        }
      });

      cb(matches);
    };
  };

  // Data
  const states = [
    'Alabama',
    'Alaska',
    'Arizona',
    'Arkansas',
    'California',
    'Colorado',
    'Connecticut',
    'Delaware',
    'Florida',
    'Georgia',
    'Hawaii',
    'Idaho',
    'Illinois',
    'Indiana',
    'Iowa',
    'Kansas',
    'Kentucky',
    'Louisiana',
    'Maine',
    'Maryland',
    'Massachusetts',
    'Michigan',
    'Minnesota',
    'Mississippi',
    'Missouri',
    'Montana',
    'Nebraska',
    'Nevada',
    'New Hampshire',
    'New Jersey',
    'New Mexico',
    'New York',
    'North Carolina',
    'North Dakota',
    'Ohio',
    'Oklahoma',
    'Oregon',
    'Pennsylvania',
    'Rhode Island',
    'South Carolina',
    'South Dakota',
    'Tennessee',
    'Texas',
    'Utah',
    'Vermont',
    'Virginia',
    'Washington',
    'West Virginia',
    'Wisconsin',
    'Wyoming'
  ];

  // Set RTL if applicable
  if (isRtl) {
    document.querySelectorAll('.typeahead').forEach(el => {
      el.setAttribute('dir', 'rtl');
    });
  }

  // Basic
  // --------------------------------------------------------------------
  $('.typeahead').typeahead(
    {
      hint: !isRtl,
      highlight: true,
      minLength: 1
    },
    {
      name: 'states',
      source: substringMatcher(states)
    }
  );

  var bloodhoundBasicExample = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.whitespace,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    local: states
  });

  // Bloodhound Example
  // --------------------------------------------------------------------
  $('.typeahead-bloodhound').typeahead(
    {
      hint: !isRtl,
      highlight: true,
      minLength: 1
    },
    {
      name: 'states',
      source: bloodhoundBasicExample
    }
  );

  var prefetchExample = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.whitespace,
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: `${assetsPath}json/typeahead.json`
  });

  // Prefetch Example
  // --------------------------------------------------------------------
  $('.typeahead-prefetch').typeahead(
    {
      hint: !isRtl,
      highlight: true,
      minLength: 1
    },
    {
      name: 'states',
      source: prefetchExample
    }
  );

  // Render Default Suggestions
  function renderDefaults(q, sync) {
    if (q === '') {
      sync(prefetchExample.get('Alaska', 'New York', 'Washington'));
    } else {
      prefetchExample.search(q, sync);
    }
  }

  // Default Suggestions Example
  // --------------------------------------------------------------------
  $('.typeahead-default-suggestions').typeahead(
    {
      hint: !isRtl,
      highlight: true,
      minLength: 0
    },
    {
      name: 'states',
      source: renderDefaults
    }
  );

  // Initialize Bloodhound for custom template
  const customTemplate = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: `${assetsPath}json/typeahead-data-2.json`
  });

  // Select the input element for typeahead
  const customTemplateInput = document.querySelector('.typeahead-custom-template');

  // Initialize Typeahead with custom templates
  if (customTemplateInput) {
    $(customTemplateInput).typeahead(
      {
        highlight: true,
        hint: !isRtl
      },
      {
        name: 'best-movies',
        display: 'value',
        source: customTemplate,
        templates: {
          empty: `
          <div class="empty-message px-4">
            Unable to find any Best Picture winners that match the current query
          </div>
        `,
          suggestion: data => `
          <div>
            <span class="fw-medium">${data.value}</span> – ${data.year}
          </div>
        `
        }
      }
    );
  }

  // Teams Data
  const teamsData = {
    nba: [
      { team: 'Boston Celtics' },
      { team: 'Dallas Mavericks' },
      { team: 'Brooklyn Nets' },
      { team: 'Houston Rockets' },
      { team: 'New York Knicks' },
      { team: 'Memphis Grizzlies' },
      { team: 'Philadelphia 76ers' },
      { team: 'New Orleans Hornets' },
      { team: 'Toronto Raptors' },
      { team: 'San Antonio Spurs' },
      { team: 'Chicago Bulls' },
      { team: 'Denver Nuggets' },
      { team: 'Cleveland Cavaliers' },
      { team: 'Minnesota Timberwolves' },
      { team: 'Detroit Pistons' },
      { team: 'Portland Trail Blazers' },
      { team: 'Indiana Pacers' },
      { team: 'Oklahoma City Thunder' },
      { team: 'Milwaukee Bucks' },
      { team: 'Utah Jazz' },
      { team: 'Atlanta Hawks' },
      { team: 'Golden State Warriors' },
      { team: 'Charlotte Bobcats' },
      { team: 'Los Angeles Clippers' },
      { team: 'Miami Heat' },
      { team: 'Los Angeles Lakers' },
      { team: 'Orlando Magic' },
      { team: 'Phoenix Suns' },
      { team: 'Washington Wizards' },
      { team: 'Sacramento Kings' }
    ],
    nhl: [
      { team: 'New Jersey Devils' },
      { team: 'New York Islanders' },
      { team: 'New York Rangers' },
      { team: 'Philadelphia Flyers' },
      { team: 'Pittsburgh Penguins' },
      { team: 'Chicago Blackhawks' },
      { team: 'Columbus Blue Jackets' },
      { team: 'Detroit Red Wings' },
      { team: 'Nashville Predators' },
      { team: 'St. Louis Blues' },
      { team: 'Boston Bruins' },
      { team: 'Buffalo Sabres' },
      { team: 'Montreal Canadiens' },
      { team: 'Ottawa Senators' },
      { team: 'Toronto Maple Leafs' },
      { team: 'Calgary Flames' },
      { team: 'Colorado Avalanche' },
      { team: 'Edmonton Oilers' },
      { team: 'Minnesota Wild' },
      { team: 'Vancouver Canucks' },
      { team: 'Carolina Hurricanes' },
      { team: 'Florida Panthers' },
      { team: 'Tampa Bay Lightning' },
      { team: 'Washington Capitals' },
      { team: 'Winnipeg Jets' },
      { team: 'Anaheim Ducks' },
      { team: 'Dallas Stars' },
      { team: 'Los Angeles Kings' },
      { team: 'Phoenix Coyotes' },
      { team: 'San Jose Sharks' }
    ]
  };

  // Function to create a Bloodhound instance
  const createBloodhound = teamData => {
    return new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('team'),
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      local: teamData
    });
  };

  // Bloodhound Instances
  const nbaExample = createBloodhound(teamsData.nba);
  const nhlExample = createBloodhound(teamsData.nhl);

  // Dataset Configurations
  const datasets = [
    {
      name: 'nba-teams',
      source: nbaExample,
      display: 'team',
      templates: {
        header: '<h4 class="league-name border-bottom mb-0 mx-3 mt-3 pb-2">NBA Teams</h4>'
      }
    },
    {
      name: 'nhl-teams',
      source: nhlExample,
      display: 'team',
      templates: {
        header: '<h4 class="league-name border-bottom mb-0 mx-3 mt-3 pb-2">NHL Teams</h4>'
      }
    }
  ];

  // Initialize Typeahead
  const multiDatasetInput = document.querySelector('.typeahead-multi-datasets');
  if (multiDatasetInput) {
    $(multiDatasetInput).typeahead(
      {
        hint: !isRtl,
        highlight: true,
        minLength: 0
      },
      ...datasets
    );
  }
});
