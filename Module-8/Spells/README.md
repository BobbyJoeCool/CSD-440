# Module 8.2 - PHP and MySQL: The Spellbook Grimoire

Robert Breutzmann
CSD-440, Module 8.2

A three-page PHP application that builds a small D&D spellbook backed by MySQL:
it creates the database and table, inserts a dozen 5th Edition spells, and
displays them as a formatted grimoire.

## Start here

**Open `BobCreateSpells.php` first.**



Each page ends with a link to the next one, so once the first page is open the
rest of the assignment can be reached by clicking through. The pages must be run
in order the first time, because each one depends on what the previous one built.

| Order | File | What it does |
|-------|------|--------------|
| 1 | **`BobCreateSpells.php`** | **Start here.** Creates the `DnDSpellsDB` database and the `Spells_8_PHP` table. |
| 2 | `BobInsertSpells.php` | Inserts twelve spells using a prepared statement. |
| 3 | `BobReadSpells.php` | Reads every row back and displays one card per spell. |

Supporting files (not opened directly):

- `spellsConfig.php` - connection settings, shared helper functions, and the
  seed data for the twelve spells.
- `style.css` - page styling.