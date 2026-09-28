<?php

  /*
    Name: Robert Breutzmann
    Date: 09/20/2026
    Assignment: Module 9.2 - MySQLi Query and Form (spellsConfig.php)
    Purpose: Holds everything the five spellbook pages share: the
             database connection settings, the helper that opens a
             connection, the helper that escapes user-facing text
             before it is printed, the seed data for the dozen spells
             the insert script loads, and the shared display helpers
             (the navigation bar, the status line, and the spell card)
             that the index, query, form, and read pages all draw with.
             Keeping all of it in one file means the five pages can
             never drift out of sync with one another.
             Carried forward from Module 8.2 and extended for the
             index, query, and form pages added in Module 9.2.
  */

  // As of PHP 8.1 mysqli reports a failed connection or query by
  // throwing an exception, which ends the page mid-HTML and, with
  // display_errors turned off, shows the visitor nothing but a blank
  // screen. Turning that reporting off restores the older behavior
  // these pages are written against: a failed call returns false and
  // leaves its message in $conn->error, so every page can catch the
  // problem itself and print a readable status line inside the
  // grimoire instead of dying.
  mysqli_report(MYSQLI_REPORT_OFF);

  // Local XAMPP MySQL connection settings. The database itself is
  // created by BobCreateSpells.php if it does not already exist.
  define("DB_HOST", "localhost");
  define("DB_USER", "student1");
  define("DB_PASS", "pass");
  define("DB_NAME", "DnDSpellsDB");
  define("SPELLS_TABLE", "Spells_8_PHP");

  // Turn anything pulled from the database (or typed by a user) into
  // text that is safe to print inside a page, the same guard Module 7
  // used against cross-site scripting.
  function escapeForHtml($value) {

    return htmlspecialchars($value, ENT_QUOTES);
  }

  // Open a connection to the MySQL server. Pass false for $selectDatabase
  // when the database itself might not exist yet, such as the first
  // step of BobCreateSpells.php, and true everywhere else.
  function connectToSpellServer($selectDatabase = true) {

    if ($selectDatabase) {

      $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    } else {

      $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    }

    if ($conn->connect_error) {

      die("ERROR: Unable to connect to the MySQL server: " . $conn->connect_error);
    }

    return $conn;
  }

  // The dozen spells BobInsertSpells.php loads into the table, written
  // out in the same shape as the Spell model this data is drawn from in
  // my Dragon Ledger campaign tracker: slug, sourceId, name, edition,
  // level, school, castingTime, range, components, material, duration,
  // concentration, ritual, classes, description, and higherLevels.
  // "classes" is stored as a JSON-encoded array, exactly the way Dragon
  // Ledger's importer serializes it before writing it to a text column.
  function getSpellSeedData() {

    return array(
      array(
        "slug" => "magic-missile",
        "sourceId" => "srd-5.1",
        "name" => "Magic Missile",
        "edition" => "5e",
        "level" => 1,
        "school" => "Evocation",
        "castingTime" => "1 action",
        "range" => "120 feet",
        "components" => "V, S",
        "material" => null,
        "duration" => "Instantaneous",
        "concentration" => false,
        "ritual" => false,
        "classes" => array("Sorcerer", "Wizard"),
        "description" => "Three glowing darts of magical force streak from your fingertips toward targets you choose within range, each dart striking unerringly and dealing force damage on impact.",
        "higherLevels" => "The spell creates one additional dart for each slot level above 1st.",
      ),
      array(
        "slug" => "cure-wounds",
        "sourceId" => "srd-5.1",
        "name" => "Cure Wounds",
        "edition" => "5e",
        "level" => 1,
        "school" => "Evocation",
        "castingTime" => "1 action",
        "range" => "Touch",
        "components" => "V, S",
        "material" => null,
        "duration" => "Instantaneous",
        "concentration" => false,
        "ritual" => false,
        "classes" => array("Bard", "Cleric", "Druid", "Paladin", "Ranger"),
        "description" => "A creature you touch regains a number of hit points, called back by the positive energy that flows through your hand.",
        "higherLevels" => "The healing increases by 1d8 for each slot level above 1st.",
      ),
      array(
        "slug" => "healing-word",
        "sourceId" => "srd-5.1",
        "name" => "Healing Word",
        "edition" => "5e",
        "level" => 1,
        "school" => "Evocation",
        "castingTime" => "1 bonus action",
        "range" => "60 feet",
        "components" => "V",
        "material" => null,
        "duration" => "Instantaneous",
        "concentration" => false,
        "ritual" => false,
        "classes" => array("Bard", "Cleric", "Druid"),
        "description" => "A creature of your choice that you can see within range regains hit points, called back from the brink by a single spoken word of power.",
        "higherLevels" => "The healing increases by 1d4 for each slot level above 1st.",
      ),
      array(
        "slug" => "shield",
        "sourceId" => "srd-5.1",
        "name" => "Shield",
        "edition" => "5e",
        "level" => 1,
        "school" => "Abjuration",
        "castingTime" => "1 reaction, when hit by an attack or targeted by magic missile",
        "range" => "Self",
        "components" => "V, S",
        "material" => null,
        "duration" => "1 round",
        "concentration" => false,
        "ritual" => false,
        "classes" => array("Sorcerer", "Wizard"),
        "description" => "An invisible barrier of magical force springs into being around you, raising your armor class and granting immunity to magic missile until the start of your next turn.",
        "higherLevels" => null,
      ),
      array(
        "slug" => "mage-armor",
        "sourceId" => "srd-5.1",
        "name" => "Mage Armor",
        "edition" => "5e",
        "level" => 1,
        "school" => "Abjuration",
        "castingTime" => "1 action",
        "range" => "Touch",
        "components" => "V, S, M",
        "material" => "A piece of cured leather",
        "duration" => "8 hours",
        "concentration" => false,
        "ritual" => false,
        "classes" => array("Sorcerer", "Wizard"),
        "description" => "A protective magical force surrounds a willing creature you touch, setting its base armor class for as long as the spell lasts, so long as it wears no armor of its own.",
        "higherLevels" => null,
      ),
      array(
        "slug" => "detect-magic",
        "sourceId" => "srd-5.1",
        "name" => "Detect Magic",
        "edition" => "5e",
        "level" => 1,
        "school" => "Divination",
        "castingTime" => "1 action",
        "range" => "Self",
        "components" => "V, S",
        "material" => null,
        "duration" => "Concentration, up to 10 minutes",
        "concentration" => true,
        "ritual" => true,
        "classes" => array("Bard", "Cleric", "Druid", "Paladin", "Ranger", "Sorcerer", "Wizard"),
        "description" => "For the duration you sense the presence of magic within 30 feet of you, and by spending your action can see a faint aura around any visible creature or object that carries it.",
        "higherLevels" => null,
      ),
      array(
        "slug" => "misty-step",
        "sourceId" => "srd-5.1",
        "name" => "Misty Step",
        "edition" => "5e",
        "level" => 2,
        "school" => "Conjuration",
        "castingTime" => "1 bonus action",
        "range" => "Self",
        "components" => "V",
        "material" => null,
        "duration" => "Instantaneous",
        "concentration" => false,
        "ritual" => false,
        "classes" => array("Sorcerer", "Warlock", "Wizard"),
        "description" => "You are surrounded by a brief flicker of silvery mist and teleport up to 30 feet to an unoccupied space that you can see.",
        "higherLevels" => null,
      ),
      array(
        "slug" => "fireball",
        "sourceId" => "srd-5.1",
        "name" => "Fireball",
        "edition" => "5e",
        "level" => 3,
        "school" => "Evocation",
        "castingTime" => "1 action",
        "range" => "150 feet",
        "components" => "V, S, M",
        "material" => "A tiny ball of bat guano and sulfur",
        "duration" => "Instantaneous",
        "concentration" => false,
        "ritual" => false,
        "classes" => array("Sorcerer", "Wizard"),
        "description" => "A bright streak flashes to a point you choose and erupts into a roaring ball of flame, dealing fire damage to everyone caught in a 20-foot-radius sphere and igniting flammable objects that are not worn or carried.",
        "higherLevels" => "The damage increases by 1d6 for each slot level above 3rd.",
      ),
      array(
        "slug" => "counterspell",
        "sourceId" => "srd-5.1",
        "name" => "Counterspell",
        "edition" => "5e",
        "level" => 3,
        "school" => "Abjuration",
        "castingTime" => "1 reaction, when a creature within 60 feet starts casting a spell",
        "range" => "60 feet",
        "components" => "S",
        "material" => null,
        "duration" => "Instantaneous",
        "concentration" => false,
        "ritual" => false,
        "classes" => array("Sorcerer", "Warlock", "Wizard"),
        "description" => "You attempt to interrupt a creature in the process of casting a spell. If the creature was casting a spell of 3rd level or lower, its spell fails and has no effect.",
        "higherLevels" => "If cast using a slot of 4th level or higher, the interrupted spell fails only if its level is less than or equal to the level of the slot you used.",
      ),
      array(
        "slug" => "haste",
        "sourceId" => "srd-5.1",
        "name" => "Haste",
        "edition" => "5e",
        "level" => 3,
        "school" => "Transmutation",
        "castingTime" => "1 action",
        "range" => "30 feet",
        "components" => "V, S, M",
        "material" => "A shaving of licorice root",
        "duration" => "Concentration, up to 1 minute",
        "concentration" => true,
        "ritual" => false,
        "classes" => array("Sorcerer", "Wizard"),
        "description" => "Choose a willing creature you can see within range. Until the spell ends, the target's speed is doubled, it gains a bonus to armor class, and it gains an extra action on each of its turns.",
        "higherLevels" => null,
      ),
      array(
        "slug" => "polymorph",
        "sourceId" => "srd-5.1",
        "name" => "Polymorph",
        "edition" => "5e",
        "level" => 4,
        "school" => "Transmutation",
        "castingTime" => "1 action",
        "range" => "60 feet",
        "components" => "V, S, M",
        "material" => "A caterpillar cocoon",
        "duration" => "Concentration, up to 1 hour",
        "concentration" => true,
        "ritual" => false,
        "classes" => array("Bard", "Druid", "Sorcerer", "Wizard"),
        "description" => "This spell transforms a creature you can see within range into a new form for the duration, replacing its game statistics with those of the chosen beast while its mind and personality remain.",
        "higherLevels" => null,
      ),
      array(
        "slug" => "eldritch-blast",
        "sourceId" => "srd-5.1",
        "name" => "Eldritch Blast",
        "edition" => "5e",
        "level" => 0,
        "school" => "Evocation",
        "castingTime" => "1 action",
        "range" => "120 feet",
        "components" => "V, S",
        "material" => null,
        "duration" => "Instantaneous",
        "concentration" => false,
        "ritual" => false,
        "classes" => array("Warlock"),
        "description" => "A beam of crackling energy streaks toward a creature within range, striking with force damage on a successful hit.",
        "higherLevels" => "The spell creates more than one beam at higher character levels: two beams at 5th level, three at 11th, and four at 17th.",
      ),
    );
  }
  // The eight schools of magic, each with the accent color its spell
  // cards, level badges, and school labels are drawn in. The query and
  // form pages use the keys of this array to fill their school menus,
  // so a school can never be offered on a form that the cards do not
  // know how to color.
  function getSchoolColors() {

    return array(
      "Abjuration"    => "#3f6fa8",
      "Conjuration"   => "#3f8f5f",
      "Divination"    => "#b8952f",
      "Enchantment"   => "#c1548b",
      "Evocation"     => "#a5303c",
      "Illusion"      => "#7a4fc2",
      "Necromancy"    => "#4a4a4a",
      "Transmutation" => "#c07830",
    );
  }

  // Every class that can hold a spell on its list in 5th Edition. The
  // query page offers these as a search filter and the form page offers
  // them as checkboxes.
  function getSpellClasses() {

    return array(
      "Artificer", "Bard", "Cleric", "Druid", "Paladin",
      "Ranger", "Sorcerer", "Warlock", "Wizard",
    );
  }

  // Look up the accent color for a school, falling back to the generic
  // arcane purple for any school not in the list above.
  function getSchoolColor($school) {

    $schoolColors = getSchoolColors();

    return isset($schoolColors[$school]) ? $schoolColors[$school] : "#8a4fff";
  }

  // A whole number spell level reads as "Cantrip" at 0 and as
  // "1st Level", "2nd Level", and so on above that.
  function formatSpellLevel($level) {

    if ($level == 0) {

      return "Cantrip";
    }

    $suffixes = array(1 => "st", 2 => "nd", 3 => "rd");
    $suffix = isset($suffixes[$level]) ? $suffixes[$level] : "th";

    return $level . $suffix . " Level";
  }

  // Turn a spell name into the hyphenated identifier used for the slug
  // primary key, so "Chromatic Orb" is stored as "chromatic-orb". Any
  // character that is not a letter or a digit becomes a hyphen, and
  // runs of hyphens are collapsed to one.
  function makeSpellSlug($name) {

    $slug = strtolower(trim($name));
    $slug = preg_replace("/[^a-z0-9]+/", "-", $slug);

    return trim($slug, "-");
  }

  // Print one line of status, marked with a success or failure rune
  // depending on whether the step it describes worked. The create,
  // insert, index, and form pages all report with this.
  function printStatusLine($message, $succeeded) {

    $cssClass = $succeeded ? "ok" : "fail";
    print("<p class=\"status-line " . $cssClass . "\">" . escapeForHtml($message) . "</p>\n");
  }

  // Print the navigation bar that every page in the spellbook carries,
  // so any page can be reached from any other. The page currently being
  // viewed is marked so it is obvious where in the book the reader is.
  function printSpellbookNav($currentFile) {

    $pages = array(
      "BobIndex.php"        => "Index",
      "BobCreateSpells.php" => "1. Bind the Tome",
      "BobInsertSpells.php" => "2. Ink the Spells",
      "BobReadSpells.php"   => "3. Read the Grimoire",
      "BobQuery.php"        => "Search the Grimoire",
      "BobForm.php"         => "Scribe a New Spell",
    );

    print("<div class=\"actions\">\n");

    foreach ($pages as $fileName => $linkText) {

      $cssClass = ($fileName === $currentFile) ? "spell-link current" : "spell-link";

      print("<a class=\"" . $cssClass . "\" href=\"" . $fileName . "\">" . escapeForHtml($linkText) . "</a>\n");
    }

    print("</div>\n");
  }

  // Print one spell as a card: name and level badge, school, the four
  // casting statistics, the classes that can learn it, and its
  // description. The read page, the query page, and the confirmation on
  // the form page all display spells with this one function, so a spell
  // looks the same everywhere in the book. $spell is one associative
  // row as fetched from the Spells_8_PHP table.
  function renderSpellCard($spell) {

    $accentColor = getSchoolColor($spell["school"]);

    // "classes" is stored as a JSON array in a text column, so it has
    // to be decoded before the class tags can be printed.
    $classList = json_decode($spell["classes"], true);
    $classList = is_array($classList) ? $classList : array();

    print("<div class=\"spell-card\" style=\"--card-accent: " . $accentColor . ";\">\n");

    print("<div class=\"spell-card-header\">");
    print("<span class=\"spell-name\">" . escapeForHtml($spell["name"]) . "</span>");
    print("<span class=\"spell-level-badge\">" . escapeForHtml(formatSpellLevel($spell["level"])) . "</span>");
    print("</div>\n");

    print("<p class=\"spell-school\">" . escapeForHtml($spell["school"]));

    if ($spell["ritual"]) {

      print(" (ritual)");
    }

    print("</p>\n");

    print("<ul class=\"spell-meta\">");
    print("<li><span class=\"meta-label\">Casting Time</span>" . escapeForHtml($spell["castingTime"]) . "</li>");
    print("<li><span class=\"meta-label\">Range</span>" . escapeForHtml($spell["range"]) . "</li>");

    // The material component, when a spell has one, reads as a
    // parenthetical after the component letters: "V, S, M (a pinch of
    // soot)".
    $componentsLine = $spell["components"];

    if (!empty($spell["material"])) {

      $componentsLine .= " (" . $spell["material"] . ")";
    }

    print("<li><span class=\"meta-label\">Components</span>" . escapeForHtml($componentsLine) . "</li>");

    // The duration text itself already reads "Concentration, up to ..."
    // for any spell that needs it, so it is printed as-is; the
    // "concentration" column is only consulted for filtering on the
    // query page.
    print("<li><span class=\"meta-label\">Duration</span>" . escapeForHtml($spell["duration"]) . "</li>");
    print("</ul>\n");

    print("<div class=\"spell-tags\">");

    foreach ($classList as $className) {

      print("<span class=\"spell-tag\">" . escapeForHtml($className) . "</span>");
    }

    print("</div>\n");

    print("<p class=\"spell-description\">" . escapeForHtml($spell["description"]) . "</p>\n");

    if (!empty($spell["higherLevels"])) {

      print("<p class=\"spell-higher-levels\">At Higher Levels: " . escapeForHtml($spell["higherLevels"]) . "</p>\n");
    }

    print("</div>\n");
  }

  // Read one field out of a submitted form, trimmed of stray spaces,
  // returning an empty string when the field was not submitted at all.
  // Every value typed on the query and form pages comes through here
  // before it is looked at.
  function postedValue($fieldName) {

    return isset($_POST[$fieldName]) ? trim($_POST[$fieldName]) : "";
  }

  // Print the value a visitor already typed back into a form field, so
  // a search or a rejected new spell does not lose what was entered.
  function printFieldValue($fieldName) {

    print(escapeForHtml(postedValue($fieldName)));
  }

?>
