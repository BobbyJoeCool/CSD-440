<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 10/05/2026
  Assignment: Module 11.2 - PDFs (BobPDFCreateSpells.php)
  Purpose: First of three set-up steps, carried forward from Module 8.2,
           that build the D&D spellbook the Module 11.2 PDF is printed
           from. This page connects to the local MySQL server,
           creates the DnDSpellsDB database if it is not already there,
           and (re)creates the Spells_8_PHP table with one column for
           every field my Dragon Ledger campaign tracker keeps on a
           spell: slug, sourceId, name, edition, level, school,
           castingTime, range, components, material, duration,
           concentration, ritual, classes, description, and
           higherLevels. Run this page first, then BobPDFInsertSpells.php,
           then BobPDFReadSpells.php, and finally BobPDF.php.
-->

<html>

  <head>

    <title>

      D&amp;D Spellbook - Step 1: Create the Table

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="BobPDF.css">

  </head>

  <body>

    <div class="container">

    <h1>The Spellbook Grimoire - Binding the Pages</h1>

    <p class="scroll-intro">
      Before a single spell can be recorded, the grimoire itself must
      be bound. This page prepares the tome: the database that houses
      it, and the table that will hold every spell entry.
    </p>

    <?php

      require("BobPDFConfig.php");

      // Print one line of status, marked with a success or failure
      // rune depending on whether the step it describes worked.
      function printStatusLine($message, $succeeded) {

        $cssClass = $succeeded ? "ok" : "fail";
        print("<p class=\"status-line " . $cssClass . "\">" . escapeForHtml($message) . "</p>\n");
      }

      // Connect without selecting a database yet, since DnDSpellsDB
      // might not exist on this server the first time this page runs.
      $conn = connectToSpellServer(false);

      printStatusLine("Connected to the MySQL server at " . DB_HOST . ".", true);

      // Create the database if it is not already there. Using
      // "IF NOT EXISTS" means this page can be re-run safely without
      // wiping out data that other pages, or other modules, might
      // already be keeping in the same server.
      $createDatabaseSql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;

      if ($conn->query($createDatabaseSql) === TRUE) {

        printStatusLine("Database " . DB_NAME . " is ready.", true);

      } else {

        printStatusLine("Could not create database " . DB_NAME . ": " . $conn->error, false);
        $conn->close();
        exit;
      }

      $conn->select_db(DB_NAME);

      // Drop the table if a previous run of this page left one behind,
      // so the spellbook always starts from a clean, empty tome.
      $dropTableSql = "DROP TABLE IF EXISTS " . SPELLS_TABLE;

      if ($conn->query($dropTableSql) === TRUE) {

        printStatusLine("Cleared any existing " . SPELLS_TABLE . " table.", true);

      } else {

        printStatusLine("Could not drop table " . SPELLS_TABLE . ": " . $conn->error, false);
      }

      // Build the table with one column per field of the Spell record.
      // "range" is a reserved word in modern MySQL, so it is wrapped in
      // backticks. "concentration" and "ritual" are stored as TINYINT(1),
      // the usual MySQL stand-in for a boolean flag.
      $createTableSql =
        "CREATE TABLE " . SPELLS_TABLE . " (" .
        "slug VARCHAR(60) NOT NULL PRIMARY KEY, " .
        "sourceId VARCHAR(40) NOT NULL, " .
        "name VARCHAR(80) NOT NULL, " .
        "edition VARCHAR(10) NULL, " .
        "level TINYINT UNSIGNED NOT NULL, " .
        "school VARCHAR(30) NOT NULL, " .
        "castingTime VARCHAR(90) NOT NULL, " .
        "`range` VARCHAR(50) NOT NULL, " .
        "components VARCHAR(20) NOT NULL, " .
        "material VARCHAR(150) NULL, " .
        "duration VARCHAR(60) NOT NULL, " .
        "concentration TINYINT(1) NOT NULL, " .
        "ritual TINYINT(1) NOT NULL, " .
        "classes TEXT NOT NULL, " .
        "description TEXT NOT NULL, " .
        "higherLevels TEXT NULL, " .
        "extraData TEXT NULL" .
        ")";

      if ($conn->query($createTableSql) === TRUE) {

        printStatusLine("Table " . SPELLS_TABLE . " created successfully.", true);

      } else {

        printStatusLine("Could not create table " . SPELLS_TABLE . ": " . $conn->error, false);
      }

      $conn->close();

    ?>

    <div class="actions">
      <a class="spell-link" href="BobPDFInsertSpells.php">Next: Ink the Spells</a>
    </div>

    </div>

  </body>

</html>
