<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN'
  'http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd'>

<!--
  Name: Robert Breutzmann
  Date: 09/03/2026
  Assignment: Module 5 - Customers (BobCustomers.php)
  Purpose: Builds an indexed array of associative arrays holding twelve
           customers of the Ye Olde Adventurer's Guild shop, each with a
           first name, last name, age, and phone number. Displays the
           full roster, then uses array functions (array_filter, usort,
           and array_search) to look up customers by last name, by a
           minimum age, sorted youngest to oldest, and by phone number.
-->

<html>

  <head>

    <title>

      D&amp;D Guild Customer Roster

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
        max-width: 960px;
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

      table {
        width: 100%;
        border-collapse: collapse;
        margin: 16px 0 8px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
      }

      thead th {
        background: var(--wood);
        color: var(--gold-light);
        font-family: "Cinzel", Georgia, serif;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.85em;
        padding: 12px 10px;
        border: 1px solid var(--wood-dark);
      }

      tbody td {
        padding: 10px;
        border: 1px solid var(--parchment-shadow);
      }

      tbody tr:nth-child(odd) {
        background: var(--parchment);
      }

      tbody tr:nth-child(even) {
        background: var(--parchment-dark);
      }

      tbody tr:hover {
        background: #f0dca0;
      }

      em {
        color: var(--blood);
      }

    </style>

  </head>

  <body>

    <div class="container">

    <h1>Ye Olde Adventurer's Guild - Customer Roster</h1>

    <?php

      // The full customer roster. Each customer is an associative array
      // holding a first name, last name, age, and phone number. A few
      // customers share a last name on purpose, to demonstrate the
      // last-name lookup below finding more than one match.
      $customers = array(
        array("firstName" => "Thalia",    "lastName" => "Stormwind",   "age" => 27, "phone" => "555-0142"),
        array("firstName" => "Grimm",     "lastName" => "Ironfist",    "age" => 34, "phone" => "555-0198"),
        array("firstName" => "Elowen",    "lastName" => "Nightshade",  "age" => 22, "phone" => "555-0173"),
        array("firstName" => "Borin",     "lastName" => "Stonehelm",   "age" => 58, "phone" => "555-0119"),
        array("firstName" => "Seraphina", "lastName" => "Duskwhisper", "age" => 41, "phone" => "555-0165"),
        array("firstName" => "Kael",      "lastName" => "Emberforge",  "age" => 29, "phone" => "555-0187"),
        array("firstName" => "Lyra",      "lastName" => "Moonshadow",  "age" => 19, "phone" => "555-0154"),
        array("firstName" => "Dorian",    "lastName" => "Ironfist",    "age" => 45, "phone" => "555-0121"),
        array("firstName" => "Wren",      "lastName" => "Fen",         "age" => 31, "phone" => "555-0138"),
        array("firstName" => "Osric",     "lastName" => "Stormwind",   "age" => 63, "phone" => "555-0110"),
        array("firstName" => "Nissa",     "lastName" => "Thistledown", "age" => 25, "phone" => "555-0192"),
        array("firstName" => "Bram",      "lastName" => "Blackwood",   "age" => 52, "phone" => "555-0146"),
      );

      // Print one customer as a single table row.
      function printCustomerRow($customer) {

        print("<tr>");
        print("<td>" . $customer["firstName"] . "</td>");
        print("<td>" . $customer["lastName"] . "</td>");
        print("<td>" . $customer["age"] . "</td>");
        print("<td>" . $customer["phone"] . "</td>");
        print("</tr>");
      }

      // Print a whole list of customers as a table, reusing the same
      // header row every time so each lookup below looks consistent.
      function printCustomerTable($customerList) {

        if (count($customerList) === 0) {

          print("<p><em>No matching customers found.</em></p>");
          return;
        }

        print("<table border=\"1\" cellpadding=\"6\">");
        print("<thead><tr><th>First Name</th><th>Last Name</th><th>Age</th><th>Phone</th></tr></thead>");
        print("<tbody>");

        foreach ($customerList as $customer) {

          printCustomerRow($customer);
        }

        print("</tbody>");
        print("</table>");
      }

    ?>

    <h2>Full Roster</h2>

    <?php printCustomerTable($customers); ?>

    <h2>Lookup: Last Name is "Stormwind"</h2>

    <?php

      // array_filter keeps only the customers whose lastName field
      // matches the one we are searching for.
      $stormwinds = array_filter($customers, function ($customer) {

        return $customer["lastName"] === "Stormwind";
      });

      printCustomerTable($stormwinds);

    ?>

    <h2>Lookup: Age 40 or Older</h2>

    <?php

      // array_filter again, this time keeping anyone whose age field
      // meets the minimum age we are searching for.
      $minAge = 40;

      $olderCustomers = array_filter($customers, function ($customer) use ($minAge) {

        return $customer["age"] >= $minAge;
      });

      printCustomerTable($olderCustomers);

    ?>

    <h2>Sorted by Age (Youngest to Oldest)</h2>

    <?php

      // usort reorders a copy of the roster in place using a custom
      // comparison function that compares two customers' ages.
      $sortedByAge = $customers;

      usort($sortedByAge, function ($customerA, $customerB) {

        return $customerA["age"] <=> $customerB["age"];
      });

      printCustomerTable($sortedByAge);

    ?>

    <h2>Lookup: Phone Number "555-0187"</h2>

    <?php

      // array_column pulls just the phone numbers into their own
      // indexed array, and array_search finds the position of the one
      // we want inside it. That same position is then used to fetch
      // the matching customer out of the original roster.
      $searchPhone = "555-0187";

      $phoneNumbers = array_column($customers, "phone");

      $foundIndex = array_search($searchPhone, $phoneNumbers);

      if ($foundIndex !== false) {

        printCustomerTable(array($customers[$foundIndex]));

      } else {

        print("<p><em>No customer has that phone number.</em></p>");
      }

    ?>

    </div>

  </body>

</html>
