# Module 11.2 - PDFs: The Spellbook Grimoire PDF

Robert Breutzmann
CSD-440, Module 11.2

Prints every spell from the Module 8 spellbook database (`DnDSpellsDB.Spells_8_PHP`)
into a PDF. The PDF opens with general information about spells in Dungeons & Dragons,
then shows the data in two tables: spell statistics and spell descriptions. Each table
has a header row that repeats on every page and a footer row with totals. Every page
also has a running page header and a "Page X of Y" footer.

## Start here

**Open `BobPDF.php`** and click **Open the Grimoire PDF**.

`BobPDF.php` checks the database first. If it reports that the table is missing or
empty, run the three set-up pages (carried forward from Module 8) once, in order:

| Order | File | What it does |
|-------|------|--------------|
| 1 | `BobPDFCreateSpells.php` | Creates the `DnDSpellsDB` database and the `Spells_8_PHP` table. |
| 2 | `BobPDFInsertSpells.php` | Inserts the twelve spells. |
| 3 | `BobPDFReadSpells.php` | Shows the spells as a web page, with a link to `BobPDF.php`. |

## Files

- `BobPDF.php` - start page; opens the PDF in a new tab.
- `BobPDFGrimoire.php` - builds the PDF with FPDF and sends it to the browser.
- `BobPDFConfig.php` - connection settings, shared helpers, and the spell seed data.
- `BobPDF.css` - styling for the web pages.
- `fpdf.php`, `font/`, `FPDF-license.txt` - the FPDF 1.86 library from
  http://www.fpdf.org/ (by Olivier Plathey), bundled because the FPDF that ships
  with XAMPP is version 1.7 and does not run on PHP 8.

Every file has a `BobPDF` name so it cannot overwrite a Module 8 or 9 file that
is in the same folder.
