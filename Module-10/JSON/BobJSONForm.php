<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 09/28/2026
  Assignment: Module 10.2 - JSON (BobJSONForm.php)
  Purpose: Displays a D&D character sheet form that collects twelve
           fields, then posts them to BobJSON.php, which encodes them
           into JSON with json_encode. The fields cover a range of
           kinds so the JSON shows a range of types: text becomes
           strings, the level and ability scores become integers, the
           gold becomes a decimal number, the inspiration box becomes
           true or false, the languages become an array, and the six
           ability scores become a nested object. The drop-down,
           radio, and check box choices are pulled from BobJSONData.php
           so the form and the checker always offer the same choices.
-->

<html>

  <head>

    <!-- The page and the form both declare UTF-8 so the browser sends
         accented letters as UTF-8, which is the only text encoding
         json_encode accepts. -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>

      D&amp;D Character Sheet - Export to JSON

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap">

    <link rel="stylesheet" type="text/css" href="BobJSON.css">

  </head>

  <body>

    <div class="container">

    <h1>Character Sheet Export</h1>

    <p class="scroll-intro">
      Fill in your adventurer below and the scribe will copy the sheet
      out as JSON, ready for any virtual tabletop that reads it.
    </p>

    <?php

      // Bring in the species, class, alignment, ability, and language
      // lists along with the helpers that print them.
      require("BobJSONData.php");

      // Today's date, used as the latest date a sheet can have been
      // created on. BobJSON.php repeats this check, since a browser
      // that does not understand a date field will not enforce it.
      $today = date("Y-m-d");

    ?>

    <form action="BobJSON.php" method="post" accept-charset="UTF-8">

      <fieldset>

        <legend>The Character</legend>

        <div class="field">
          <label for="characterName">1. Character Name</label>
          <input type="text" name="characterName" id="characterName" maxlength="40">
          <span class="hint">Letters, spaces, hyphens, and apostrophes only, 2 to 40 characters.</span>
        </div>

        <div class="field-pair">

          <div class="field">
            <label for="species">2. Species</label>
            <select name="species" id="species">
              <option value="">-- Choose a species --</option>
              <?php printOptions($speciesList); ?>
            </select>
          </div>

          <div class="field">
            <label for="characterClass">3. Class</label>
            <select name="characterClass" id="characterClass">
              <option value="">-- Choose a class --</option>
              <?php printOptions($characterClasses); ?>
            </select>
          </div>

        </div>

        <div class="field-pair">

          <div class="field">
            <label for="level">4. Level</label>
            <input type="number" name="level" id="level" min="1" max="20" step="1">
            <span class="hint">A whole number from 1 to 20.</span>
          </div>

          <div class="field">
            <label for="gold">5. Gold Pieces</label>
            <input type="number" name="gold" id="gold" min="0" max="100000" step="0.01">
            <span class="hint">A decimal amount from 0 to 100000, such as 42.50.</span>
          </div>

        </div>

        <div class="field">
          <label>6. Alignment</label>

          <div class="choice-grid">

          <?php

            // One radio button per alignment. They all share the name
            // "alignment", which keeps them one group where only one
            // may be chosen.
            foreach ($alignments as $index => $alignment) {

              $inputId = "alignment_" . $index;

              print("<span class=\"choice\">");
              print("<input type=\"radio\" name=\"alignment\" id=\"" . $inputId . "\" value=\"" . escapeForHtml($alignment) . "\">");
              print("<label for=\"" . $inputId . "\">" . escapeForHtml($alignment) . "</label>");
              print("</span>\n");
            }

          ?>

          </div>
        </div>

      </fieldset>

      <fieldset>

        <legend>7. Ability Scores</legend>

        <p class="hint">Each score is a whole number from 3 to 20.</p>

        <div class="ability-grid">

        <?php

          // One number box per ability. They are named abilities[str],
          // abilities[dex], and so on, so PHP collects all six into
          // one array that becomes a nested object in the JSON.
          foreach ($abilityNames as $shortName => $fullName) {

            $inputId = "ability_" . $shortName;

            print("<div class=\"ability-box\">");
            print("<label for=\"" . $inputId . "\">" . strtoupper($shortName) . "</label>");
            print("<input type=\"number\" name=\"abilities[" . $shortName . "]\" id=\"" . $inputId . "\" min=\"3\" max=\"20\" step=\"1\">");
            print("<span class=\"ability-name\">" . ucfirst($fullName) . "</span>");
            print("</div>\n");
          }

        ?>

        </div>

      </fieldset>

      <fieldset>

        <legend>Background</legend>

        <div class="field">
          <label>8. Languages Spoken</label>

          <div class="choice-grid">

          <?php

            // One check box per language. The name ends in square
            // brackets so PHP gathers every ticked box into an array,
            // which becomes a JSON array.
            foreach ($languageList as $index => $language) {

              $inputId = "language_" . $index;

              print("<span class=\"choice\">");
              print("<input type=\"checkbox\" name=\"languages[]\" id=\"" . $inputId . "\" value=\"" . escapeForHtml($language) . "\">");
              print("<label for=\"" . $inputId . "\">" . escapeForHtml($language) . "</label>");
              print("</span>\n");
            }

          ?>

          </div>

          <span class="hint">Tick at least one.</span>
        </div>

        <div class="field">
          <label>9. Inspiration</label>
          <span class="choice">
            <input type="checkbox" name="inspiration" id="inspiration" value="yes">
            <label for="inspiration">The Dungeon Master has granted this character inspiration</label>
          </span>
          <span class="hint">Ticked or not, this becomes true or false in the JSON.</span>
        </div>

        <div class="field">
          <label for="backstory">10. Backstory</label>
          <textarea name="backstory" id="backstory" rows="5" cols="60"></textarea>
          <span class="hint">Between 20 and 1000 characters.</span>
        </div>

      </fieldset>

      <fieldset>

        <legend>The Player</legend>

        <div class="field-pair">

          <div class="field">
            <label for="playerEmail">11. Player E-mail</label>
            <input type="email" name="playerEmail" id="playerEmail" maxlength="60">
            <span class="hint">Such as bob@example.com.</span>
          </div>

          <div class="field">
            <label for="createdOn">12. Sheet Created On</label>
            <input type="date" name="createdOn" id="createdOn" max="<?php print($today); ?>">
            <span class="hint">A real date, today or earlier.</span>
          </div>

        </div>

      </fieldset>

      <div class="actions">
        <input type="submit" value="Export to JSON">
      </div>

    </form>

    </div>

  </body>

</html>
