<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 08/29/2026
  Assignment: Module 4 - Palindrome Checker (BobPalindrome.php)
  Purpose: Checks whether a string reads the same forwards and backwards
           using a custom function, then displays the original string,
           its reverse, and whether it is a palindrome. Tests thirty
           Dungeons and Dragons themed strings, nine of which are
           palindromes and twenty-one of which are not.
-->

<html>

  <head>

    <title>

      D&amp;D Palindrome Checker

    </title>

    <link rel="stylesheet" type="text/css" href="style.css">

  </head>

  <body>

    <h1>D&amp;D Palindrome Checker</h1>

    <?php

      // Test whether a string is a palindrome and display the result.
      // The check ignores uppercase versus lowercase so that words like
      // "Level" still count. It prints the original string, the
      // reversed version, and a message saying whether it matched,
      // each wrapped in a styled box.
      function testPalindrome($testString) {

        // Make the string all lowercase so the comparison is fair.
        $lowerString = strtolower($testString);

        // Reverse the lowercase version of the string.
        $reversedString = strrev($lowerString);

        // Compare the lowercase original to its reverse to decide
        // whether the string is a palindrome.
        $isPalindrome = ($lowerString === $reversedString);

        // Pick a class so palindromes and non-palindromes get a
        // different accent color on their box.
        $boxClass = $isPalindrome ? "check-box palindrome" : "check-box not-palindrome";

        print("<div class=\"" . $boxClass . "\">");

        // Show the lowercase string and what it looks like reversed.
        print("<p><strong>String:</strong> " . $lowerString . "</p>");
        print("<p><strong>Reversed:</strong> " . $reversedString . "</p>");

        // Tell the reader whether this one is a palindrome or not.
        if ($isPalindrome) {

          print("<p><strong>Result:</strong> \"" . $lowerString . "\" <em>is</em> a palindrome.</p>");

        } else {

          print("<p><strong>Result:</strong> \"" . $lowerString . "\" is <em>not</em> a palindrome.</p>");
        }

        print("</div>");
      }

    ?>

    <h2>Palindromes</h2>

    <div class="checks-grid">

    <?php

      // "level" reads the same in both directions and is one of the
      // most common words in D&D, as every character has one.
      testPalindrome("Level");

      // "deified" reads the same forwards and backwards, fitting for
      // a cleric who has ascended to godhood.
      testPalindrome("Deified");

      // "reviver" is a palindrome that describes any healer who brings
      // a fallen party member back from the brink.
      testPalindrome("Reviver");

      // "sagas" reads the same both ways, and every campaign is built
      // on the epic sagas of adventurers past.
      testPalindrome("Sagas");

      // "stats" is a palindrome for the six ability scores that define
      // every character: Strength, Dexterity, Constitution, and so on.
      testPalindrome("Stats");

      // "deed" reads the same in reverse, like a heroic deed performed
      // by a knight or paladin sworn to an oath.
      testPalindrome("Deed");

      // "eye" is a short palindrome, and the Eye of Vecna is one of
      // the most powerful and dangerous artifacts in D&D.
      testPalindrome("Eye");

      // "noon" reads the same both ways, the hour when a vampire's
      // power is at its weakest and the sun is highest.
      testPalindrome("Noon");

      // "tenet" is a palindrome meaning a core belief, like the sacred
      // tenets that guide a paladin's oath.
      testPalindrome("Tenet");

    ?>

    </div>

    <h2>Not Palindromes</h2>

    <div class="checks-grid">

    <?php

      // A Beholder is an iconic floating eye monster with deadly rays.
      testPalindrome("Beholder");

      // The Tarrasque is the most feared creature in all of D&D,
      // a nearly unstoppable engine of destruction.
      testPalindrome("Tarrasque");

      // A Mimic is a shapeshifting creature that disguises itself as
      // a treasure chest to ambush unsuspecting adventurers.
      testPalindrome("Mimic");

      // Dragons are the namesake of Dungeons and Dragons, ancient
      // creatures of immense power sorted by color and type.
      testPalindrome("Dragon");

      // Initiative is the roll that decides turn order at the start
      // of every combat encounter.
      testPalindrome("Initiative");

      // A Dungeon is the classic setting for any adventure, full of
      // traps, monsters, and treasure.
      testPalindrome("Dungeon");

      // Goblins are small, cunning creatures that attack in large
      // numbers and are often the first enemies new adventurers face.
      testPalindrome("Goblin");

      // A Wizard is an arcane spellcaster who studies magic from
      // ancient tomes and spellbooks.
      testPalindrome("Wizard");

      // A Paladin is a holy warrior bound by a sacred oath, wielding
      // divine magic alongside martial skill.
      testPalindrome("Paladin");

      // A Sorcerer draws magical power from their bloodline rather
      // than from study, casting spells through sheer willpower.
      testPalindrome("Sorcerer");

      // A Warlock gains their power through a pact with a powerful
      // patron such as a fiend, an archfey, or a Great Old One.
      testPalindrome("Warlock");

      // A Barbarian channels primal rage in battle, shrugging off
      // wounds that would fell a lesser warrior.
      testPalindrome("Barbarian");

      // Kobolds are small reptilian creatures that worship dragons
      // and set elaborate traps to protect their lairs.
      testPalindrome("Kobold");

      // A Lich is an undead spellcaster who has bound their soul to
      // a phylactery to achieve a twisted form of immortality.
      testPalindrome("Lich");

      // An Owlbear is a fearsome hybrid with the body of a bear and
      // the head and talons of a giant owl.
      testPalindrome("Owlbear");

      // A Gelatinous Cube is a nearly transparent ooze that fills
      // an entire dungeon corridor, dissolving anything it engulfs.
      testPalindrome("Gelatinous Cube");

      // A Mind Flayer is a psionic horror from the Underdark that
      // feeds on the brains of intelligent creatures.
      testPalindrome("Mind Flayer");

      // A Rust Monster corrodes and devours metal equipment on
      // contact, the bane of any heavily armored fighter.
      testPalindrome("Rust Monster");

      // An Orc is a fierce, aggressive warrior often found raiding
      // settlements and clashing with other races.
      testPalindrome("Orc");

      // A Druid is a nature-based spellcaster who can shapeshift
      // into animals using Wild Shape.
      testPalindrome("Druid");

      // A Rogue is a stealthy combatant who relies on cunning and
      // sneak attacks rather than brute force.
      testPalindrome("Rogue");

    ?>

    </div>

  </body>

</html>
