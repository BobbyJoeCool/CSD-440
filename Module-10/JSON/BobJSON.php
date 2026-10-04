<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 09/28/2026
  Assignment: Module 10.2 - JSON (BobJSON.php)
  Purpose: Receives the character sheet posted by BobJSONForm.php,
           checks all twelve fields, and encodes the accepted sheet
           into JSON with json_encode. When the encoding succeeds, the
           page shows the JSON in a formatted, color-coded display,
           along with its compact one-line form, a download link, and
           a check that json_decode reads it back into exactly the
           same data. When anything goes wrong, whether the page was
           opened without a submission, a field failed its check, or
           json_encode itself refused the data, the page shows an
           error display that reports the problem instead.
-->

<html>

  <head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>

      D&amp;D Character Sheet - JSON Export

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap">

    <link rel="stylesheet" type="text/css" href="BobJSON.css">

  </head>

  <body>

    <div class="container">

    <h1>Character Sheet Export</h1>

    <?php

      // The same lists the form was built from, so an answer can only
      // be accepted if it was one of the choices actually offered.
      require("BobJSONData.php");

      // Pull one posted text field, trimming the stray spaces a typist
      // leaves on the ends. A field that was never posted comes back
      // as an empty string, so a missing field and a blank field are
      // handled the same way.
      function postedText($fieldName) {

        if (!isset($_POST[$fieldName]) || !is_string($_POST[$fieldName])) {

          return "";
        }

        return trim($_POST[$fieldName]);
      }

      // Print the error display: a heading, a sentence saying what
      // went wrong, and a list of every specific problem.
      function printErrorDisplay($heading, $explanation, $problemList) {

        print("<div class=\"verdict-rejected\">\n");
        print("<h2>" . escapeForHtml($heading) . "</h2>\n");
        print("<p>" . escapeForHtml($explanation) . "</p>\n");
        print("<ul class=\"error-list\">\n");

        foreach ($problemList as $problem) {

          print("<li>" . escapeForHtml($problem) . "</li>\n");
        }

        print("</ul>\n");
        print("</div>\n");
      }

    ?>

    <?php

      // Every problem found is added to this list rather than stopping
      // the checks, so the player sees all of the mistakes at once.
      $errors = array();

      // Set only once the sheet has been checked and encoded.
      $characterSheet   = null;
      $prettyJson       = false;
      $jsonErrorMessage = "";

      // Guard against the page being opened directly instead of by
      // submitting the form. There is nothing to encode in that case.
      $formWasSubmitted = ($_SERVER["REQUEST_METHOD"] === "POST");

      if ($formWasSubmitted) {

        // Read the plain fields out of the POST data. The languages
        // and ability scores arrive as arrays and are read below.
        $characterName  = postedText("characterName");
        $species        = postedText("species");
        $characterClass = postedText("characterClass");
        $level          = postedText("level");
        $gold           = postedText("gold");
        $alignment      = postedText("alignment");
        $backstory      = postedText("backstory");
        $playerEmail    = postedText("playerEmail");
        $createdOn      = postedText("createdOn");

        $postedAbilities = (isset($_POST["abilities"]) && is_array($_POST["abilities"])) ? $_POST["abilities"] : array();
        $postedLanguages = (isset($_POST["languages"]) && is_array($_POST["languages"])) ? $_POST["languages"] : array();

        // An unticked check box is not posted at all, so inspiration
        // is simply whether the box arrived.
        $hasInspiration = isset($_POST["inspiration"]);

        // 1. Character name: required, and only characters a name can
        // reasonably hold. The u flag lets accented letters such as
        // the e in Zoë count as letters.
        if ($characterName === "") {

          $errors[] = "Character name is required.";

        } else if (!preg_match("/^[\p{L}' -]{2,40}$/u", $characterName)) {

          $errors[] = "Character name must be 2 to 40 characters and may contain only letters, spaces, hyphens, and apostrophes.";
        }

        // 2 and 3. Species and class: required, and must be one of the
        // choices the form offered.
        if ($species === "") {

          $errors[] = "A species must be chosen.";

        } else if (!in_array($species, $speciesList, true)) {

          $errors[] = "\"" . $species . "\" is not a species on the list.";
        }

        if ($characterClass === "") {

          $errors[] = "A class must be chosen.";

        } else if (!in_array($characterClass, $characterClasses, true)) {

          $errors[] = "\"" . $characterClass . "\" is not a class on the list.";
        }

        // 4. Level: a whole number from 1 to 20. FILTER_VALIDATE_INT
        // hands back a real integer, which is what makes it appear in
        // the JSON as 5 rather than as the string "5".
        $levelValue = false;

        if ($level === "") {

          $errors[] = "Level is required.";

        } else {

          $levelValue = filter_var($level, FILTER_VALIDATE_INT,
            array("options" => array("min_range" => 1, "max_range" => 20)));

          if ($levelValue === false) {

            $errors[] = "Level must be a whole number from 1 to 20.";
          }
        }

        // 5. Gold: a decimal number from 0 to 100000, rounded to the
        // copper. FILTER_VALIDATE_FLOAT has no range option, so the two
        // ends are checked by hand.
        $goldValue = false;

        if ($gold === "") {

          $errors[] = "Gold pieces are required. Enter 0 for a penniless adventurer.";

        } else {

          $goldValue = filter_var($gold, FILTER_VALIDATE_FLOAT);

          if ($goldValue === false) {

            $errors[] = "Gold pieces must be a number, such as 42.50.";

          } else if ($goldValue < 0 || $goldValue > 100000) {

            $errors[] = "Gold pieces must be between 0 and 100000.";
            $goldValue = false;

          } else {

            $goldValue = round($goldValue, 2);
          }
        }

        // 6. Alignment: required, and one of the nine.
        if ($alignment === "") {

          $errors[] = "An alignment must be chosen.";

        } else if (!in_array($alignment, $alignments, true)) {

          $errors[] = "\"" . $alignment . "\" is not one of the nine alignments.";
        }

        // 7. Ability scores: all six are required, each a whole number
        // from 3 to 20. They are gathered under their full names, which
        // become the keys of a nested JSON object.
        $abilityScores = array();

        foreach ($abilityNames as $shortName => $fullName) {

          $rawScore = (isset($postedAbilities[$shortName]) && is_string($postedAbilities[$shortName]))
                      ? trim($postedAbilities[$shortName]) : "";

          if ($rawScore === "") {

            $errors[] = ucfirst($fullName) . " score is required.";
            continue;
          }

          $scoreValue = filter_var($rawScore, FILTER_VALIDATE_INT,
            array("options" => array("min_range" => 3, "max_range" => 20)));

          if ($scoreValue === false) {

            $errors[] = ucfirst($fullName) . " score must be a whole number from 3 to 20.";

          } else {

            $abilityScores[$fullName] = $scoreValue;
          }
        }

        // 8. Languages: at least one, and every one from the list.
        // array_values renumbers the list from zero, which is what
        // makes json_encode write it as a JSON array rather than an
        // object with numbered keys.
        $languages = array();

        if (count($postedLanguages) === 0) {

          $errors[] = "At least one language must be ticked.";

        } else {

          foreach ($postedLanguages as $language) {

            if (!is_string($language) || !in_array($language, $languageList, true)) {

              $errors[] = "One of the languages ticked is not on the list.";
              break;
            }

            $languages[] = $language;
          }

          $languages = array_values(array_unique($languages));
        }

        // 10. Backstory: long enough to say something, short enough to
        // read. Its characters are deliberately not filtered here: any
        // text is a fair backstory, and checking that the text is
        // valid UTF-8 is left to json_encode, whose failure is reported
        // below like any other problem.
        if ($backstory === "") {

          $errors[] = "A backstory is required.";

        } else if (strlen($backstory) < 20) {

          $errors[] = "The backstory must be at least 20 characters. Yours is " . strlen($backstory) . ".";

        } else if (strlen($backstory) > 1000) {

          $errors[] = "The backstory may be no more than 1000 characters. Yours is " . strlen($backstory) . ".";
        }

        // 11. Player e-mail: required, and a real e-mail address.
        if ($playerEmail === "") {

          $errors[] = "Player e-mail is required.";

        } else if (!filter_var($playerEmail, FILTER_VALIDATE_EMAIL)) {

          $errors[] = "\"" . $playerEmail . "\" is not a valid e-mail address.";
        }

        // 12. Sheet created on: a real calendar date, not in the
        // future. createFromFormat rolls a date like 2026-02-31 forward
        // into March, so the parsed date is formatted back out and
        // compared to what was typed to catch that.
        if ($createdOn === "") {

          $errors[] = "The date the sheet was created is required.";

        } else {

          $createdOnDate = DateTime::createFromFormat("Y-m-d", $createdOn);

          if ($createdOnDate === false || $createdOnDate->format("Y-m-d") !== $createdOn) {

            $errors[] = "The creation date must be a real calendar date in YYYY-MM-DD form.";

          } else if ($createdOn > date("Y-m-d")) {

            $errors[] = "The creation date cannot be in the future.";
          }
        }
      }

    ?>

    <?php

      // With every field checked, build the sheet and encode it. This
      // is only attempted when there were no errors, since there is no
      // point encoding a sheet that is about to be rejected.
      if ($formWasSubmitted && count($errors) === 0) {

        // A few numbers worked out from the ones entered, the way a
        // character sheet shows them: each ability's modifier, and the
        // proficiency bonus that comes with the character's level.
        $abilityModifiers = array();

        foreach ($abilityScores as $fullName => $score) {

          $abilityModifiers[$fullName] = (int) floor(($score - 10) / 2);
        }

        $proficiencyBonus = (int) ceil($levelValue / 4) + 1;

        // The sheet as a PHP associative array. Nested arrays with
        // named keys become nested JSON objects, and the language list
        // becomes a JSON array.
        $characterSheet = array(
          "character" => array(
            "name"        => $characterName,
            "species"     => $species,
            "class"       => $characterClass,
            "level"       => $levelValue,
            "alignment"   => $alignment,
            "inspiration" => $hasInspiration,
          ),
          "abilityScores" => $abilityScores,
          "languages"     => $languages,
          "gold"          => $goldValue,
          "backstory"     => $backstory,
          "player" => array(
            "email"     => $playerEmail,
            "createdOn" => $createdOn,
          ),
          "derived" => array(
            "proficiencyBonus" => $proficiencyBonus,
            "abilityModifiers" => $abilityModifiers,
          ),
          "exportedAt" => date("c"),
        );

        // The flags, in order: indent the output so a person can read
        // it, leave slashes and accented letters as they are instead
        // of escaping them, and keep 50.0 written as 50.0 so the gold
        // is still a decimal number when it is read back.
        $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION;

        $prettyJson = json_encode($characterSheet, $jsonFlags | JSON_PRETTY_PRINT);

        // json_encode returns false rather than a string when it cannot
        // encode the data, most often because some text is not valid
        // UTF-8. json_last_error_msg explains why in plain words.
        if ($prettyJson === false) {

          $jsonErrorMessage = json_last_error_msg() . " (JSON error code " . json_last_error() . ")";

        } else {

          // The same data without the indenting, the way it would
          // actually be sent between programs.
          $compactJson = json_encode($characterSheet, $jsonFlags);

          // Read the JSON straight back in. If json_decode produces an
          // array identical to the one encoded, in both values and
          // types, nothing was lost in the conversion.
          $roundTripMatches = (json_decode($prettyJson, true) === $characterSheet);
        }
      }

    ?>

    <?php

      // The page now splits one of two ways: the JSON display, or the
      // error display saying what went wrong.
      if (!$formWasSubmitted) {

        printErrorDisplay(
          "No Character Sheet Received",
          "This page encodes a submitted character sheet, but none was sent. Fill in the form first.",
          array("The page was opened directly (a " . $_SERVER["REQUEST_METHOD"] . " request) rather than by submitting the form.")
        );

      } else if ($jsonErrorMessage !== "") {

        printErrorDisplay(
          "JSON Encoding Failed",
          "Every field passed its checks, but json_encode refused the data and returned false instead of JSON. " .
            "This usually means some text reached the page in an encoding other than UTF-8. " .
            "Use the browser's Back button, retype the backstory and name, and export again.",
          array("json_encode reported: " . $jsonErrorMessage . ".")
        );

      } else if ($prettyJson === false) {

        $problemCount = count($errors);

        printErrorDisplay(
          "Sheet Not Exported",
          "The scribe found " . $problemCount . " problem" . ($problemCount === 1 ? "" : "s") .
            " and could not produce the JSON. Use the browser's Back button to return to the form " .
            "with your entries still filled in, fix " . ($problemCount === 1 ? "it" : "them") . ", and export again.",
          $errors
        );

      } else {

        $byteCount = strlen($prettyJson);

        // A file name built from the character's name, used for the
        // download link and as the label on the JSON display.
        // The u flag keeps accented letters, so Zoë stays Zoë.
        $fileName = strtolower(trim(preg_replace("/[^\p{L}\p{N}]+/u", "-", $characterName), "-"));
        $fileName = ($fileName === "" ? "character" : $fileName) . ".json";

        print("<div class=\"verdict-accepted\">\n");
        print("<h2>Sheet Exported</h2>\n");
        print("<p>All twelve fields passed their checks, and json_encode turned " .
              escapeForHtml($characterName) . "'s sheet into " . number_format($byteCount) .
              " bytes of JSON.</p>\n");
        print("</div>\n");

        // The JSON itself, indented and color coded, in a panel made
        // to look like a code editor window.
        print("<div class=\"json-window\">\n");
        print("<div class=\"json-titlebar\">");
        print("<span class=\"json-filename\">" . escapeForHtml($fileName) . "</span>");
        print("<span class=\"json-size\">" . number_format($byteCount) . " bytes</span>");
        print("</div>\n");
        print("<pre class=\"json-view\">" . highlightJson($prettyJson) . "</pre>\n");
        print("</div>\n");

        // A download link that carries the JSON inside the link itself,
        // so the file can be saved without writing anything on the
        // server.
        print("<p class=\"json-actions\">");
        print("<a class=\"back-link\" download=\"" . escapeForHtml($fileName) . "\" href=\"data:application/json;charset=utf-8," . rawurlencode($prettyJson) . "\">Download " . escapeForHtml($fileName) . "</a>");
        print("</p>\n");

        print("<h2>Compact Form</h2>\n");
        print("<p>The same JSON without the indenting, the way one program would send it to another: " .
              number_format(strlen($compactJson)) . " bytes.</p>\n");
        print("<pre class=\"json-compact\">" . escapeForHtml($compactJson) . "</pre>\n");

        print("<h2>Round Trip Check</h2>\n");

        if ($roundTripMatches) {

          print("<p class=\"check-passed\">json_decode read the JSON back into a PHP array identical to the one encoded, " .
                "with every value and every type (string, integer, decimal, true or false, and array) intact.</p>\n");

        } else {

          print("<p class=\"check-failed\">json_decode read the JSON back, but the result does not exactly match " .
                "the array that was encoded.</p>\n");
        }
      }

    ?>

    <p><a class="back-link" href="BobJSONForm.php">Return to the Character Sheet</a></p>

    </div>

  </body>

</html>
