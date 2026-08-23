<?php

  /*
    Name: Robert Breutzmann
    Date: 08/18/2026
    Assignment: Module 3 - Spell Damage Table (SpellDamage.php)
    Purpose: Holds the functions that turn one or more die rolls into
             the damage a spell deals for the round.
  */

  // Add a static modifier onto a single die roll to get spell damage.
  function rollSpellDamage($dieResult, $staticModifier) {

    return $dieResult + $staticModifier;
  }

  // Sum an array of die rolls, plus a static modifier, for spells that
  // hit with multiple bolts.
  function rollSpellDamageMulti($diceResults, $staticModifier) {

    $total = $staticModifier;

    foreach ($diceResults as $die) {

      $total += $die;
    }

    return $total;
  }

?>
