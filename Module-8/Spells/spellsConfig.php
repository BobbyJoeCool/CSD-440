<?php

  /*
    Name: Robert Breutzmann
    Date: 09/14/2026
    Assignment: Module 8.2 - PHP and MySQL (spellsConfig.php)
    Purpose: Holds everything the three spellbook scripts share, the same
             way partyData.php held the shared lists for Module 7: the
             database connection settings, the helper that opens a
             connection, the helper that escapes user-facing text before
             it is printed, and the seed data for the dozen spells the
             insert script loads into the database. Keeping the spell
             data here means the create, insert, and read scripts can
             never drift out of sync with one another.
  */

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

?>
