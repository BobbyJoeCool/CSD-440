<?php

  /*
    Name: Robert Breutzmann
    Date: 10/05/2026
    Assignment: Module 11.2 - PDFs (BobPDFGrimoire.php)
    Purpose: Builds a PDF of every spell in the Module 8 spellbook
             database. The first page explains what spells are in
             Dungeons & Dragons and how they are described; after that
             the spells are printed in two tables, one for their casting
             statistics and one for their descriptions, each with a
             header row that repeats on every page and a footer row
             that totals up what the table shows. Every page also
             carries a running page header and footer.

    Credits: The PDF is drawn with FPDF 1.86 by Olivier Plathey
             (http://www.fpdf.org/), included in this folder as
             fpdf.php and font/ under its own permissive license
             (FPDF-license.txt). countWrappedLines() below is adapted
             from the NbLines() function in the "Table with MultiCells"
             script on fpdf.org, which is the standard way to work out
             how tall a table row needs to be before drawing it.
  */

  // XAMPP ships its own, much older FPDF on PHP's include path, and
  // it does not run on PHP 8. Naming this folder explicitly makes sure
  // the FPDF 1.86 bundled with this assignment is the one loaded.
  require(__DIR__ . "/fpdf.php");
  require("BobPDFConfig.php");

  // Colors from the grimoire's web stylesheet (BobPDF.css), as the
  // red, green, blue triples FPDF expects, so the PDF matches the
  // spellbook's web pages.
  define("COLOR_LEATHER", array(47, 27, 61));
  define("COLOR_ARCANE", array(138, 79, 255));
  define("COLOR_SILVER_BRIGHT", array(234, 232, 255));
  define("COLOR_PAGE", array(248, 242, 228));
  define("COLOR_PAGE_DARK", array(226, 211, 172));
  define("COLOR_INK", array(42, 31, 19));
  define("COLOR_RULE", array(184, 166, 130));
  define("COLOR_DANGER", array(165, 48, 60));


  // Turn a "#rrggbb" color from getSchoolColors() into the red, green,
  // blue triple FPDF's color functions take.
  function hexToRgb($hexColor) {

    $hexColor = ltrim($hexColor, "#");

    return array(
      hexdec(substr($hexColor, 0, 2)),
      hexdec(substr($hexColor, 2, 2)),
      hexdec(substr($hexColor, 4, 2)),
    );
  }


  // FPDF's built-in fonts only understand the Windows-1252 character
  // set, not the UTF-8 this file and the database use, so any text
  // with characters such as an em dash or curly quote is converted
  // before it is printed. Plain ASCII text passes through unchanged.
  function toPdfText($text) {

    $text = (string) $text;

    if (function_exists("iconv")) {

      $converted = iconv("UTF-8", "windows-1252//TRANSLIT", $text);

      if ($converted !== false) {

        return $converted;
      }
    }

    return $text;
  }


  // FPDF itself only knows how to draw lines, boxes, and text. This
  // subclass adds the grimoire's running page header and footer, plus
  // the helpers that draw a table: a header row that repeats on every
  // page the table runs onto, body rows that grow to fit wrapped text,
  // and a closing footer row.
  class BobSpellbookPDF extends FPDF {

    // The columns of the table currently being drawn, each an array of
    // "label", "width" (in millimeters), and "align" ("L", "C", "R").
    // Kept on the object so a row that has to start a new page can
    // redraw the column headings at the top of that page.
    private $activeColumns = array();

    // Height of one line of text inside a table cell, in millimeters.
    private $tableLineHeight = 4.4;


    // Set the current fill, text, or draw color from one of the
    // COLOR_ constants above, or any other red, green, blue triple.
    private function useColor($colorType, $rgb) {

      if ($colorType === "fill") {

        $this->SetFillColor($rgb[0], $rgb[1], $rgb[2]);

      } else if ($colorType === "text") {

        $this->SetTextColor($rgb[0], $rgb[1], $rgb[2]);

      } else {

        $this->SetDrawColor($rgb[0], $rgb[1], $rgb[2]);
      }
    }


    // FPDF calls Header() automatically at the top of every new page.
    // It prints the document's title on the left, the author and
    // assignment on the right, and an arcane-purple rule beneath them.
    function Header() {

      $this->SetFont("Times", "B", 15);
      $this->useColor("text", COLOR_LEATHER);
      $this->Cell(0, 8, toPdfText("The Spellbook Grimoire"), 0, 0, "L");

      $this->SetFont("Helvetica", "", 9);
      $this->SetX($this->lMargin);
      $this->Cell(0, 8, toPdfText("Robert Breutzmann  |  CSD 440, Module 11.2"), 0, 1, "R");

      $this->useColor("draw", COLOR_ARCANE);
      $this->SetLineWidth(0.6);
      $this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
      $this->SetLineWidth(0.2);

      $this->Ln(5);
      $this->useColor("text", COLOR_INK);
    }


    // FPDF calls Footer() automatically at the bottom of every page.
    // It names where the data came from and when it was printed on the
    // left, and "Page X of Y" on the right. "{nb}" is swapped for the
    // final page count by AliasNbPages() once the document is done.
    function Footer() {

      $this->SetY(-14);

      $this->useColor("draw", COLOR_RULE);
      $this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());

      $this->SetFont("Helvetica", "I", 8);
      $this->useColor("text", COLOR_LEATHER);

      $sourceNote = "Printed live from " . DB_NAME . "." . SPELLS_TABLE . " on " . date("F j, Y");
      $this->Cell(0, 8, toPdfText($sourceNote), 0, 0, "L");

      $this->SetX($this->lMargin);
      $this->Cell(0, 8, "Page " . $this->PageNo() . " of {nb}", 0, 0, "R");
    }


    // A section title in the grimoire's heading style, followed by a
    // thin rule.
    function sectionTitle($title) {

      $this->SetFont("Times", "B", 14);
      $this->useColor("text", COLOR_LEATHER);
      $this->Cell(0, 8, toPdfText($title), 0, 1, "L");

      $this->useColor("draw", COLOR_RULE);
      $this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
      $this->Ln(2);

      $this->useColor("text", COLOR_INK);
    }


    // A paragraph of body text that wraps across the full page width.
    function paragraph($text) {

      $this->SetFont("Times", "", 11);
      $this->useColor("text", COLOR_INK);
      $this->MultiCell(0, 5.2, toPdfText($text), 0, "L");
      $this->Ln(2);
    }


    // A row of small colored swatches, one per school of magic, that
    // serves as the key for the school colors used in the tables.
    function schoolColorKey($schoolColors) {

      $swatchSize = 3.5;
      $entryWidth = ($this->w - $this->lMargin - $this->rMargin) / 4;
      $entryCount = 0;

      $this->SetFont("Helvetica", "", 9);

      foreach ($schoolColors as $schoolName => $hexColor) {

        // Four schools per line, so the eight schools make two lines.
        if ($entryCount > 0 && $entryCount % 4 === 0) {

          $this->Ln(6);
        }

        $entryX = $this->lMargin + ($entryCount % 4) * $entryWidth;
        $entryY = $this->GetY();

        $this->useColor("fill", hexToRgb($hexColor));
        $this->Rect($entryX, $entryY + 1, $swatchSize, $swatchSize, "F");

        $this->SetXY($entryX + $swatchSize + 2, $entryY);
        $this->useColor("text", hexToRgb($hexColor));
        $this->Cell($entryWidth - $swatchSize - 2, 5.5, toPdfText($schoolName), 0, 0, "L");

        $entryCount++;
      }

      $this->Ln(10);
      $this->useColor("text", COLOR_INK);
    }


    // How many lines a MultiCell of the given width will need for the
    // given text in the current font. Adapted from NbLines() in the
    // fpdf.org "Table with MultiCells" script (see Credits above): it
    // walks the text one character at a time, adding up character
    // widths from the font's width table, and counts a new line every
    // time the running width passes what fits in the cell, breaking at
    // the last space the same way MultiCell itself does.
    function countWrappedLines($cellWidth, $text) {

      $characterWidths = $this->CurrentFont["cw"];
      $maxLineWidth = ($cellWidth - 2 * $this->cMargin) * 1000 / $this->FontSize;

      $text = str_replace("\r", "", (string) $text);
      $textLength = strlen($text);

      if ($textLength > 0 && $text[$textLength - 1] === "\n") {

        $textLength--;
      }

      $lastSpace = -1;
      $position = 0;
      $lineStart = 0;
      $lineWidth = 0;
      $lineCount = 1;

      while ($position < $textLength) {

        $character = $text[$position];

        // An explicit line break always starts a new line.
        if ($character === "\n") {

          $position++;
          $lastSpace = -1;
          $lineStart = $position;
          $lineWidth = 0;
          $lineCount++;
          continue;
        }

        if ($character === " ") {

          $lastSpace = $position;
        }

        $lineWidth += $characterWidths[$character];

        // The line is full: wrap at the last space, or mid-word if the
        // line has no space to break at.
        if ($lineWidth > $maxLineWidth) {

          if ($lastSpace === -1) {

            if ($position === $lineStart) {

              $position++;
            }

          } else {

            $position = $lastSpace + 1;
          }

          $lastSpace = -1;
          $lineStart = $position;
          $lineWidth = 0;
          $lineCount++;

        } else {

          $position++;
        }
      }

      return $lineCount;
    }


    // Start a new table: print its title, remember its columns, and
    // draw its header row. If there is not room left on this page for
    // the title, the header row, and at least a couple of rows, the
    // table starts on a fresh page instead of leaving a lonely title
    // at the bottom.
    function beginTable($title, $columns) {

      if ($this->GetY() + 40 > $this->PageBreakTrigger) {

        $this->AddPage();
      }

      $this->sectionTitle($title);
      $this->activeColumns = $columns;
      $this->drawTableHeaderRow();
    }


    // The table's header row: each column's label, bold and light on
    // the grimoire's dark leather color.
    function drawTableHeaderRow() {

      $this->SetFont("Helvetica", "B", 8.5);
      $this->useColor("fill", COLOR_LEATHER);
      $this->useColor("text", COLOR_SILVER_BRIGHT);
      $this->useColor("draw", COLOR_LEATHER);

      foreach ($this->activeColumns as $column) {

        $this->Cell($column["width"], 7, toPdfText($column["label"]), 1, 0, "C", true);
      }

      $this->Ln();
    }


    // One body row of the current table. $cellTexts holds one string
    // per column. $cellColors optionally maps a column index to the
    // RGB color its text should be printed in. Rows alternate between
    // white and parchment so long rows are easy to follow across the
    // page.
    function drawTableRow($cellTexts, $isShadedRow, $cellColors = array()) {

      $this->SetFont("Helvetica", "", 8.5);

      // The row is as tall as its most-wrapped cell, plus a little
      // padding above and below the text.
      $mostLines = 1;

      foreach ($this->activeColumns as $columnIndex => $column) {

        $lineCount = $this->countWrappedLines($column["width"], toPdfText($cellTexts[$columnIndex]));
        $mostLines = max($mostLines, $lineCount);
      }

      $cellPadding = 1.2;
      $rowHeight = $mostLines * $this->tableLineHeight + 2 * $cellPadding;

      // A row is never split across pages. If it will not fit, start a
      // new page and repeat the table's header row at the top of it.
      if ($this->GetY() + $rowHeight > $this->PageBreakTrigger) {

        $this->AddPage();
        $this->drawTableHeaderRow();
        $this->SetFont("Helvetica", "", 8.5);
      }

      if ($isShadedRow) {

        $this->useColor("fill", COLOR_PAGE);

      } else {

        $this->SetFillColor(255, 255, 255);
      }

      $this->useColor("draw", COLOR_RULE);

      $rowTop = $this->GetY();
      $cellLeft = $this->lMargin;

      foreach ($this->activeColumns as $columnIndex => $column) {

        // Draw the cell's box at the full row height first, then write
        // its (possibly wrapped) text inside it with no border.
        $this->Rect($cellLeft, $rowTop, $column["width"], $rowHeight, "DF");

        if (isset($cellColors[$columnIndex])) {

          $this->useColor("text", $cellColors[$columnIndex]);

        } else {

          $this->useColor("text", COLOR_INK);
        }

        $this->SetXY($cellLeft, $rowTop + $cellPadding);
        $this->MultiCell($column["width"], $this->tableLineHeight, toPdfText($cellTexts[$columnIndex]), 0, $column["align"]);

        $cellLeft += $column["width"];
      }

      $this->SetXY($this->lMargin, $rowTop + $rowHeight);
    }


    // The table's footer row: one cell spanning every column, holding
    // the totals for the table, set in bold italics on a darker
    // parchment so it reads as the end of the table.
    function endTable($footerText) {

      $tableWidth = 0;

      foreach ($this->activeColumns as $column) {

        $tableWidth += $column["width"];
      }

      $this->SetFont("Helvetica", "BI", 8.5);

      // Keep the footer row on the same page as at least the end of
      // the table rather than letting it fall onto a page of its own
      // without a header row above it.
      if ($this->GetY() + 8 > $this->PageBreakTrigger) {

        $this->AddPage();
        $this->drawTableHeaderRow();
        $this->SetFont("Helvetica", "BI", 8.5);
      }

      $this->useColor("fill", COLOR_PAGE_DARK);
      $this->useColor("draw", COLOR_LEATHER);
      $this->useColor("text", COLOR_LEATHER);
      $this->Cell($tableWidth, 7, toPdfText($footerText), 1, 1, "L", true);

      $this->activeColumns = array();
      $this->Ln(6);
      $this->useColor("text", COLOR_INK);
    }


    // A boxed error message, used in place of the tables when the
    // spells could not be read from the database.
    function errorBox($message) {

      $this->SetFont("Helvetica", "B", 10);
      $this->useColor("draw", COLOR_DANGER);
      $this->useColor("text", COLOR_DANGER);
      $this->SetFillColor(250, 232, 232);
      $this->MultiCell(0, 6, toPdfText($message), 1, "L", true);
      $this->useColor("text", COLOR_INK);
    }
  }


  // Read every spell out of the Module 8 table, ordered the same way
  // as the grimoire's web page: by level, then by name. Returns an
  // array with the list of spells and, if anything went wrong, a
  // message describing the problem.
  function loadSpellsForPdf() {

    // The "@" keeps a failed connection's PHP warning from being
    // printed into the response, which would corrupt the PDF. The
    // failure is still caught on the next line and reported inside
    // the PDF itself.
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {

      return array(array(), "Could not connect to the " . DB_NAME . " database: " . $conn->connect_error);
    }

    $result = $conn->query("SELECT * FROM " . SPELLS_TABLE . " ORDER BY level ASC, name ASC");

    if ($result === false) {

      $errorMessage = "Could not read the " . SPELLS_TABLE . " table: " . $conn->error;
      $conn->close();

      return array(array(), $errorMessage);
    }

    $spells = array();

    while ($spell = $result->fetch_assoc()) {

      $spells[] = $spell;
    }

    $conn->close();

    if (count($spells) === 0) {

      return array(array(), "The " . SPELLS_TABLE . " table has no spells in it yet.");
    }

    return array($spells, null);
  }


  // Count how many spells match a test, for the tables' footer rows.
  function countSpellsWhere($spells, $test) {

    return count(array_filter($spells, $test));
  }


  // Build "1 Cantrip, 6 1st Level, 1 2nd Level ..." from the spells,
  // in level order, for the statistics table's footer row.
  function summarizeSpellLevels($spells) {

    $levelCounts = array();

    foreach ($spells as $spell) {

      $level = (int) $spell["level"];
      $levelCounts[$level] = isset($levelCounts[$level]) ? $levelCounts[$level] + 1 : 1;
    }

    ksort($levelCounts);

    $levelParts = array();

    foreach ($levelCounts as $level => $spellCount) {

      $levelParts[] = $spellCount . " " . formatSpellLevel($level);
    }

    return implode(", ", $levelParts);
  }


  list($spells, $loadError) = loadSpellsForPdf();
  $schoolColors = getSchoolColors();


  // Create the document: Letter paper in landscape, so the statistics
  // table has room for all of its columns, measured in millimeters.
  // The bottom margin leaves room for the running footer.
  $pdf = new BobSpellbookPDF("L", "mm", "Letter");
  $pdf->AliasNbPages();
  $pdf->SetMargins(12, 10, 12);
  $pdf->SetAutoPageBreak(true, 18);

  $pdf->SetTitle("The Spellbook Grimoire");
  $pdf->SetAuthor("Robert Breutzmann");
  $pdf->SetSubject("CSD 440 Module 11.2 - Dungeons & Dragons spells from the Module 8 database");
  $pdf->SetCreator("BobPDFGrimoire.php with FPDF " . FPDF::VERSION);

  $pdf->AddPage();


  // General information about the topic, before any of the data.
  $pdf->sectionTitle("About Spells in Dungeons & Dragons");

  $pdf->paragraph(
    "In Dungeons & Dragons, a spell is a single, well-defined piece of magic that a character can cast, " .
    "anything from a glowing dart of force to a roaring ball of fire. The Fifth Edition rules (5e) describe " .
    "every spell with the same set of statistics: its level, its school of magic, how long it takes to cast, " .
    "its range, the components it needs, and how long it lasts, followed by a description of what it does. " .
    "That fixed shape is exactly why spells fit so neatly into a database table."
  );

  $pdf->paragraph(
    "Spell levels run from 0 to 9. Level 0 spells, called cantrips, can be cast at will as often as a " .
    "character likes. Casting a spell of 1st level or higher uses up one of the caster's spell slots of that " .
    "level or higher, and many spells grow stronger when cast with a higher-level slot, as their \"At Higher " .
    "Levels\" entry explains."
  );

  $pdf->paragraph(
    "Components are what the caster has to provide: V (verbal) means speaking words of power, S (somatic) " .
    "means precise hand gestures, and M (material) means a particular object, listed in the Material column. " .
    "A concentration spell lasts only as long as the caster keeps their focus on it, and a ritual spell can " .
    "be cast without spending a spell slot by taking ten extra minutes to cast it."
  );

  $pdf->paragraph(
    "Every spell belongs to one of eight schools of magic, which group spells by the way their magic works. " .
    "Each school has its own color, used for the school names in the tables that follow:"
  );

  $pdf->schoolColorKey($schoolColors);

  $pdf->paragraph(
    "The spells in this grimoire come from the System Reference Document 5.1 (SRD 5.1), which Wizards of the " .
    "Coast released under the Creative Commons Attribution 4.0 license, and are stored in the same shape my " .
    "Dragon Ledger campaign tracker uses. They are read live from the " . SPELLS_TABLE . " table in the " .
    DB_NAME . " database built in Module 8, so this PDF always matches what is in the database."
  );


  if ($loadError !== null) {

    // Without data there is nothing to put in the tables, so the PDF
    // says what went wrong and how to fix it instead.
    $pdf->Ln(4);
    $pdf->errorBox(
      $loadError . "\n" .
      "Run BobPDFCreateSpells.php and then BobPDFInsertSpells.php to build and fill the spellbook, " .
      "then open this PDF again."
    );

  } else {

    // Table 1: every spell's casting statistics, one row per spell.
    // The column widths add up to the 255.4 mm between the margins.
    $pdf->beginTable("Spell Statistics", array(
      array("label" => "Spell",        "width" => 34, "align" => "L"),
      array("label" => "Slug",         "width" => 28, "align" => "L"),
      array("label" => "Source",       "width" => 15, "align" => "C"),
      array("label" => "Edition",      "width" => 13, "align" => "C"),
      array("label" => "Level",        "width" => 17, "align" => "C"),
      array("label" => "School",       "width" => 24, "align" => "L"),
      array("label" => "Casting Time", "width" => 38.4, "align" => "L"),
      array("label" => "Range",        "width" => 18, "align" => "L"),
      array("label" => "Comp.",        "width" => 12, "align" => "C"),
      array("label" => "Duration",     "width" => 32, "align" => "L"),
      array("label" => "Conc.",        "width" => 12, "align" => "C"),
      array("label" => "Ritual",       "width" => 12, "align" => "C"),
    ));

    foreach ($spells as $rowNumber => $spell) {

      $schoolColor = isset($schoolColors[$spell["school"]]) ? hexToRgb($schoolColors[$spell["school"]]) : COLOR_INK;

      $pdf->drawTableRow(
        array(
          $spell["name"],
          $spell["slug"],
          $spell["sourceId"],
          $spell["edition"],
          formatSpellLevel($spell["level"]),
          $spell["school"],
          $spell["castingTime"],
          $spell["range"],
          $spell["components"],
          $spell["duration"],
          $spell["concentration"] ? "Yes" : "No",
          $spell["ritual"] ? "Yes" : "No",
        ),
        $rowNumber % 2 === 1,
        array(5 => $schoolColor)
      );
    }

    $concentrationCount = countSpellsWhere($spells, function ($spell) { return (bool) $spell["concentration"]; });
    $ritualCount = countSpellsWhere($spells, function ($spell) { return (bool) $spell["ritual"]; });

    $pdf->endTable(
      "Total: " . count($spells) . " spells  |  " . summarizeSpellLevels($spells) .
      "  |  " . $concentrationCount . " need concentration  |  " . $ritualCount . " can be cast as a ritual"
    );


    // Table 2: what each spell does, who can learn it, and what it
    // costs to cast. These columns hold long text, so this table
    // starts on a fresh page.
    $pdf->AddPage();

    $pdf->beginTable("Spell Descriptions", array(
      array("label" => "Spell",            "width" => 34,   "align" => "L"),
      array("label" => "Classes",          "width" => 36,   "align" => "L"),
      array("label" => "Material",         "width" => 34,   "align" => "L"),
      array("label" => "Description",      "width" => 96.4, "align" => "L"),
      array("label" => "At Higher Levels", "width" => 55,   "align" => "L"),
    ));

    foreach ($spells as $rowNumber => $spell) {

      // "classes" is stored as a JSON array, the way Dragon Ledger
      // saves it, so it is decoded back into a list before printing.
      $classList = json_decode($spell["classes"], true);
      $classList = is_array($classList) ? $classList : array();

      $pdf->drawTableRow(
        array(
          $spell["name"],
          implode(", ", $classList),
          empty($spell["material"]) ? "None" : $spell["material"],
          $spell["description"],
          empty($spell["higherLevels"]) ? "No change" : $spell["higherLevels"],
        ),
        $rowNumber % 2 === 1
      );
    }

    $materialCount = countSpellsWhere($spells, function ($spell) { return !empty($spell["material"]); });
    $higherLevelCount = countSpellsWhere($spells, function ($spell) { return !empty($spell["higherLevels"]); });

    $pdf->endTable(
      "Total: " . count($spells) . " spells described  |  " . $materialCount . " need a material component  |  " .
      $higherLevelCount . " grow stronger at higher levels"
    );

    // The table's last column, extraData, is a spare column Dragon
    // Ledger keeps for future use. While it is empty for every spell it
    // is noted here rather than printed as a column of blanks.
    $extraDataCount = countSpellsWhere($spells, function ($spell) { return !empty($spell["extraData"]); });

    if ($extraDataCount === 0) {

      $pdf->SetFont("Helvetica", "I", 8.5);
      $pdf->MultiCell(0, 4.5, toPdfText(
        "Every column of " . SPELLS_TABLE . " is shown above except extraData, a spare column kept for " .
        "future use that is empty for every spell."
      ), 0, "L");
    }
  }


  // Send the finished PDF to the browser to display inline, with a
  // file name to use if the reader saves it.
  $pdf->Output("I", "BobSpellbookGrimoire.pdf");
