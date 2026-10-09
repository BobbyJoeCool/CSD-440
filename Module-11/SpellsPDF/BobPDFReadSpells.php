<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 10/05/2026
  Assignment: Module 11.2 - PDFs (BobPDFReadSpells.php)
  Purpose: Third and last of the set-up steps carried forward from
           Module 8.2, and the web-page twin of the PDF. This page reads
           every row out of Spells_8_PHP and displays it as a grimoire
           page: one card per spell, sorted by level and then name,
           showing the casting statistics, the classes that can learn
           it, and its description. Each school of magic gets its own
           accent color, so the grimoire reads at a glance the way a
           real spellbook divides its sections.
-->

<html>

  <head>

    <title>

      D&amp;D Spellbook - Step 3: Read the Grimoire

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="BobPDF.css">

  </head>

  <body>

    <div class="container">

    <h1>The Spellbook Grimoire</h1>

    <p class="scroll-intro">
      Every spell recorded in the grimoire, drawn live from the
      Spells_8_PHP table and laid out one card per page.
    </p>

    <?php

      require("BobPDFConfig.php");

      // One accent color per school of magic, so the level badge, the
      // school label, and the card's top border all share a color that
      // marks which of the eight schools a spell belongs to. The same
      // colors mark the schools in the PDF.
      $schoolColors = getSchoolColors();

      $conn = connectToSpellServer(true);

      $sql = "SELECT * FROM " . SPELLS_TABLE . " ORDER BY level ASC, name ASC";
      $result = $conn->query($sql);

      if ($result === false) {

        print("<p class=\"status-line fail\">Could not read the spellbook: " . escapeForHtml($conn->error) . "</p>");
        print("<p class=\"status-line fail\">Run BobPDFCreateSpells.php and BobPDFInsertSpells.php first.</p>");

      } else if ($result->num_rows === 0) {

        print("<p class=\"status-line fail\">The grimoire has no spells recorded yet. Run BobPDFInsertSpells.php first.</p>");

      } else {

        print("<div class=\"spell-grid\">\n");

        while ($spell = $result->fetch_assoc()) {

          $accentColor = isset($schoolColors[$spell["school"]]) ? $schoolColors[$spell["school"]] : "#8a4fff";
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

          $componentsLine = $spell["components"];

          if (!empty($spell["material"])) {

            $componentsLine .= " (" . $spell["material"] . ")";
          }

          print("<li><span class=\"meta-label\">Components</span>" . escapeForHtml($componentsLine) . "</li>");

          // The duration text itself already reads "Concentration, up
          // to ..." for any spell that needs it, the same way Dragon
          // Ledger's importer stores it, so it is printed as-is; the
          // "concentration" column is only consulted above for the
          // "(ritual)" style aside next to the school name.
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

        print("</div>\n");

        print("<p class=\"spell-count\">" . $result->num_rows . " spells recorded in the grimoire.</p>");
      }

      $conn->close();

    ?>

    <div class="actions">
      <a class="spell-link" href="BobPDFCreateSpells.php">Start Over</a>
      <a class="spell-link" href="BobPDF.php">Next: Print the Grimoire PDF</a>
    </div>

    </div>

  </body>

</html>
