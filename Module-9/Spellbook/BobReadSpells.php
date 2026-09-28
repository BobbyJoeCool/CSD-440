<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 09/20/2026
  Assignment: Module 9.2 - MySQLi Query and Form (BobReadSpells.php,
              carried forward from Module 8.2)
  Purpose: Reads every row out of Spells_8_PHP and displays it as a
           grimoire page: one card per spell, sorted by level and then
           name, showing the casting statistics, the classes that can
           learn it, and its description. Where the query page shows
           only the spells that match a search, this page always shows
           the whole book. The cards themselves are drawn by
           renderSpellCard() in spellsConfig.php, so every page of the
           spellbook displays a spell the same way.
-->

<html>

  <head>

    <title>

      D&amp;D Spellbook - Read the Grimoire

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap">

    <link rel="stylesheet" type="text/css" href="style.css">

  </head>

  <body>

    <div class="container">

    <h1>The Spellbook Grimoire</h1>

    <p class="scroll-intro">
      Every spell recorded in the grimoire, drawn live from the
      Spells_8_PHP table and laid out one card per page.
    </p>

    <?php

      // spellsConfig.php supplies the connection helper and the shared
      // display helpers: renderSpellCard() and printSpellbookNav().
      require("spellsConfig.php");

      $conn = connectToSpellServer(true);

      // Sorted the way a spellbook is organized: cantrips first, then
      // by level, and alphabetically inside each level.
      $sql = "SELECT * FROM " . SPELLS_TABLE . " ORDER BY level ASC, name ASC";
      $result = $conn->query($sql);

      if ($result === false) {

        // The usual cause is that the table has not been built yet, so
        // the failure message points at the page that builds it.
        printStatusLine("Could not read the spellbook: " . $conn->error, false);
        printStatusLine("Run BobCreateSpells.php and BobInsertSpells.php first.", false);

      } else if ($result->num_rows === 0) {

        printStatusLine("The grimoire has no spells recorded yet. Run BobInsertSpells.php first.", false);

      } else {

        print("<div class=\"spell-grid\">\n");

        while ($spell = $result->fetch_assoc()) {

          renderSpellCard($spell);
        }

        print("</div>\n");

        print("<p class=\"spell-count\">" . $result->num_rows . " spells recorded in the grimoire.</p>\n");
      }

      $conn->close();

    ?>

    <?php printSpellbookNav("BobReadSpells.php"); ?>

    </div>

  </body>

</html>
