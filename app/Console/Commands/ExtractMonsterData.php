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
                            {--id=all : Output specific ID}
                            {--names : Extract monster names}
                            {--data : Extract monster data}
                            {--special-attacks : Extract monster special attack data}
                            {--monsters : Extract monster bitmaps}
                            {--players : Extract player bitmaps}
                            {--weapons : Extract weapon data}
                            {--ranged-weapons : Extract ranged weapon data}
                            {--armors : Extract armor data}
                            {--shields : Extract shield data}
                            {--quests : Extract quest object bitmaps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract monster data from game save file';

    /**
     * Output bitmap.
     */
    private function outputBitmap($bitmap)
    {
        for ($y = 0; $y < 16; $y++) {
            $line = "";

            for ($x = 0; $x < 16; $x++) {
                if ($bitmap[$x][$y] == 1) {
                    $line .= "#";
                } else {
                    $line .= ".";
                }
            }

            $this->info($line);
        }
    }

    /**
     * Convert bitmap to piskel.
     */
    private function convertBitmapToPiskel($bitmap, $name, $description)
    {
        $im = imagecreatetruecolor(16, 16);

        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparent);

        $black = imagecolorallocate($im, 0, 0, 0);

        for ($x = 0; $x < 16; $x++) {
            for ($y = 0; $y < 16; $y++) {
                if ($bitmap[$x][$y] === 1) {
                    imagesetpixel($im, $x, $y, $black);
                }
            }
        }

        ob_start();
        imagepng($im);
        $binary = ob_get_clean();

        $base64 = base64_encode($binary);

        imagedestroy($im);

        // ---

        $layers = [];

        $layer = [];
        $layer["name"] = $name;
        $layer["frameCount"] = 1;
        $layer["base64PNG"] = "data:image/png;base64," . $base64;

        $layers[] = json_encode($layer);

        $piskel = [];
        $piskel["name"] = $name;
        $piskel["description"] = $description;
        $piskel["fps"] = 12;
        $piskel["width"] = 16;
        $piskel["height"] = 16;
        $piskel["layers"] = $layers;

        $top = [];
        $top["modelVersion"] = 2;
        $top["piskel"] = $piskel;

        return $top;
    }

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

            $bytes = unpack("c*", fread($fp, 10));

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

            $bytes = unpack("c*", fread($fp, 10));

            print_r($bytes);

            $maxHitPoints = 6 * $bytes[1];

            $defense = $bytes[2];

            $attackDamage = abs($bytes[3]);
            $attackRanged = ($bytes[3] < 0);

            $maxDamage = $bytes[4];

            $specialAttackChance = $bytes[5];

            $specialAttackId = abs($bytes[6]);
            $specialAttackRanged = ($bytes[6] < 0);

            //$value7 = $bytes[7];
            //$value8 = $bytes[8];

            //$value9 = $bytes[9];
            //$value10 = $bytes[10];

            $items[] = [
                $i + 1,
                $name,
                $maxHitPoints,
                $defense,
                $attackDamage,
                $attackRanged,
                $maxDamage,
                $specialAttackChance,
                $specialAttackId,
                $specialAttackRanged,
                //$value7,
                //$value8,
                //$value9,
                //$value10,
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
        $items = [];

        $monsters = [];
        $attacks = [];

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

            $monster = (int) ($i / 2) + 1;

            $attack = ($i % 2) == 1;

            if ($attack) {
                $attacks[] = $bitmap;
            } else {
                $monsters[] = $bitmap;
            }
        }

        for ($i = 0; $i < 16; $i++) {
            $items[] = [$i + 1, $monsters[$i], $attacks[$i]];
        }

        return $items;
    }

    /**
     * Extract player bitmaps from game save file.
     */
    private function getPlayerBitmaps($fp)
    {
        $items = [];

        $players = [];
        $attacks = [];

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

            $player = (int) ($i / 2) + 1;

            $attack = ($i % 2) == 1;

            if ($attack) {
                $attacks[] = $bitmap;
            } else {
                $players[] = $bitmap;
            }
        }

        for ($i = 0; $i < 4; $i++) {
            $items[] = [$i + 1, $players[$i], $attacks[$i]];
        }

        return $items;
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

            $items[] = [$i + 1, $weapon, $damage, $cost, $ammo];
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

            $items[] = [$i + 1, $armor, $protection, $cost];
        }

        return $items;
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

            $items[] = [$i + 1, $shield, $protection, $cost];
        }

        return $items;
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

        if ($this->option('id') === 'all') {
            $all = true;
        } else {
            $all = false;

            $id = intval($this->option('id')) - 1;

            if ($id < 0) {
                $this->error('ID must be 1...N!');
                return;
            }
        }

        if ($this->option('monsters')) {
            if ($all) {
                $this->error('Must specify ID in 1...N!');
                return;
            }
        }

        $fp = fopen($filename, "r");

        if ($this->option('names')) {
            $items = $this->getMonsterNames($fp);

            $headers = ['ID', 'Name'];

            $this->table($headers, $items);
        }

        if ($this->option('data')) {
            $headers = [
                'ID',
                'Name',
                'Max Hit Points',
                'Defense',
                'Attack Damage',
                'Is Ranged',
                'Max Damage',
                'Special Attack Chance',
                'Special Attack',
                'Is Ranged',
                //'Val7',
                //'Val8',
                //'Val9',
                //'Val10',
            ];

            $items = $this->getMonsterData($fp);

            $this->table($headers, $items);
        }

        if ($this->option('special-attacks')) {
            $headers = ['ID', 'Special Attack'];

            $items = $this->getSpecialAttacks($fp);

            $this->table($headers, $items);
        }

        if ($this->option('monsters')) {
            $items = $this->getMonsterBitmaps($fp);

            if (!isset($items[$id])) {
                $this->error('ID must be 1...N!');
                return;
            }

            $items = $items[$id];

            $id = $items[0];
            $monster = $items[1];
            $attack = $items[2];

            // ---

            $this->outputBitmap($monster);

            $output = "monster-" . $id . ".piskel";

            $piskel = $this->convertBitmapToPiskel($monster, "monster-" . $id, "monster-" . $id);

            file_put_contents($output, json_encode($piskel));

            $this->info("Monster bitmap saved in piskel format: " . $output);

            // ---

            $this->outputBitmap($attack);

            $output = "monster-" . $id . "-attack.piskel";

            $piskel = $this->convertBitmapToPiskel($attack, "attack-" . $id, "attack-" . $id);

            file_put_contents($output, json_encode($piskel));

            $this->info("Monster attack bitmap saved in piskel format: " . $output);
        }

        /*
        if ($this->option('players')) {
        $this->getPlayerBitmaps($fp);
        }
         */

        if ($this->option('weapons')) {
            $headers = ['ID', 'Weapon', 'Damage'];

            $items = $this->getWeapons($fp);

            $this->table($headers, $items);
        }

        if ($this->option('ranged-weapons')) {
            $headers = ['ID', 'Weapon', 'Damage', 'Cost', 'Ammo'];

            $items = $this->getRangedWeapons($fp);

            $this->table($headers, $items);
        }

        if ($this->option('armors')) {
            $headers = ['ID', 'Armor', 'Protection', 'Cost'];

            $items = $this->getArmors($fp);

            $this->table($headers, $items);
        }

        if ($this->option('shields')) {
            $headers = ['ID', 'Shield', 'Protection', 'Cost'];

            $items = $this->getShields($fp);

            $this->table($headers, $items);
        }

        /*
        if ($this->option('quests')) {
        $this->getQuestObjectBitmaps($fp);
        }
         */

        fclose($fp);
    }
}
