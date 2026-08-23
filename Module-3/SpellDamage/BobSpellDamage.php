<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 08/18/2026
  Assignment: Module 3 - Spell Damage Table (SpellDamage.php)
  Purpose: Builds on the Module 2 damage table by rolling each combatant's
           dice and passing them, along with a static modifier, to a
           function, kept in an external file, that returns the damage
           for the round. Weapons roll a single die while spells that
           hit with multiple bolts sum an array of dice instead.
-->

<html>

  <head>

    <title>

      D&D Combat Damage

    </title>

    <?php

      // Bring in the other file so we can use the dice-rolling helpers it has.
      require('diceRoll.php');

    ?>

  </head>

  <body>

    <table border="1" cellpadding="6">

      <caption>

        Weapon &amp; Spell Damage Per Round

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

          // This is the list of every weapon and spell in the table. Each
          // line below is one of them, showing its name, how many dice it
          // rolls, how many sides those dice have, and a flat number added
          // on top of the roll. Magic Missile also has a "darts" number,
          // since it is rolled separately three times instead of once.
          $combatants = array(
            array("name" => "Longsword",     "numDice" => 1,  "dieSides" => 8,  "modifier" => 3),
            array("name" => "Greatsword",    "numDice" => 1,  "dieSides" => 12, "modifier" => 6),
            array("name" => "Firebolt",      "numDice" => 1,  "dieSides" => 10, "modifier" => 0),
            array("name" => "Fireball",      "numDice" => 8,  "dieSides" => 6,  "modifier" => 0),
            array("name" => "Meteor Swarm",  "numDice" => 40, "dieSides" => 6,  "modifier" => 0),
            array("name" => "Magic Missile", "numDice" => 1,  "dieSides" => 4,  "modifier" => 1, "darts" => 3),
          );

        ?>

        <?php // Go through the list above one weapon or spell at a time, to build one table row for each. ?>
        <?php for ($row = 0; $row < count($combatants); ++$row): ?>

          <?php // Grab the details for whichever weapon or spell we are on right now. ?>
          <?php $combatant = $combatants[$row]; ?>

          <tr>

            <td><?php // Show that weapon or spell's name in the first column of the row. ?><?php print($combatant["name"]); ?></td>

            <?php // Repeat everything below five times, once for each round shown across the row. ?>
            <?php for ($col = 0; $col < 5; ++$col): ?>

              <td>

                <?php

                  // Find out how many separate rolls this one needs. Almost
                  // everything only needs one, but Magic Missile needs three.
                  $darts = isset($combatant["darts"]) ? $combatant["darts"] : 1;

                  // Start an empty list to hold the number(s) rolled this round.
                  $results = array();

                  // Do the whole roll as many times as this one calls for.
                  for ($dart = 0; $dart < $darts; ++$dart) {

                    // If it only uses one die, roll that single die and add the flat bonus.
                    if ($combatant["numDice"] == 1) {

                      // Roll the one die, add the bonus number, and save that result.
                      $results[] = rollSpellDamage(rand(1, $combatant["dieSides"]), $combatant["modifier"]);

                    } else {

                      // Otherwise this one uses several dice added together, so start a list for them.
                      $rolls = array();

                      // Roll each of the dice one at a time and save every number.
                      for ($die = 0; $die < $combatant["numDice"]; ++$die) {

                        $rolls[] = rand(1, $combatant["dieSides"]);
                      }

                      // Add all of those dice together plus the bonus, then save that total.
                      $results[] = rollSpellDamageMulti($rolls, $combatant["modifier"]);
                    }
                  }

                  // Show the roll (or rolls, for Magic Missile) in the cell, separated by slashes.
                  print(implode(" / ", $results));

                ?>

              </td>

            <?php endfor; ?>

          </tr>

        <?php endfor; ?>

      </tbody>

    </table>

  </body>

</html>
