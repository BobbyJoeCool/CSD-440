<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 09/14/2026
  Assignment: Module 8.2 - PHP and MySQL (BobInsertSpells.php)
  Purpose: Second of three steps that build the D&D spellbook. This page
           connects to DnDSpellsDB and inks a dozen of the most popular
           5th Edition spells into the Spells_8_PHP table, reading the
           spell data out of spellsConfig.php so the same data can never
           drift from what BobCreateSpells.php built columns for. A
           prepared statement is used instead of building each INSERT as
           a string, which is both the safer way to hand user-controlled
           values to MySQL and the more efficient one, since the same
           compiled statement is reused for all twelve rows.
-->

<html>

  <head>

    <title>

      D&amp;D Spellbook - Step 2: Ink the Spells

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="style.css">

  </head>

  <body>

    <div class="container">

    <h1>The Spellbook Grimoire - Inking the Spells</h1>

    <p class="scroll-intro">
      With the tome bound, a dozen of the most popular spells known to
      adventurers are now inked onto its pages, one row per spell.
    </p>

    <?php

      require("spellsConfig.php");

      function printStatusLine($message, $succeeded) {

        $cssClass = $succeeded ? "ok" : "fail";
        print("<p class=\"status-line " . $cssClass . "\">" . escapeForHtml($message) . "</p>\n");
      }

      $conn = connectToSpellServer(true);

      printStatusLine("Connected to " . DB_NAME . ".", true);

      // One placeholder per column, in the same order the columns were
      // declared in BobCreateSpells.php.
      $insertSql =
        "INSERT INTO " . SPELLS_TABLE . " " .
        "(slug, sourceId, name, edition, level, school, castingTime, `range`, components, material, duration, concentration, ritual, classes, description, higherLevels, extraData) " .
        "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

      $statement = $conn->prepare($insertSql);

      if ($statement === false) {

        printStatusLine("Could not prepare the insert statement: " . $conn->error, false);
        $conn->close();
        exit;
      }

      // "s" for every column here: the two boolean flags are bound as
      // the characters "0" and "1" rather than as native MySQL
      // integers, which keeps every placeholder using the same type
      // character and still stores correctly into the TINYINT(1)
      // columns.
      $statement->bind_param(
        "sssssssssssssssss",
        $slug, $sourceId, $name, $edition, $level, $school, $castingTime,
        $range, $components, $material, $duration, $concentration, $ritual,
        $classesJson, $description, $higherLevels, $extraData
      );

      $insertedCount = 0;

      foreach (getSpellSeedData() as $spell) {

        $slug         = $spell["slug"];
        $sourceId     = $spell["sourceId"];
        $name         = $spell["name"];
        $edition      = $spell["edition"];
        $level        = $spell["level"];
        $school       = $spell["school"];
        $castingTime  = $spell["castingTime"];
        $range        = $spell["range"];
        $components   = $spell["components"];
        $material     = $spell["material"];
        $duration     = $spell["duration"];
        $concentration = $spell["concentration"] ? "1" : "0";
        $ritual        = $spell["ritual"] ? "1" : "0";
        $classesJson   = json_encode($spell["classes"]);
        $description   = $spell["description"];
        $higherLevels  = $spell["higherLevels"];
        $extraData     = null;

        if ($statement->execute()) {

          $insertedCount++;
          printStatusLine($name . " (level " . $level . " " . $school . ")", true);

        } else {

          printStatusLine("Could not insert " . $name . ": " . $statement->error, false);
        }
      }

      $statement->close();
      $conn->close();

      print("<p class=\"spell-count\">" . $insertedCount . " of " . count(getSpellSeedData()) . " spells inked into the grimoire.</p>");

    ?>

    <div class="actions">
      <a class="spell-link" href="BobReadSpells.php">Next: Read the Grimoire</a>
    </div>

    </div>

  </body>

</html>
