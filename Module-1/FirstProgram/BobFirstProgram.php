<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 08/10/2026
  Assignment: Module 1 - First Program (FirstProgram.php)
  Purpose: Displays a Dungeons and Dragons themed message announcing that
           the ritual to stop Tiamat's arrival has failed.
-->

<html>

  <head>

    <title>

      D&D First Program

    </title>

  </head>

  <body>

    <?php

      // Build the message describing the failed ritual and Tiamat's arrival.
      $ritualOutcome = "You have failed to inturrupt the ritual and a 5 headed Dragon comes through the portal.";
      $bossName = "Tiamat approaches.";
      $callToAction = "Roll for initavive.";

      print("<p>" . $ritualOutcome . "</p>");
      print("<p style=\"font-size:1.5rem;\"><strong>" . $bossName . "</strong></p>");
      print("<h1>" . $callToAction . "</h1>");

    ?>

    <?php

      // Track how many party members must roll for initiative.
      $partySize = 4;

      print("<p>All $partySize party members must roll for initiative before the encounter begins.</p>");

    ?>

  </body>

</html>
