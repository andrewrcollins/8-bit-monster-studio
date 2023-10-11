# Tunnels of Doom

A tiny but powerful editor for monsters.

Used to create 8-bit style graphic elements for games developed with [Phaser](https://phaser.io/).

Inspired by the classic role-playing video game for the TI-99/4A home computer!

<https://en.wikipedia.org/wiki/Tunnels_of_Doom>

---

## TODO

Make a model with a migration

* `Monster` model
* `monsters` table

Make a command

* Extract monster data from game save file
* Output monster data to console
* Save monster data to `monster.json`
* Export bitmaps in `piskel` format

<https://github.com/rostok/piskel-converter>

The command starts from an ancient game save file decoder I wrote years ago.

Make a seeder

* Seed `monsters` table from `monster.json`

Make a view

* View a `Monster`

Store bitmaps in `piskel` format

* <https://github.com/piskelapp/piskel>

Embed bitmap editor

* <https://github.com/piskelapp/piskel-embed>
