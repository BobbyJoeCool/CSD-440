<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 09/20/2026
  Assignment: Module 9.2 - MySQLi Query and Form (BobIndex.php)
  Purpose: The front door of the spellbook. This page links to every
           other page in the assignment - the three that build the
           database from Module 8, the query page that searches it, and
           the form page that adds to it - and reports the live state of
           the database above those links, so a visitor can see whether
           the table exists and how many spells it holds before
           choosing where to go.
-->

<html>

  <head>

    <title>

      D&amp;D Spellbook - Index

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap">

    <link rel="stylesheet" type="text/css" href="style.css">

  </head>

  <body>

    <div class="container">

    <h1>The Spellbook Grimoire</h1>

    <p class="scroll-intro">
      A MySQL-backed grimoire of 5th Edition spells. Bind the tome, ink
      the spells, then search the pages or scribe a new spell of your
      own.
    </p>

    <h2>State of the Grimoire</h2>

    <?php

      // spellsConfig.php supplies the connection settings and the
      // shared display helpers used on every page.
      require("spellsConfig.php");

      // This page connects with a plain mysqli call rather than with
      // connectToSpellServer(), because that helper stops the script on
      // a failed connection. The index is the one page that must keep
      // working when the database is not ready yet: its whole job is to
      // link to BobCreateSpells.php, which is what fixes that.
      $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if ($conn->connect_error) {

        printStatusLine("Cannot reach the " . DB_NAME . " database: " . $conn->connect_error, false);
        printStatusLine("Start MySQL in the XAMPP control panel, then run Bind the Tome below.", false);

      } else {

        printStatusLine("Connected to " . DB_NAME . " on " . DB_HOST . ".", true);

        // COUNT(*) answers both questions at once: whether the table
        // has been built, and how many spells are recorded in it.
        $countResult = $conn->query("SELECT COUNT(*) AS spellCount FROM " . SPELLS_TABLE);

        if ($countResult === false) {

          printStatusLine("The " . SPELLS_TABLE . " table does not exist yet. Run Bind the Tome below.", false);

        } else {

          $countRow = $countResult->fetch_assoc();

          if ($countRow["spellCount"] == 0) {

            printStatusLine("The " . SPELLS_TABLE . " table is built but empty. Run Ink the Spells below.", false);

          } else {

            printStatusLine("The grimoire holds " . $countRow["spellCount"] . " spells.", true);
          }
        }

        $conn->close();
      }

    ?>

    <h2>Pages of the Book</h2>

    <p class="scroll-intro">
      The first three pages build the database and must be run in order
      the first time. After that, any page can be opened directly.
    </p>

    <ul class="index-list">

      <li class="index-entry">
        <a class="index-title" href="BobCreateSpells.php">1. Bind the Tome</a>
        <span class="index-file">BobCreateSpells.php</span>
        <p class="index-blurb">
          Creates the DnDSpellsDB database and the Spells_8_PHP table,
          one column for every field a spell record keeps. Run this
          first.
        </p>
      </li>

      <li class="index-entry">
        <a class="index-title" href="BobInsertSpells.php">2. Ink the Spells</a>
        <span class="index-file">BobInsertSpells.php</span>
        <p class="index-blurb">
          Inserts a dozen of the best known 5th Edition spells using a
          single prepared statement reused for every row.
        </p>
      </li>

      <li class="index-entry">
        <a class="index-title" href="BobReadSpells.php">3. Read the Grimoire</a>
        <span class="index-file">BobReadSpells.php</span>
        <p class="index-blurb">
          Displays every spell in the table as a card, sorted by level
          and then by name.
        </p>
      </li>

      <li class="index-entry">
        <a class="index-title" href="BobQuery.php">Search the Grimoire</a>
        <span class="index-file">BobQuery.php</span>
        <p class="index-blurb">
          Searches the table on whatever a visitor fills in: part of a
          name, a school of magic, a spell level, a class that can cast
          it, or any combination of the four.
        </p>
      </li>

      <li class="index-entry">
        <a class="index-title" href="BobForm.php">Scribe a New Spell</a>
        <span class="index-file">BobForm.php</span>
        <p class="index-blurb">
          Adds a spell of your own to the table through a form, checking
          the entry before it is written and confirming it afterward
          with the finished spell card.
        </p>
      </li>

    </ul>

    <?php printSpellbookNav("BobIndex.php"); ?>

    </div>

  </body>

</html>
