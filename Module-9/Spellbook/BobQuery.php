<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 09/20/2026
  Assignment: Module 9.2 - MySQLi Query and Form (BobQuery.php)
  Purpose: Searches the Spells_8_PHP table on whatever a visitor fills
           in on the form: part of a spell name, a school of magic, a
           spell level, a class that can cast it, and whether it can be
           cast as a ritual. The page posts back to itself, builds a
           WHERE clause out of only the filters that were actually
           used, and runs it as a prepared statement so the text typed
           into the form is never pasted into the SQL. Matching spells
           are drawn as the same cards the rest of the grimoire uses.
-->

<html>

  <head>

    <title>

      D&amp;D Spellbook - Search the Grimoire

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap">

    <link rel="stylesheet" type="text/css" href="style.css">

  </head>

  <body>

    <div class="container">

    <h1>Search the Grimoire</h1>

    <p class="scroll-intro">
      Fill in as much or as little as you like. Every field left blank
      is simply left out of the search, so an empty form returns the
      whole book.
    </p>

    <?php

      // spellsConfig.php supplies the connection helper, the list of
      // schools and classes offered below, and the shared display
      // helpers renderSpellCard() and printSpellbookNav().
      require("spellsConfig.php");

      // Print the "selected" marker when the option being written is
      // the one the visitor chose on the last search, so the form still
      // shows the search that produced the results underneath it.
      function printSelectedIfChosen($fieldName, $optionValue) {

        if (postedValue($fieldName) === $optionValue) {

          print(" selected=\"selected\"");
        }
      }

      // The same idea for the ritual checkbox.
      function printCheckedIfChosen($fieldName) {

        if (postedValue($fieldName) !== "") {

          print(" checked=\"checked\"");
        }
      }

      // A search only runs when the form has been submitted; the first
      // visit to the page just shows an empty form.
      $searchWasSubmitted = ($_SERVER["REQUEST_METHOD"] === "POST");

    ?>

    <form class="spell-form" action="BobQuery.php" method="post">

      <div class="form-row">

        <label class="form-label" for="name">Spell Name Contains</label>
        <input class="form-input" type="text" id="name" name="name" size="40"
               maxlength="80" value="<?php printFieldValue("name"); ?>">

      </div>

      <div class="form-row">

        <label class="form-label" for="school">School of Magic</label>

        <select class="form-input" id="school" name="school">

          <option value="">Any school</option>

          <?php

            // The school menu is filled from the same list that gives
            // each school its accent color, so the two can never
            // disagree about which schools exist.
            foreach (getSchoolColors() as $schoolName => $schoolColor) {

              print("<option value=\"" . escapeForHtml($schoolName) . "\"");
              printSelectedIfChosen("school", $schoolName);
              print(">" . escapeForHtml($schoolName) . "</option>\n");
            }

          ?>

        </select>

      </div>

      <div class="form-row">

        <label class="form-label" for="level">Spell Level</label>

        <select class="form-input" id="level" name="level">

          <option value="">Any level</option>

          <?php

            // Levels 0 through 9, printed as "Cantrip", "1st Level",
            // and so on by the same helper the spell cards use.
            for ($level = 0; $level <= 9; $level++) {

              print("<option value=\"" . $level . "\"");
              printSelectedIfChosen("level", (string) $level);
              print(">" . formatSpellLevel($level) . "</option>\n");
            }

          ?>

        </select>

      </div>

      <div class="form-row">

        <label class="form-label" for="spellClass">Castable By</label>

        <select class="form-input" id="spellClass" name="spellClass">

          <option value="">Any class</option>

          <?php

            foreach (getSpellClasses() as $className) {

              print("<option value=\"" . escapeForHtml($className) . "\"");
              printSelectedIfChosen("spellClass", $className);
              print(">" . escapeForHtml($className) . "</option>\n");
            }

          ?>

        </select>

      </div>

      <div class="form-row">

        <label class="form-label" for="ritualOnly">Rituals Only</label>

        <span class="form-check">
          <input type="checkbox" id="ritualOnly" name="ritualOnly" value="yes"
                 <?php printCheckedIfChosen("ritualOnly"); ?>>
          <label for="ritualOnly">Show only spells that can be cast as a ritual</label>
        </span>

      </div>

      <div class="form-actions">
        <input class="form-submit" type="submit" value="Search the Grimoire">
      </div>

    </form>

    <?php

      if ($searchWasSubmitted) {

        $conn = connectToSpellServer(true);

        // Each filter the visitor actually used adds one condition and,
        // where it needs one, one value to bind. Anything left blank
        // never reaches the SQL at all, which is what lets a single
        // query serve all sixteen combinations of the four menus.
        $conditions     = array();
        $boundValues    = array();
        $boundTypes     = "";
        $searchSummary  = array();

        $nameSearch = postedValue("name");

        if ($nameSearch !== "") {

          // LIKE with wildcards on both sides finds the text anywhere
          // in the name, so "fire" matches "Fireball".
          $conditions[]    = "name LIKE ?";
          $boundValues[]   = "%" . $nameSearch . "%";
          $boundTypes     .= "s";
          $searchSummary[] = "name containing \"" . $nameSearch . "\"";
        }

        $schoolSearch = postedValue("school");

        if ($schoolSearch !== "") {

          $conditions[]    = "school = ?";
          $boundValues[]   = $schoolSearch;
          $boundTypes     .= "s";
          $searchSummary[] = "school of " . $schoolSearch;
        }

        $levelSearch = postedValue("level");

        // Checked against "" rather than with empty(), because "0" is a
        // real choice here: it is the cantrip level.
        if ($levelSearch !== "") {

          $conditions[]    = "level = ?";
          $boundValues[]   = $levelSearch;
          $boundTypes     .= "i";
          $searchSummary[] = ($levelSearch == 0) ? "a cantrip level" : "a level of " . $levelSearch;
        }

        $classSearch = postedValue("spellClass");

        if ($classSearch !== "") {

          // The classes column holds a JSON array such as
          // ["Sorcerer","Wizard"], so the class is matched with its
          // surrounding quotation marks. Without them a search for
          // "Bard" would also match a class named "Bardic Scholar".
          $conditions[]    = "classes LIKE ?";
          $boundValues[]   = "%\"" . $classSearch . "\"%";
          $boundTypes     .= "s";
          $searchSummary[] = "castable by a " . $classSearch;
        }

        if (postedValue("ritualOnly") !== "") {

          // A fixed condition with no placeholder: nothing the visitor
          // typed goes into it.
          $conditions[]    = "ritual = 1";
          $searchSummary[] = "a ritual casting";
        }

        $sql = "SELECT * FROM " . SPELLS_TABLE;

        if (count($conditions) > 0) {

          $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY level ASC, name ASC";

        print("<h2>Search Results</h2>\n");

        if (count($searchSummary) === 0) {

          print("<p class=\"search-summary\">Searching for every spell in the grimoire.</p>\n");

        } else {

          print("<p class=\"search-summary\">Searching for spells with " .
                escapeForHtml(implode(", ", $searchSummary)) . ".</p>\n");
        }

        $statement = $conn->prepare($sql);

        if ($statement === false) {

          printStatusLine("Could not prepare the search: " . $conn->error, false);
          printStatusLine("If the table is missing, run BobCreateSpells.php first.", false);

        } else {

          // bind_param is only called when at least one filter carried
          // a value with it; a search with no filters, or with only the
          // ritual checkbox, has nothing to bind.
          if (count($boundValues) > 0) {

            $statement->bind_param($boundTypes, ...$boundValues);
          }

          if (!$statement->execute()) {

            printStatusLine("Could not run the search: " . $statement->error, false);

          } else {

            $result = $statement->get_result();

            if ($result->num_rows === 0) {

              print("<p class=\"no-results\">No spell in the grimoire matches that search. " .
                    "Try widening it, or scribe the spell yourself on the form page.</p>\n");

            } else {

              print("<div class=\"spell-grid\">\n");

              while ($spell = $result->fetch_assoc()) {

                renderSpellCard($spell);
              }

              print("</div>\n");

              $spellWord = ($result->num_rows === 1) ? " spell" : " spells";

              print("<p class=\"spell-count\">" . $result->num_rows . $spellWord . " found.</p>\n");
            }
          }

          $statement->close();
        }

        $conn->close();
      }

    ?>

    <?php printSpellbookNav("BobQuery.php"); ?>

    </div>

  </body>

</html>
