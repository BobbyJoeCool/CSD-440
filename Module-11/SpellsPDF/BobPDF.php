<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 10/05/2026
  Assignment: Module 11.2 - PDFs (BobPDF.php)
  Purpose: Start page for the Module 11.2 assignment. It checks that
           the Module 8 spellbook table exists and has spells in it,
           then offers a button that opens BobPDFGrimoire.php, which
           builds the spellbook PDF, in a new tab. If the table is
           missing or empty, the page points to the set-up steps
           carried forward from Module 8 that build and fill it.
-->

<html>

  <head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>

      D&amp;D Spellbook - Print the Grimoire PDF

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap">

    <link rel="stylesheet" type="text/css" href="BobPDF.css">

    <script type="text/javascript">

      // Open the PDF in a new tab, the same way the Module 11 example
      // program opens toPDF.php.
      function openGrimoirePdf() {

        window.open("BobPDFGrimoire.php");
      }

    </script>

  </head>

  <body>

    <div class="container">

    <h1>The Spellbook Grimoire - Printing the Tome</h1>

    <p class="scroll-intro">
      Every spell inked into the grimoire in Module 8, bound into a
      single PDF: an introduction to how spells work in Dungeons &amp;
      Dragons, followed by tables of each spell's statistics and
      descriptions, drawn live from the database.
    </p>

    <?php

      require("BobPDFConfig.php");

      // Check the spellbook before offering the PDF, so a missing or
      // empty table is explained here instead of only inside the PDF.
      // The "@" keeps a failed connection's PHP warning off the page;
      // the failure is reported in the status line below instead.
      $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
      $spellCount = 0;

      if ($conn->connect_error) {

        print("<p class=\"status-line fail\">Could not open the " . DB_NAME . " database: " . escapeForHtml($conn->connect_error) . "</p>\n");

      } else {

        $result = $conn->query("SELECT COUNT(*) AS spellCount FROM " . SPELLS_TABLE);

        if ($result === false) {

          print("<p class=\"status-line fail\">Could not read the " . SPELLS_TABLE . " table: " . escapeForHtml($conn->error) . "</p>\n");

        } else {

          $spellCount = (int) $result->fetch_assoc()["spellCount"];

          if ($spellCount > 0) {

            print("<p class=\"status-line ok\">" . $spellCount . " spells are recorded in " . DB_NAME . "." . SPELLS_TABLE . " and ready to print.</p>\n");

          } else {

            print("<p class=\"status-line fail\">The " . SPELLS_TABLE . " table is empty.</p>\n");
          }
        }

        $conn->close();
      }

    ?>

    <div class="actions">
      <button class="spell-link" type="button" onclick="openGrimoirePdf();">Open the Grimoire PDF</button>
    </div>

    <h2>First time here?</h2>

    <p>
      The PDF is printed from the spellbook database built in Module 8.
      If the status line above shows a problem, run these three pages
      in order once to build and fill it, then come back here:
    </p>

    <ol class="setup-steps">
      <li><a href="BobPDFCreateSpells.php">BobPDFCreateSpells.php</a> creates the DnDSpellsDB database and the Spells_8_PHP table.</li>
      <li><a href="BobPDFInsertSpells.php">BobPDFInsertSpells.php</a> inks the twelve spells into the table.</li>
      <li><a href="BobPDFReadSpells.php">BobPDFReadSpells.php</a> shows the same spells as a web page, with a link back here.</li>
    </ol>

    </div>

  </body>

</html>
