<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 08/18/2026
  Assignment: Module 2 - Damage Table (DamageGrid.php)
  Purpose: Uses a PHP nested loop to build an HTML table showing random
           damage rolled by different weapons and spells across five
           rounds of combat.
-->

<html>

  <head>

    <title>

      D&D Damage Table

    </title>

  </head>

  <body>

    <table border="1" cellpadding="6">

      <caption>

        Damage Dealt Per Round

      </caption>

      <thead>

        <tr>

          <th>Weapon / Spell</th>
          <th>Round 1</th>
          <th>Round 2</th>
          <th>Round 3</th>
          <th>Round 4</th>
          <th>Round 5</th>

        </tr>

      </thead>

      <tbody>

        <?php

          // This is the list of weapons and spells shown down the left
          // side of the table, one row for each.
          $combatants = array("Longsword", "Fireball", "Dagger", "Lightning Bolt", "Warhammer");

        ?>

        <?php // Go through the list above one weapon or spell at a time, to build one table row for each. ?>
        <?php for ($row = 0; $row < count($combatants); ++$row): ?>

          <tr>

            <td><?php // Show that weapon or spell's name in the first column of the row. ?><?php print($combatants[$row]); ?></td>

            <?php // Repeat everything below five times, once for each round shown across the row. ?>
            <?php for ($col = 0; $col < 5; ++$col): ?>

              <td><?php // Pick a random damage number between 1 and 12 and show it in this cell. ?><?php print(rand(1, 12)); ?></td>

            <?php endfor; ?>

          </tr>

        <?php endfor; ?>

      </tbody>

    </table>

  </body>

</html>
