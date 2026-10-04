# Module 9.2 - MySQLi Query and Form: The Spellbook Grimoire

Robert Breutzmann
CSD-440, Module 9.2

A five-page PHP application over the MySQL database built in Module 8. The three
Module 8 pages that create the database, insert a dozen 5th Edition spells, and
display them are carried forward here unchanged in purpose, and Module 9 adds an
index that links to everything, a search page, and a form for adding a spell.

## Start here

**Open `BobIndex.php`.** It links to every other page and reports whether the
database is ready, so nothing has to be run in a particular order by hand. The
first time through, follow its first two links in order (Bind the Tome, then Ink
the Spells), because the search and form pages need a table with rows in it.

## Setup: use a separate folder from Module 8

Copy these files into their own folder. Don't put them in the same folder as the
Module 8 submission. Module 8 also has a `spellsConfig.php` and a `style.css`, but
they are older versions. If the Module 9 pages load the Module 8 copies, they fail:
`BobIndex.php` stops with a fatal error because `printStatusLine()` is undefined,
and `BobQuery.php` stops partway through drawing its form. The reverse fails as
well: the Module 8 pages define their own copies of helpers that the Module 9
`spellsConfig.php` also defines, so they cannot run with it. If both modules must
share one folder, copy all eight Module 9 files in together. This replaces the
three Module 8 pages with their Module 9 versions.

| File | What it does |
|------|--------------|
| **`BobIndex.php`** | **Start here.** Links to all five pages and reports the live state of the database: connected or not, table built or not, and how many spells it holds. |
| `BobQuery.php` | Searches the table on any combination of spell name, school of magic, spell level, casting class, and ritual casting. Posts back to itself and runs the search as a prepared statement. |
| `BobForm.php` | Adds a spell through a form. Validates every field, refuses duplicates, writes the row with a prepared statement, then reads it back out and shows it as a spell card. |
| `BobCreateSpells.php` | From Module 8. Creates the `DnDSpellsDB` database and the `Spells_8_PHP` table. |
| `BobInsertSpells.php` | From Module 8. Inserts twelve spells using one prepared statement. |
| `BobReadSpells.php` | From Module 8. Displays every spell in the table, sorted by level and then name. |

Supporting files (not opened directly):

- `spellsConfig.php` - connection settings, shared helper functions, the seed data
  for the twelve spells, and the shared display helpers (navigation bar, status
  line, and spell card) that every page draws with.
- `style.css` - page styling for all five pages.

## Notes

- Every page links to every other page through the navigation bar at the bottom,
  so the book can be read from any page once it is built.
- All user input reaches MySQL through prepared statements, and everything printed
  back to the page goes through `htmlspecialchars()` first.
- `spellsConfig.php` turns mysqli's exception reporting off. Since PHP 8.1 a failed
  query throws instead of returning false, which with XAMPP's `display_errors=Off`
  shows a blank page; with reporting off, each page catches the failure itself and
  prints a readable message, such as telling the visitor to run
  `BobCreateSpells.php` when the table is missing.
