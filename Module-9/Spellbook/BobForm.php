<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 09/20/2026
  Assignment: Module 9.2 - MySQLi Query and Form (BobForm.php)
  Purpose: Adds a spell of the visitor's own to the Spells_8_PHP table.
           The page posts back to itself, checks every field before it
           goes anywhere near the database, and writes the accepted
           spell with a prepared statement. A rejected entry comes back
           with a list of what needs fixing and everything already typed
           still in place; an accepted one is read back out of the
           table and shown as a finished spell card, which proves the
           row really landed.
-->

<html>

  <head>

    <title>

      D&amp;D Spellbook - Scribe a New Spell

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap">

    <link rel="stylesheet" type="text/css" href="style.css">

  </head>

  <body>

    <div class="container">

    <h1>Scribe a New Spell</h1>

    <p class="scroll-intro">
      Every spell in this book was written down by somebody. Fill in the
      pages below and yours joins them.
    </p>

    <?php

      // spellsConfig.php supplies the connection helper, the lists of
      // schools and classes offered below, the slug builder, and the
      // shared display helpers.
      require("spellsConfig.php");

      // Read a group of checkboxes, such as the class list, which
      // arrives as an array rather than as a single value. Returns an
      // empty array when none of the boxes were ticked.
      function postedList($fieldName) {

        if (isset($_POST[$fieldName]) && is_array($_POST[$fieldName])) {

          return $_POST[$fieldName];
        }

        return array();
      }

      // Re-tick a checkbox that was ticked on the submission being
      // redisplayed, so a rejected spell does not lose its class or
      // component choices.
      function printCheckedIfInList($fieldName, $optionValue) {

        if (in_array($optionValue, postedList($fieldName))) {

          print(" checked=\"checked\"");
        }
      }

      // The same for a single on/off checkbox such as concentration.
      function printCheckedIfSet($fieldName) {

        if (postedValue($fieldName) !== "") {

          print(" checked=\"checked\"");
        }
      }

      // And for the school and level menus.
      function printSelectedIfChosen($fieldName, $optionValue) {

        if (postedValue($fieldName) === $optionValue) {

          print(" selected=\"selected\"");
        }
      }

      // Every problem found with a submitted spell, in the order the
      // fields appear on the form. An empty list means the spell is
      // ready to be written.
      $validationErrors = array();
      $spellWasAdded    = false;

      if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $name        = postedValue("name");
        $level       = postedValue("level");
        $school      = postedValue("school");
        $castingTime = postedValue("castingTime");
        $range       = postedValue("range");
        $material    = postedValue("material");
        $duration    = postedValue("duration");
        $description = postedValue("description");
        $higherLevels = postedValue("higherLevels");

        $chosenComponents = postedList("components");
        $chosenClasses    = postedList("classes");

        // --- Name: required, and short enough for the 80 character
        // column it is stored in. ---
        if ($name === "") {

          $validationErrors[] = "A spell needs a name.";

        } else if (strlen($name) > 80) {

          $validationErrors[] = "The spell name must be 80 characters or fewer.";

        } else if (makeSpellSlug($name) === "") {

          // A name of nothing but punctuation would leave no letters or
          // digits to build the slug primary key out of.
          $validationErrors[] = "The spell name needs at least one letter or number in it.";
        }

        // --- Level: required, and one of the ten real spell levels. ---
        if ($level === "") {

          $validationErrors[] = "Choose a spell level.";

        } else if (!ctype_digit($level) || $level < 0 || $level > 9) {

          $validationErrors[] = "The spell level must be a cantrip or a level from 1 to 9.";
        }

        // --- School: required, and one this grimoire knows how to
        // color and file. ---
        if ($school === "") {

          $validationErrors[] = "Choose a school of magic.";

        } else if (!array_key_exists($school, getSchoolColors())) {

          $validationErrors[] = "That is not one of the eight schools of magic.";
        }

        // --- The three plain text casting statistics. ---
        if ($castingTime === "") {

          $validationErrors[] = "Enter a casting time, such as \"1 action\".";

        } else if (strlen($castingTime) > 90) {

          $validationErrors[] = "The casting time must be 90 characters or fewer.";
        }

        if ($range === "") {

          $validationErrors[] = "Enter a range, such as \"60 feet\" or \"Touch\".";

        } else if (strlen($range) > 50) {

          $validationErrors[] = "The range must be 50 characters or fewer.";
        }

        if ($duration === "") {

          $validationErrors[] = "Enter a duration, such as \"Instantaneous\".";

        } else if (strlen($duration) > 60) {

          $validationErrors[] = "The duration must be 60 characters or fewer.";
        }

        // --- Components: at least one of V, S, or M, and a material
        // component described whenever M is among them. ---
        $validComponents = array("V", "S", "M");

        foreach ($chosenComponents as $component) {

          if (!in_array($component, $validComponents)) {

            $validationErrors[] = "Components can only be verbal, somatic, or material.";
            break;
          }
        }

        if (count($chosenComponents) === 0) {

          $validationErrors[] = "Choose at least one component.";
        }

        if (in_array("M", $chosenComponents) && $material === "") {

          $validationErrors[] = "A spell with a material component has to say what the material is.";
        }

        if (strlen($material) > 150) {

          $validationErrors[] = "The material component must be 150 characters or fewer.";
        }

        // --- Classes: at least one, and every one of them real. ---
        if (count($chosenClasses) === 0) {

          $validationErrors[] = "Choose at least one class that can cast the spell.";

        } else {

          foreach ($chosenClasses as $className) {

            if (!in_array($className, getSpellClasses())) {

              $validationErrors[] = "That is not a class this grimoire recognizes.";
              break;
            }
          }
        }

        // --- Description: required. ---
        if ($description === "") {

          $validationErrors[] = "Describe what the spell does.";
        }

        // Nothing touches the database until the entry itself is sound.
        if (count($validationErrors) === 0) {

          $conn = connectToSpellServer(true);

          // The slug is the table's primary key, so a second "Fireball"
          // would collide with the one already recorded. Looking first
          // turns that collision into a sentence the visitor can act on
          // instead of a raw duplicate key error.
          $slug = makeSpellSlug($name);

          $duplicateCheck = $conn->prepare("SELECT name FROM " . SPELLS_TABLE . " WHERE slug = ?");

          if ($duplicateCheck === false) {

            $validationErrors[] = "Could not search the grimoire: " . $conn->error .
                                  " If the table is missing, run BobCreateSpells.php first.";

          } else {

            $duplicateCheck->bind_param("s", $slug);
            $duplicateCheck->execute();
            $duplicateResult = $duplicateCheck->get_result();

            if ($duplicateResult->num_rows > 0) {

              $validationErrors[] = "The grimoire already holds a spell named " . $name . ".";
            }

            $duplicateCheck->close();
          }

          if (count($validationErrors) === 0) {

            // The columns the form does not ask about. A spell scribed
            // here is homebrew rather than something out of the SRD,
            // and extraData is the spare column the table keeps for
            // anything a later module wants to attach.
            $sourceId  = "homebrew";
            $edition   = "5e";
            $extraData = null;

            // Stored the same way the seed data stores them: the
            // component letters joined into "V, S, M", the class list
            // JSON encoded, and the two flags as "1" or "0" for their
            // TINYINT(1) columns.
            $components    = implode(", ", $chosenComponents);
            $classesJson   = json_encode(array_values($chosenClasses));
            $concentration = (postedValue("concentration") !== "") ? "1" : "0";
            $ritual        = (postedValue("ritual") !== "") ? "1" : "0";

            // Optional columns are stored as SQL NULL rather than as an
            // empty string, which is what the read and query pages
            // check for before printing them.
            $materialValue     = ($material === "") ? null : $material;
            $higherLevelsValue = ($higherLevels === "") ? null : $higherLevels;

            $insertSql =
              "INSERT INTO " . SPELLS_TABLE . " " .
              "(slug, sourceId, name, edition, level, school, castingTime, `range`, components, material, duration, concentration, ritual, classes, description, higherLevels, extraData) " .
              "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $statement = $conn->prepare($insertSql);

            if ($statement === false) {

              $validationErrors[] = "Could not prepare the insert: " . $conn->error;

            } else {

              // A prepared statement, so nothing typed on the form is
              // ever pasted into the SQL itself.
              $statement->bind_param(
                "sssssssssssssssss",
                $slug, $sourceId, $name, $edition, $level, $school, $castingTime,
                $range, $components, $materialValue, $duration, $concentration,
                $ritual, $classesJson, $description, $higherLevelsValue, $extraData
              );

              if (!$statement->execute()) {

                $validationErrors[] = "Could not write the spell: " . $statement->error;

              } else {

                $spellWasAdded = true;
              }

              $statement->close();
            }
          }

          // The new row is read back out of the table rather than
          // printed from what was typed, so the card below is proof of
          // what MySQL actually stored.
          if ($spellWasAdded) {

            $readBack = $conn->prepare("SELECT * FROM " . SPELLS_TABLE . " WHERE slug = ?");

            if ($readBack === false) {

              // The spell is written either way; only the card showing
              // it back could not be built.
              $spellWasAdded = false;
              $validationErrors[] = "The spell was written, but it could not be read back: " . $conn->error;

            } else {

              $readBack->bind_param("s", $slug);
              $readBack->execute();
              $newSpell = $readBack->get_result()->fetch_assoc();
              $readBack->close();
            }
          }

          $conn->close();
        }
      }

      // Report on the submission: either the list of what needs fixing,
      // or the finished spell read back out of the grimoire.
      if (count($validationErrors) > 0) {

        print("<h2>The Ink Would Not Take</h2>\n");
        print("<ul class=\"error-list\">\n");

        foreach ($validationErrors as $errorMessage) {

          print("<li>" . escapeForHtml($errorMessage) . "</li>\n");
        }

        print("</ul>\n");

      } else if ($spellWasAdded) {

        print("<h2>Scribed Into the Grimoire</h2>\n");

        printStatusLine($newSpell["name"] . " is now recorded in " . SPELLS_TABLE . ".", true);

        print("<div class=\"spell-grid\">\n");
        renderSpellCard($newSpell);
        print("</div>\n");

        // The form below is redrawn from $_POST so a rejected spell
        // keeps what was typed. Once a spell is safely written, that is
        // no longer wanted: emptying $_POST hands back a blank form,
        // ready for the next spell.
        $_POST = array();
      }

    ?>

    <h2>Spell Entry</h2>

    <form class="spell-form" action="BobForm.php" method="post">

      <div class="form-row">

        <label class="form-label" for="name">Spell Name</label>
        <input class="form-input" type="text" id="name" name="name" size="40"
               maxlength="80" value="<?php printFieldValue("name"); ?>">

      </div>

      <div class="form-row">

        <label class="form-label" for="level">Spell Level</label>

        <select class="form-input" id="level" name="level">

          <option value="">Choose a level</option>

          <?php

            // Levels 0 through 9, labelled by the same helper the spell
            // cards use, so 0 reads as "Cantrip".
            for ($levelOption = 0; $levelOption <= 9; $levelOption++) {

              print("<option value=\"" . $levelOption . "\"");
              printSelectedIfChosen("level", (string) $levelOption);
              print(">" . formatSpellLevel($levelOption) . "</option>\n");
            }

          ?>

        </select>

      </div>

      <div class="form-row">

        <label class="form-label" for="school">School of Magic</label>

        <select class="form-input" id="school" name="school">

          <option value="">Choose a school</option>

          <?php

            foreach (getSchoolColors() as $schoolName => $schoolColor) {

              print("<option value=\"" . escapeForHtml($schoolName) . "\"");
              printSelectedIfChosen("school", $schoolName);
              print(">" . escapeForHtml($schoolName) . "</option>\n");
            }

          ?>

        </select>

      </div>

      <div class="form-row">

        <label class="form-label" for="castingTime">Casting Time</label>
        <input class="form-input" type="text" id="castingTime" name="castingTime" size="40"
               maxlength="90" value="<?php printFieldValue("castingTime"); ?>">

      </div>

      <div class="form-row">

        <label class="form-label" for="range">Range</label>
        <input class="form-input" type="text" id="range" name="range" size="40"
               maxlength="50" value="<?php printFieldValue("range"); ?>">

      </div>

      <div class="form-row">

        <label class="form-label" for="componentV">Components</label>

        <span class="form-check">
          <input type="checkbox" id="componentV" name="components[]" value="V"
                 <?php printCheckedIfInList("components", "V"); ?>>
          <label for="componentV">Verbal</label>

          <input type="checkbox" id="componentS" name="components[]" value="S"
                 <?php printCheckedIfInList("components", "S"); ?>>
          <label for="componentS">Somatic</label>

          <input type="checkbox" id="componentM" name="components[]" value="M"
                 <?php printCheckedIfInList("components", "M"); ?>>
          <label for="componentM">Material</label>
        </span>

      </div>

      <div class="form-row">

        <label class="form-label" for="material">Material Component</label>
        <input class="form-input" type="text" id="material" name="material" size="40"
               maxlength="150" value="<?php printFieldValue("material"); ?>">
        <span class="form-hint">Required only when the spell has a material component.</span>

      </div>

      <div class="form-row">

        <label class="form-label" for="duration">Duration</label>
        <input class="form-input" type="text" id="duration" name="duration" size="40"
               maxlength="60" value="<?php printFieldValue("duration"); ?>">

      </div>

      <div class="form-row">

        <label class="form-label" for="concentration">Casting Notes</label>

        <span class="form-check">
          <input type="checkbox" id="concentration" name="concentration" value="yes"
                 <?php printCheckedIfSet("concentration"); ?>>
          <label for="concentration">Requires concentration</label>

          <input type="checkbox" id="ritual" name="ritual" value="yes"
                 <?php printCheckedIfSet("ritual"); ?>>
          <label for="ritual">Can be cast as a ritual</label>
        </span>

      </div>

      <div class="form-row">

        <label class="form-label" for="classArtificer">Castable By</label>

        <span class="form-check">

          <?php

            // One checkbox per class, from the same list the query page
            // searches by.
            foreach (getSpellClasses() as $className) {

              $checkboxId = "class" . $className;

              print("<input type=\"checkbox\" id=\"" . $checkboxId . "\" name=\"classes[]\" value=\"" . escapeForHtml($className) . "\"");
              printCheckedIfInList("classes", $className);
              print(">\n");
              print("<label for=\"" . $checkboxId . "\">" . escapeForHtml($className) . "</label>\n");
            }

          ?>

        </span>

      </div>

      <div class="form-row">

        <label class="form-label" for="description">Description</label>
        <textarea class="form-input" id="description" name="description" rows="5" cols="40"><?php printFieldValue("description"); ?></textarea>

      </div>

      <div class="form-row">

        <label class="form-label" for="higherLevels">At Higher Levels</label>
        <textarea class="form-input" id="higherLevels" name="higherLevels" rows="3" cols="40"><?php printFieldValue("higherLevels"); ?></textarea>
        <span class="form-hint">Optional. What changes when the spell is cast with a higher slot.</span>

      </div>

      <div class="form-actions">
        <input class="form-submit" type="submit" value="Scribe the Spell">
      </div>

    </form>

    <?php printSpellbookNav("BobForm.php"); ?>

    </div>

  </body>

</html>
