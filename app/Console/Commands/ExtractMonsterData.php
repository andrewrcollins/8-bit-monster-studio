<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExtractMonsterData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extract:monster
                            {file : Game save file filename}
                            {--format=text : Output data as "text" or "json"}
                            {--names : Extract monster names}
                            {--data : Extract monster data}
                            {--special-attacks : Extract monster special attacks}
                            {--monsters : Extract monster bitmaps}
                            {--players : Extract player bitmaps}
                            {--weapons : Extract weapon bitmaps}
                            {--ranged-weapons : Extract ranged weapon bitmaps}
                            {--armors : Extract armor bitmaps}
                            {--shields : Extract shield bitmaps}
                            {--quests : Extract quest object bitmaps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract monster data from game save file';

    /**
     * Extract monster names from game save file.
     */
    private function getMonsterNames($fp)
    {
        $items = [];

        fseek($fp, 3914);

        for ($i = 0; $i < 56; $i++) {
            $name = fread($fp, 12);
            $name = trim($name);
            $name = strtolower($name);
            $name = ucwords($name);

            $bytes = unpack("C*", fread($fp, 10));

            $items[] = [$i + 1, $name];
        }

        return $items;
    }

    /**
     * Extract monster data from game save file.
     */
    private function getMonsterData($fp)
    {
        $items = [];

        fseek($fp, 3914);

        for ($i = 0; $i < 56; $i++) {
            $name = fread($fp, 12);
            $name = trim($name);
            $name = strtolower($name);
            $name = ucwords($name);

            $bytes = unpack("C*", fread($fp, 10));

            $value1 = $bytes[1];
            $value2 = $bytes[2];
            $value3 = $bytes[3];
            $value4 = $bytes[4];
            $value5 = $bytes[5];
            $value6 = $bytes[6];
            $value7 = $bytes[7];
            $value8 = $bytes[8];
            $value9 = $bytes[9];
            $value10 = $bytes[10];

            $items[] = [
                $i + 1,
                $name,
                $value1,
                $value2,
                $value3,
                $value4,
                $value5,
                $value6,
                $value7,
                $value8,
                $value9,
                $value10,
            ];
        }

        return $items;
    }

    /**
     * Extract monster special attacks from game save file.
     */
    private function getSpecialAttacks($fp)
    {
        $items = [];

        fseek($fp, 5146);

        for ($i = 0; $i < 20; $i++) {
            $item = fread($fp, 15);
            $item = trim($item);
            $item = strtolower($item);
            $item = ucwords($item);

            $items[] = [$i + 1, $item];

            $byte = fread($fp, 1);
        }

        return $items;
    }

    /**
     * Extract monster bitmaps from game save file.
     */
    private function getMonsterBitmaps($fp)
    {
        fseek($fp, 5466);

        for ($i = 0; $i < 32; $i++) {
            $bytes = unpack("C*", fread($fp, 32));

            $bitmap = array_fill(0, 16, array_fill(0, 16, 0));

            $y = 0;

            for ($j = 1; $j <= 16; $j++) {
                $byte = $bytes[$j];

                for ($x = 0; $x < 4; $x++) {
                    if ((0x80 >> $x) & $byte) {
                        $bitmap[$x][$y] = 1;
                    }
                }

                for ($x = 0; $x < 4; $x++) {
                    if ((0x08 >> $x) & $byte) {
                        $bitmap[$x + 4][$y] = 1;
                    }
                }

                $y++;
            }

            $y = 0;

            for ($j = 17; $j <= 32; $j++) {
                $byte = $bytes[$j];

                for ($x = 0; $x < 4; $x++) {
                    if ((0x80 >> $x) & $byte) {
                        $bitmap[$x + 8][$y] = 1;
                    }
                }

                for ($x = 0; $x < 4; $x++) {
                    if ((0x08 >> $x) & $byte) {
                        $bitmap[$x + 12][$y] = 1;
                    }
                }

                $y++;
            }

            $monster = (int) ($i / 2);

            $attack = ($i % 2) == 1;

            $file = "monster-" . $monster . ($attack ? "-attack" : "") . ".png";

            echo $file . "\n";

            for ($y = 0; $y < 16; $y++) {
                for ($x = 0; $x < 16; $x++) {
                    if ($bitmap[$x][$y] == 1) {
                        echo "#";
                    } else {
                        echo ".";
                    }
                }
                echo "\n";
            }
        }
    }

    /**
     * Extract player bitmaps from game save file.
     */
    private function getPlayerBitmaps($fp)
    {
        fseek($fp, 3656);

        for ($i = 0; $i < 8; $i++) {
            $bytes = unpack("C*", fread($fp, 32));

            $bitmap = array_fill(0, 16, array_fill(0, 16, 0));

            $y = 0;

            for ($j = 1; $j <= 16; $j++) {
                $byte = $bytes[$j];

                for ($x = 0; $x < 4; $x++) {
                    if ((0x80 >> $x) & $byte) {
                        $bitmap[$x][$y] = 1;
                    }
                }

                for ($x = 0; $x < 4; $x++) {
                    if ((0x08 >> $x) & $byte) {
                        $bitmap[$x + 4][$y] = 1;
                    }
                }

                $y++;
            }

            $y = 0;

            for ($j = 17; $j <= 32; $j++) {
                $byte = $bytes[$j];

                for ($x = 0; $x < 4; $x++) {
                    if ((0x80 >> $x) & $byte) {
                        $bitmap[$x + 8][$y] = 1;
                    }
                }

                for ($x = 0; $x < 4; $x++) {
                    if ((0x08 >> $x) & $byte) {
                        $bitmap[$x + 12][$y] = 1;
                    }
                }

                $y++;
            }

            $player = (int) ($i / 2);

            $attack = ($i % 2) == 1;

            $file = "player-" . $player . ($attack ? "-attack" : "") . ".png";

            echo $file . "\n";

            for ($y = 0; $y < 16; $y++) {
                for ($x = 0; $x < 16; $x++) {
                    if ($bitmap[$x][$y] == 1) {
                        echo "#";
                    } else {
                        echo ".";
                    }
                }
                echo "\n";
            }
        }
    }

    /**
     * Extract weapons from game save file.
     */
    private function getWeapons($fp)
    {
        $items = [];

        fseek($fp, 9080);

        for ($i = 0; $i < 8; $i++) {
            $weapon = fread($fp, 15);
            $weapon = trim($weapon);
            $weapon = strtolower($weapon);
            $weapon = ucwords($weapon);

            $damage = unpack("C", fread($fp, 1));
            $damage = $damage[1];

            $items[] = [$i + 1, $weapon, $damage];

            fread($fp, 2);
        }

        return $items;
    }

    /**
     * Extract ranged weapons from game save file.
     */
    private function getRangedWeapons($fp)
    {
        $items = [];

        fseek($fp, 9224);

        for ($i = 0; $i < 6; $i++) {
            $weapon = fread($fp, 15);
            $weapon = trim($weapon);
            $weapon = strtolower($weapon);
            $weapon = ucwords($weapon);

            $damage = unpack("C", fread($fp, 1));
            $damage = $damage[1];

            $cost = unpack("C", fread($fp, 1));
            $cost = $cost[1];

            fread($fp, 4);

            $ammo = fread($fp, 13);
            $ammo = trim($ammo);
            $ammo = strtolower($ammo);
            $ammo = ucwords($ammo);

            $items[] = [$i, $weapon, $damage, $cost, $ammo];
        }

        return $items;
    }

    /**
     * Extract armors from game save file.
     */
    private function getArmors($fp)
    {
        $items = [];

        fseek($fp, 9496);

        for ($i = 0; $i < 6; $i++) {
            $armor = fread($fp, 15);
            $armor = trim($armor);
            $armor = strtolower($armor);
            $armor = ucwords($armor);

            $protection = unpack("C", fread($fp, 1));
            $protection = $protection[1];

            $cost = unpack("C", fread($fp, 1));
            $cost = $cost[1];

            fread($fp, 1);

            $items[] = [$i, $armor, $protection, $cost];
        }

        $this->table(['ID', 'Armor', 'Protection', 'Cost'], $items);
    }

    /**
     * Extract shields from game save file.
     */
    private function getShields($fp)
    {
        $items = [];

        fseek($fp, 9640);

        for ($i = 0; $i < 4; $i++) {
            $shield = fread($fp, 15);
            $shield = trim($shield);
            $shield = strtolower($shield);
            $shield = ucwords($shield);

            $protection = unpack("C", fread($fp, 1));
            $protection = $protection[1];

            $cost = unpack("C", fread($fp, 1));
            $cost = $cost[1];

            fread($fp, 1);

            $items[] = [$i, $shield, $protection, $cost];
        }

        $this->table(['ID', 'Shield', 'Protection', 'Cost'], $items);
    }

    /**
     * Extract quest object bitmaps from game save file.
     */
    private function getQuestObjectBitmaps($fp)
    {
        fseek($fp, 11024);

        for ($i = 0; $i < 32; $i++) {
            $bytes = unpack("C*", fread($fp, 32));

            $bitmap = array_fill(0, 16, array_fill(0, 16, 0));

            $y = 0;

            for ($j = 1; $j <= 16; $j++) {
                $byte = $bytes[$j];

                for ($x = 0; $x < 4; $x++) {
                    if ((0x80 >> $x) & $byte) {
                        $bitmap[$x][$y] = 1;
                    }
                }

                for ($x = 0; $x < 4; $x++) {
                    if ((0x08 >> $x) & $byte) {
                        $bitmap[$x + 4][$y] = 1;
                    }
                }

                $y++;
            }

            $y = 0;

            for ($j = 17; $j <= 32; $j++) {
                $byte = $bytes[$j];

                for ($x = 0; $x < 4; $x++) {
                    if ((0x80 >> $x) & $byte) {
                        $bitmap[$x + 8][$y] = 1;
                    }
                }

                for ($x = 0; $x < 4; $x++) {
                    if ((0x08 >> $x) & $byte) {
                        $bitmap[$x + 12][$y] = 1;
                    }
                }

                $y++;
            }

            $file = "artifact-" . $i . ".png";

            echo $file . "\n";

            for ($y = 0; $y < 16; $y++) {
                echo " ";

                for ($x = 0; $x < 16; $x++) {
                    if ($bitmap[$x][$y] == 1) {
                        echo "#";
                    } else {
                        echo ".";
                    }
                }
                echo "\n";
            }
        }
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('file');

        if (!file_exists($filename)) {
            $this->error('Game save file does not exist!');
            return;
        }

        if ($this->option('format') === 'text') {
            $format = 'text';
        } else {
            $format = 'json';
        }

        $fp = fopen($filename, "r");

        if ($this->option('names')) {
            $items = $this->getMonsterNames($fp);

            if ($format === "text") {
                $this->table(['ID', 'Name'], $items);
            } else {
                $this->info(json_encode($items, JSON_PRETTY_PRINT));
            }
        }

        if ($this->option('data')) {
            $items = $this->getMonsterData($fp);

            if ($format === "text") {
                $headers = [
                    'ID',
                    'Name',
                    'Value1',
                    'Value2',
                    'Value3',
                    'Value4',
                    'Value5',
                    'Value6',
                    'Value7',
                    'Value8',
                    'Value9',
                    'Value10',
                ];

                $this->table($headers, $items);
            } else {
                $this->info(json_encode($items, JSON_PRETTY_PRINT));
            }
        }

        if ($this->option('special-attacks')) {
            $items = $this->getSpecialAttacks($fp);

            if ($format === "text") {
                $this->table(['ID', 'Special Attack'], $items);
            } else {
                $this->info(json_encode($items, JSON_PRETTY_PRINT));
            }
        }

        if ($this->option('monsters')) {
            $this->getMonsterBitmaps($fp);
        }

        if ($this->option('players')) {
            $this->getPlayerBitmaps($fp);
        }

        if ($this->option('weapons')) {
            $items = $this->getWeapons($fp);

            if ($format === "text") {
                $this->table(['ID', 'Weapon', 'Damage'], $items);
            } else {
                $this->info(json_encode($items, JSON_PRETTY_PRINT));
            }
        }

        if ($this->option('ranged-weapons')) {
            $items = $this->getRangedWeapons($fp);

            if ($format === "text") {
                $this->table(['ID', 'Weapon', 'Damage', 'Cost', 'Ammo'], $items);
            } else {
                $this->info(json_encode($items, JSON_PRETTY_PRINT));
            }
        }

        if ($this->option('armors')) {
            $this->getArmors($fp);
        }

        if ($this->option('shields')) {
            $this->getShields($fp);
        }

        if ($this->option('quests')) {
            $this->getQuestObjectBitmaps($fp);
        }

        fclose($fp);
    }
}
