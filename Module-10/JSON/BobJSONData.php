<?php

  /*
    Name: Robert Breutzmann
    Date: 09/28/2026
    Assignment: Module 10.2 - JSON (BobJSONData.php)
    Purpose: Holds the lists of choices the character sheet form offers,
             kept in one external file so the form that displays them
             and BobJSON.php, which checks them, can never drift apart.
             Also holds the small helpers both pages share: escaping
             text for the page, printing drop-down options, and
             coloring a block of JSON for display.
  */

  // The species offered in the "Species" drop-down list. BobJSON.php
  // rejects any species that is not in this list.
  $speciesList = array(
    "Dragonborn",
    "Dwarf",
    "Elf",
    "Gnome",
    "Half-Elf",
    "Half-Orc",
    "Halfling",
    "Human",
    "Tiefling",
  );

  // The classes offered in the "Class" drop-down list.
  $characterClasses = array(
    "Barbarian",
    "Bard",
    "Cleric",
    "Druid",
    "Fighter",
    "Monk",
    "Paladin",
    "Ranger",
    "Rogue",
    "Sorcerer",
    "Warlock",
    "Wizard",
  );

  // The nine alignments, offered as radio buttons since a character
  // holds only one of them at a time.
  $alignments = array(
    "Lawful Good",
    "Neutral Good",
    "Chaotic Good",
    "Lawful Neutral",
    "True Neutral",
    "Chaotic Neutral",
    "Lawful Evil",
    "Neutral Evil",
    "Chaotic Evil",
  );

  // The six ability scores. The keys are the short names used for the
  // form fields and the values are the full names used as JSON keys,
  // so the exported sheet reads "strength" rather than "str".
  $abilityNames = array(
    "str" => "strength",
    "dex" => "dexterity",
    "con" => "constitution",
    "int" => "intelligence",
    "wis" => "wisdom",
    "cha" => "charisma",
  );

  // The languages offered as check boxes, since a character usually
  // speaks more than one. These become a JSON array.
  $languageList = array(
    "Common",
    "Dwarvish",
    "Elvish",
    "Giant",
    "Gnomish",
    "Goblin",
    "Halfling",
    "Orc",
    "Draconic",
    "Infernal",
    "Celestial",
    "Sylvan",
  );

  // Turn anything the player typed into text that is safe to print
  // inside a page, so a name like <script> is shown rather than run.
  // ENT_SUBSTITUTE swaps any byte that is not valid UTF-8 for a
  // placeholder character; without it, htmlspecialchars would return
  // an empty string and the error message quoting that text would
  // vanish.
  function escapeForHtml($value) {

    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
  }

  // Print one <option> tag for every entry in a list.
  function printOptions($optionList) {

    foreach ($optionList as $option) {

      print("<option value=\"" . escapeForHtml($option) . "\">" . escapeForHtml($option) . "</option>\n");
    }
  }

  // Wrap each piece of a JSON string in a <span> so the stylesheet can
  // color keys, strings, numbers, and true/false/null differently.
  // The pattern walks the JSON from left to right, and a quoted string
  // is always matched whole, so a digit or the word "true" inside a
  // string is never mistaken for a number or a boolean. Each matched
  // piece is escaped on its own before it is wrapped. What is left
  // between the matches is only braces, brackets, commas, colons, and
  // whitespace, none of which need escaping.
  function highlightJson($json) {

    $jsonTokenPattern = '/("(?:\\\\.|[^"\\\\])*")(\s*:)?|\b(true|false|null)\b|(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)/';

    return preg_replace_callback($jsonTokenPattern, function ($match) {

      // A quoted string followed by a colon is an object key.
      if (isset($match[2]) && $match[2] !== "") {

        return "<span class=\"json-key\">" . escapeForHtml($match[1]) . "</span>" . $match[2];
      }

      if ($match[1] !== "") {

        return "<span class=\"json-string\">" . escapeForHtml($match[1]) . "</span>";
      }

      if (isset($match[3]) && $match[3] !== "") {

        return "<span class=\"json-literal\">" . $match[3] . "</span>";
      }

      return "<span class=\"json-number\">" . $match[4] . "</span>";

    }, $json);
  }

?>
