<?php

  /*
    Name: Robert Breutzmann
    Date: 09/07/2026
    Assignment: Module 7 - Form (BobForm.php)
    Purpose: Holds the lists of choices the party application offers,
             kept in one external file so the form that displays the
             choices and the response page that checks them can never
             drift apart. Also holds the helper that escapes user input
             before it is printed back into a page.
  */

  // The classes offered in the "Calling" drop-down list. The response
  // page rejects any class that is not in this list.
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
  // may only hold one of them at a time.
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

  // The jobs a party member can cover, offered as check boxes since an
  // applicant may be willing to fill more than one of them.
  $partyRoles = array(
    "Front-Line Tank",
    "Healer",
    "Damage Dealer",
    "Scout",
    "Party Face",
    "Trap Finder",
  );

  // Turn anything the applicant typed into text that is safe to print
  // inside a page. Without this, an applicant could submit HTML or a
  // script tag as their character name and have the response page run
  // it, which is the cross-site scripting hole this module warns about.
  function escapeForHtml($value) {

    return htmlspecialchars($value, ENT_QUOTES);
  }

  // Print one <option> tag for every entry in a list, marking the one
  // that matches $selectedValue so a drop-down can be redrawn with the
  // applicant's earlier answer still chosen.
  function printOptions($optionList, $selectedValue) {

    foreach ($optionList as $option) {

      $selectedAttribute = ($option === $selectedValue) ? " selected='selected'" : "";

      print("<option value=\"" . escapeForHtml($option) . "\"" . $selectedAttribute . ">" . escapeForHtml($option) . "</option>\n");
    }
  }

?>
