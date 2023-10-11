# Tunnels of Doom

A tiny browser for monster data and bitmaps!

<https://en.wikipedia.org/wiki/Tunnels_of_Doom>

## TODO

Make a model with a migration

* `Monster` model
* `monsters` table

Make a command

* Extract monster data from game save file
* Output monster data to console
* Save monster data to `monster.json`

The command starts from an ancient game save file decoder I wrote years ago.

Make a seeder

* Seed `monsters` table from `monster.json`

Make a view

* View a `Monster`

Embed bitmap editor

* <https://github.com/piskelapp/piskel>
* <https://github.com/piskelapp/piskel-embed>
