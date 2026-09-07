<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 09/03/2026
  Assignment: Module 6 - MyInteger (BobMyInteger.php)
  Purpose: Defines a class, BobMyInteger, that holds a single integer set
           through its constructor. The class has isEven(), isOdd(), and
           isPrime() methods plus a getter and setter for the stored
           value. Two instances are created, using the hit points of a
           goblin and an ancient red dragon as the integers, and every
           method is exercised and displayed for both. The assignment
           itself is just a plain number wrapper, so there is not much
           room for a Dungeons and Dragons theme here beyond the two
           values chosen and the flavor text around them.
-->

<html>

  <head>

    <title>

      D&amp;D MyInteger Class Test

    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&amp;family=EB+Garamond:ital@0;1&amp;display=swap" rel="stylesheet">

    <style type="text/css">

      :root {
        --parchment: #ecdcb0;
        --parchment-dark: #ddc491;
        --parchment-shadow: #c9ad78;
        --ink: #2c1a0e;
        --wood: #3b2418;
        --wood-dark: #241209;
        --gold: #b8860b;
        --gold-light: #d4af37;
        --blood: #6b1414;
      }

      body {
        margin: 0;
        padding: 40px 20px;
        font-family: "EB Garamond", Georgia, "Times New Roman", serif;
        font-size: 18px;
        color: var(--ink);
        background: radial-gradient(ellipse at center, #4a3220 0%, #1c1108 100%);
        min-height: 100vh;
        box-sizing: border-box;
      }

      .container {
        max-width: 800px;
        margin: 0 auto;
        background: var(--parchment);
        border: 6px double var(--gold);
        outline: 2px solid var(--wood-dark);
        outline-offset: -14px;
        border-radius: 4px;
        padding: 40px 50px;
        box-shadow: 0 0 0 4px var(--wood-dark), 0 20px 60px rgba(0, 0, 0, 0.7);
      }

      h1 {
        font-family: "Cinzel", Georgia, serif;
        font-weight: 700;
        text-align: center;
        color: var(--blood);
        text-shadow: 1px 1px 0 var(--gold-light);
        letter-spacing: 2px;
        text-transform: uppercase;
        border-bottom: 3px double var(--gold);
        padding-bottom: 16px;
        margin-bottom: 30px;
      }

      h2 {
        font-family: "Cinzel", Georgia, serif;
        color: var(--wood);
        border-bottom: 1px solid var(--gold);
        padding-bottom: 6px;
        margin-top: 40px;
        letter-spacing: 1px;
      }

      p {
        line-height: 1.5;
      }

      .report {
        background: var(--parchment-dark);
        border: 2px solid var(--gold);
        border-radius: 4px;
        padding: 16px 24px;
        margin: 16px 0 28px;
        box-shadow: inset 0 0 12px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0, 0, 0, 0.2);
      }

      .report h3 {
        font-family: "Cinzel", Georgia, serif;
        color: var(--blood);
        border-bottom: 1px dashed var(--gold);
        padding-bottom: 4px;
        margin-top: 0;
        margin-bottom: 10px;
      }

      .report p {
        margin: 4px 0;
      }

      .report strong {
        color: var(--wood);
      }

    </style>

  </head>

  <body>

    <div class="container">

    <h1>MyInteger Class Test</h1>

    <?php

      // Wraps a single integer and answers a few basic questions about
      // it: whether it is even, whether it is odd, and whether it is
      // prime. The value can be read or replaced after construction.
      class BobMyInteger {

        // The one integer this object holds.
        private $value;

        // Set the starting value when the object is created.
        function __construct($value) {

          $this->value = $value;
        }

        // Getter: returns the currently stored value.
        function getValue() {

          return $this->value;
        }

        // Setter: replaces the stored value with a new one.
        function setValue($value) {

          $this->value = $value;
        }

        // True when the stored value divides evenly by two.
        function isEven() {

          return ($this->value % 2) === 0;
        }

        // True when the stored value does not divide evenly by two.
        function isOdd() {

          return !$this->isEven();
        }

        // True when the stored value is a prime number. Checks every
        // whole number from 2 up to the square root of the value,
        // since a factor larger than that would always have a matching
        // factor smaller than it.
        function isPrime() {

          if ($this->value < 2) {

            return false;
          }

          for ($divisor = 2; $divisor <= sqrt($this->value); ++$divisor) {

            if ($this->value % $divisor === 0) {

              return false;
            }
          }

          return true;
        }
      }

      // Print every method's result for one BobMyInteger instance,
      // labeled with a short description of what the value represents.
      function printIntegerReport($label, $myInteger) {

        print("<div class=\"report\">");
        print("<h3>" . $label . "</h3>");
        print("<p><strong>Value:</strong> " . $myInteger->getValue() . "</p>");
        print("<p><strong>Even?</strong> " . ($myInteger->isEven() ? "Yes" : "No") . "</p>");
        print("<p><strong>Odd?</strong> " . ($myInteger->isOdd() ? "Yes" : "No") . "</p>");
        print("<p><strong>Prime?</strong> " . ($myInteger->isPrime() ? "Yes" : "No") . "</p>");
        print("</div>");
      }

    ?>

    <?php

      // A goblin's hit points, a small number on the low end of the
      // scale, used to test the class against a modest value.
      $goblinHitPoints = new BobMyInteger(7);

      // An ancient red dragon's hit points, a large number, used to
      // test the class against something at the other end of the scale.
      $dragonHitPoints = new BobMyInteger(256);

    ?>

    <?php printIntegerReport("Goblin Hit Points", $goblinHitPoints); ?>

    <?php printIntegerReport("Ancient Red Dragon Hit Points", $dragonHitPoints); ?>

    <h2>Testing the Setter</h2>

    <?php

      // The goblin takes a hit and drops to 3 hit points. setValue()
      // replaces the stored value, and the report below shows every
      // method re-evaluating against the new number.
      $goblinHitPoints->setValue(3);

    ?>

    <?php printIntegerReport("Goblin Hit Points After Taking Damage", $goblinHitPoints); ?>

    </div>

  </body>

</html>
