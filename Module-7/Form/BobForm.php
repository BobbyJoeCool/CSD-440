<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 09/07/2026
  Assignment: Module 7 - Form (BobForm.php)
  Purpose: Displays the Ye Olde Adventurer's Guild application form, an
           in-character sign-up sheet for adventurers who want to join a
           party. It collects nine fields across nine different kinds of
           entry: a text box, an e-mail box, a date, a drop-down list, a
           whole number, a decimal number, a radio group, a check box
           group, and a text area. The form posts to BobResponse.php,
           which checks that every field was filled in and entered
           correctly before displaying the results. The class,
           alignment, and role lists are pulled from partyData.php so
           the form and the checker always offer the same choices.
-->

<html>

  <head>

    <title>

      D&amp;D Adventuring Party Application

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="style.css">

  </head>

  <body>

    <div class="container">

    <h1>Ye Olde Adventurer's Guild - Party Application</h1>

    <p class="scroll-intro">
      Seeking one able-bodied soul to fill the last seat at our table.
      Fill in every line below; the guild scribe rejects incomplete
      applications on sight.
    </p>

    <?php

      // Bring in the class, alignment, and role lists along with the
      // helpers that print them, so the choices offered here are the
      // same ones BobResponse.php will accept.
      require('partyData.php');

      // Today's date, used as the earliest date an applicant may claim
      // to be available. The response page repeats this check, since a
      // browser that does not understand a date field will not enforce
      // it on its own.
      $today = date("Y-m-d");

    ?>

    <form action="BobResponse.php" method="post">

      <fieldset>

        <legend>The Applicant</legend>

        <div class="field">
          <label for="characterName">Character Name</label>
          <input type="text" name="characterName" id="characterName" maxlength="40" />
          <span class="hint">Letters, spaces, hyphens, and apostrophes only, 2 to 40 characters.</span>
        </div>

        <div class="field">
          <label for="sendingStone">Sending Stone Address</label>
          <input type="email" name="sendingStone" id="sendingStone" maxlength="60" />
          <span class="hint">Where the guild will reach you, such as thalia@stormwind.example.</span>
        </div>

        <div class="field">
          <label for="musterDate">Date Available to Muster</label>
          <input type="date" name="musterDate" id="musterDate" min="<?php print($today); ?>" />
          <span class="hint">A real calendar date, today or later.</span>
        </div>

      </fieldset>

      <fieldset>

        <legend>Skill at Arms</legend>

        <div class="field">
          <label for="characterClass">Calling</label>
          <select name="characterClass" id="characterClass">
            <option value="">-- Choose a calling --</option>
            <?php printOptions($characterClasses, ""); ?>
          </select>
          <span class="hint">The class you have trained in.</span>
        </div>

        <div class="field">
          <label for="characterLevel">Character Level</label>
          <input type="number" name="characterLevel" id="characterLevel" min="1" max="20" step="1" />
          <span class="hint">A whole number from 1 to 20.</span>
        </div>

        <div class="field">
          <label for="dailyRate">Daily Rate in Gold Pieces</label>
          <input type="number" name="dailyRate" id="dailyRate" min="0" max="1000" step="0.01" />
          <span class="hint">A decimal amount from 0.00 to 1000.00, such as 12.50.</span>
        </div>

      </fieldset>

      <fieldset>

        <legend>Oaths and Duties</legend>

        <div class="field">
          <label>Alignment</label>

          <div class="choice-grid">

          <?php

            // Print one radio button per alignment. Every button shares
            // the name "alignment", which is what keeps them a single
            // group where only one may be chosen.
            foreach ($alignments as $index => $alignment) {

              $inputId = "alignment_" . $index;

              print("<span class=\"choice\">");
              print("<input type=\"radio\" name=\"alignment\" id=\"" . $inputId . "\" value=\"" . escapeForHtml($alignment) . "\" />");
              print("<label for=\"" . $inputId . "\">" . escapeForHtml($alignment) . "</label>");
              print("</span>\n");
            }

          ?>

          </div>

          <span class="hint">Choose the one that guides your character.</span>
        </div>

        <div class="field">
          <label>Roles You Will Fill</label>

          <div class="choice-grid">

          <?php

            // Print one check box per role. The name ends in square
            // brackets so PHP collects every box the applicant ticks
            // into a single array instead of keeping only the last one.
            foreach ($partyRoles as $index => $role) {

              $inputId = "role_" . $index;

              print("<span class=\"choice\">");
              print("<input type=\"checkbox\" name=\"partyRoles[]\" id=\"" . $inputId . "\" value=\"" . escapeForHtml($role) . "\" />");
              print("<label for=\"" . $inputId . "\">" . escapeForHtml($role) . "</label>");
              print("</span>\n");
            }

          ?>

          </div>

          <span class="hint">Tick at least one. Tick as many as you can honestly claim.</span>
        </div>

        <div class="field">
          <label for="tale">Tale of Notable Deeds</label>
          <textarea name="tale" id="tale" rows="5" cols="60"></textarea>
          <span class="hint">Between 20 and 500 characters. Boasting is expected; lying is not.</span>
        </div>

      </fieldset>

      <div class="actions">
        <input type="submit" value="Submit to the Guild" />
      </div>

    </form>

    </div>

  </body>

</html>
