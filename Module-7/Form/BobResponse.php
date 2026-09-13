<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 09/07/2026
  Assignment: Module 7 - Form (BobResponse.php)
  Purpose: Receives the party application posted by BobForm.php and
           checks it. Every one of the nine fields must be filled in,
           and each must hold the right kind of data: a name of letters
           only, a valid e-mail address, a real calendar date that is
           not in the past, a class and an alignment taken from the
           guild's own lists, a whole number level from 1 to 20, a
           decimal daily rate from 0.00 to 1000.00, at least one role,
           and a tale between 20 and 500 characters. If every check
           passes, the page prints the accepted application as a
           formatted roster sheet. If any check fails, it prints an
           error display listing every problem found, so the applicant
           can fix all of them in one trip back to the form.
-->

<html>

  <head>

    <title>

      D&amp;D Party Application - Guild Response

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="style.css">

  </head>

  <body>

    <div class="container">

    <h1>Ye Olde Adventurer's Guild - Scribe's Ruling</h1>

    <?php

      // The same class, alignment, and role lists the form was built
      // from, so an answer can only be accepted if it was one of the
      // choices actually offered.
      require('partyData.php');

      // Every problem found gets added to this list rather than
      // stopping the checks, so the applicant sees all of the mistakes
      // at once instead of one per submission.
      $errors = array();

      // Print one labeled row of the accepted application.
      function printResultRow($label, $value) {

        print("<tr>");
        print("<th>" . escapeForHtml($label) . "</th>");
        print("<td>" . escapeForHtml($value) . "</td>");
        print("</tr>\n");
      }

      // Pull one posted text field, trimming the stray spaces a typist
      // leaves on the ends. A field that was never posted at all comes
      // back as an empty string so the checks below can treat a missing
      // field and a blank field the same way.
      function postedText($fieldName) {

        if (!isset($_POST[$fieldName])) {

          return "";
        }

        return trim($_POST[$fieldName]);
      }

    ?>

    <?php

      // Guard against someone opening this page directly instead of
      // arriving here by submitting the form. There is nothing to
      // check in that case, so send them back to the application.
      if ($_SERVER["REQUEST_METHOD"] !== "POST") {

        print("<div class=\"verdict-rejected\">");
        print("<h2>No Application Received</h2>");
        print("<p>The scribe has nothing to read. Fill out the application first.</p>");
        print("</div>");
        print("<p><a class=\"back-link\" href=\"BobForm.php\">Return to the Application</a></p>");
        print("</div></body></html>");
        exit;
      }

    ?>

    <?php

      // Read all nine fields out of the POST data. The roles arrive as
      // an array because their check boxes were named "partyRoles[]",
      // so they are handled separately from the text fields.
      $characterName  = postedText("characterName");
      $sendingStone   = postedText("sendingStone");
      $musterDate     = postedText("musterDate");
      $characterClass = postedText("characterClass");
      $characterLevel = postedText("characterLevel");
      $dailyRate      = postedText("dailyRate");
      $alignment      = postedText("alignment");
      $tale           = postedText("tale");

      $selectedRoles = isset($_POST["partyRoles"]) ? $_POST["partyRoles"] : array();

      // Character name: required, and only the characters a name can
      // reasonably contain. Anything else is almost certainly a typo or
      // an attempt to slip markup into the page.
      if ($characterName === "") {

        $errors[] = "Character name is required.";

      } else if (!preg_match("/^[A-Za-z' -]{2,40}$/", $characterName)) {

        $errors[] = "Character name must be 2 to 40 characters and may contain only letters, spaces, hyphens, and apostrophes.";
      }

      // Sending stone address: required, and must look like a real
      // e-mail address. filter_var does the pattern work here rather
      // than a hand-written expression.
      if ($sendingStone === "") {

        $errors[] = "Sending stone address is required.";

      } else if (!filter_var($sendingStone, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Sending stone address \"" . $sendingStone . "\" is not a valid e-mail address.";
      }

      // Muster date: required, and must be a date that actually exists.
      // createFromFormat accepts something like 2026-02-31 and rolls it
      // forward to March, so the parsed date is formatted back out and
      // compared to what was typed to catch that case. A valid date is
      // then compared to today, since the form's earliest-date rule
      // only holds in browsers that understand a date field.
      $musterDateObject = false;

      if ($musterDate === "") {

        $errors[] = "Date available to muster is required.";

      } else {

        $musterDateObject = DateTime::createFromFormat("Y-m-d", $musterDate);

        if ($musterDateObject === false || $musterDateObject->format("Y-m-d") !== $musterDate) {

          $errors[] = "Date available to muster must be a real calendar date in YYYY-MM-DD form.";
          $musterDateObject = false;

        } else if ($musterDate < date("Y-m-d")) {

          $errors[] = "Date available to muster cannot be in the past. The guild cannot send you back in time.";
          $musterDateObject = false;
        }
      }

      // Calling: required, and must be one of the classes the form
      // offered. in_array rejects anything typed in by hand or sent
      // from outside the form.
      if ($characterClass === "") {

        $errors[] = "A calling must be chosen.";

      } else if (!in_array($characterClass, $characterClasses, true)) {

        $errors[] = "\"" . $characterClass . "\" is not a calling the guild recognizes.";
      }

      // Character level: required, and must be a whole number from 1
      // to 20. FILTER_VALIDATE_INT with a range rejects decimals,
      // letters, and numbers outside the range in one step, and returns
      // the value as an actual integer when it passes.
      $levelValue = false;

      if ($characterLevel === "") {

        $errors[] = "Character level is required.";

      } else {

        $levelValue = filter_var(
          $characterLevel,
          FILTER_VALIDATE_INT,
          array("options" => array("min_range" => 1, "max_range" => 20))
        );

        if ($levelValue === false) {

          $errors[] = "Character level must be a whole number from 1 to 20.";
        }
      }

      // Daily rate: required, and must be a decimal number from 0.00 to
      // 1000.00. FILTER_VALIDATE_FLOAT has no range option, so the two
      // ends are checked by hand once the value is known to be a number.
      $rateValue = false;

      if ($dailyRate === "") {

        $errors[] = "Daily rate in gold pieces is required.";

      } else {

        $rateValue = filter_var($dailyRate, FILTER_VALIDATE_FLOAT);

        if ($rateValue === false) {

          $errors[] = "Daily rate must be a number, such as 12.50.";

        } else if ($rateValue < 0 || $rateValue > 1000) {

          $errors[] = "Daily rate must be between 0.00 and 1000.00 gold pieces.";
          $rateValue = false;
        }
      }

      // Alignment: required, and must be one of the nine the form
      // offered.
      if ($alignment === "") {

        $errors[] = "An alignment must be chosen.";

      } else if (!in_array($alignment, $alignments, true)) {

        $errors[] = "\"" . $alignment . "\" is not one of the nine alignments.";
      }

      // Roles: at least one check box must be ticked, and every ticked
      // box must carry a role from the guild's list. An unticked group
      // is not posted at all, which is why the array starts out empty.
      if (!is_array($selectedRoles) || count($selectedRoles) === 0) {

        $errors[] = "At least one party role must be chosen.";

      } else {

        foreach ($selectedRoles as $role) {

          if (!in_array($role, $partyRoles, true)) {

            $errors[] = "\"" . $role . "\" is not a party role the guild recognizes.";
          }
        }
      }

      // Tale of deeds: required, and long enough to say something but
      // short enough for the scribe to read.
      if ($tale === "") {

        $errors[] = "A tale of notable deeds is required.";

      } else if (strlen($tale) < 20) {

        $errors[] = "The tale of notable deeds must be at least 20 characters. Yours is " . strlen($tale) . ".";

      } else if (strlen($tale) > 500) {

        $errors[] = "The tale of notable deeds may be no more than 500 characters. Yours is " . strlen($tale) . ".";
      }

    ?>

    <?php

      // With every field checked, the page splits one of two ways: the
      // accepted application printed as a roster sheet, or the list of
      // everything that went wrong. Note that every value printed below
      // passes through escapeForHtml first, so anything the applicant
      // typed is shown as text and never run as markup.
      if (count($errors) === 0) {

        print("<div class=\"verdict-accepted\">");
        print("<h2>Application Accepted</h2>");
        print("<p>Welcome to the party, " . escapeForHtml($characterName) . ". Your marching orders are recorded below.</p>");
        print("</div>");

        print("<table border=\"1\" cellpadding=\"6\">");
        print("<tbody>\n");

        printResultRow("Character Name", $characterName);
        printResultRow("Sending Stone Address", $sendingStone);
        printResultRow("Available to Muster", $musterDateObject->format("l, F j, Y"));
        printResultRow("Calling", $characterClass);
        printResultRow("Character Level", $levelValue);
        printResultRow("Daily Rate", number_format($rateValue, 2) . " gp");
        printResultRow("Alignment", $alignment);

        // The roles came in as an array, so they are joined back into
        // one readable line before being printed.
        printResultRow("Roles Filled", implode(", ", $selectedRoles));

        printResultRow("Tale of Notable Deeds", $tale);

        print("</tbody>");
        print("</table>\n");

        // A small closing flourish that uses two of the accepted
        // numbers together, showing they came through as real numbers
        // and not just as text.
        $tenDayWage = $rateValue * 10;

        print("<p>At " . number_format($rateValue, 2) . " gp per day, a ten-day delve pays you "
          . number_format($tenDayWage, 2) . " gp. Sign here, level "
          . $levelValue . " " . escapeForHtml($characterClass) . ".</p>");

      } else {

        print("<div class=\"verdict-rejected\">");
        print("<h2>Application Returned</h2>");
        print("<p>The guild scribe found " . count($errors) . " problem"
          . (count($errors) === 1 ? "" : "s")
          . " with your application and has sent it back unsealed:</p>");
        print("<ul class=\"error-list\">\n");

        foreach ($errors as $error) {

          print("<li>" . escapeForHtml($error) . "</li>\n");
        }

        print("</ul>");
        print("</div>");
      }

    ?>

    <p><a class="back-link" href="BobForm.php">Return to the Application</a></p>

    </div>

  </body>

</html>
