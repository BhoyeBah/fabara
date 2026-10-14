<?php
namespace App\Support;

/** Lit un tableau depuis un fichier Excel (.xlsx) ou CSV : renvoie une liste de lignes (listes de cellules). */
class TableReader
{
    public static function read(string $path): array
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'xlsx' ? self::xlsx($path) : self::csv($path);
    }

    private static function csv(string $path): array
    {
        $h = fopen($path, 'r');
        $first = preg_replace('/^\xEF\xBB\xBF/', '', (string) fgets($h));
        $delim = substr_count($first, ';') >= substr_count($first, ',') ? ';' : ',';
        $rows = [str_getcsv($first, $delim)];
        while (($r = fgetcsv($h, 0, $delim)) !== false) {
            $rows[] = $r;
        }
        fclose($h);
        return $rows;
    }

    private static function xlsx(string $path): array
    {
        $z = new \ZipArchive();
        if ($z->open($path) !== true) {
            return [];
        }
        $shared = [];
        if (($x = $z->getFromName('xl/sharedStrings.xml')) !== false) {
            foreach ((@simplexml_load_string($x)->si ?? []) as $si) {
                $t = '';
                if (isset($si->t)) {
                    $t = (string) $si->t;
                } else {
                    foreach ($si->r as $run) {
                        $t .= (string) $run->t;
                    }
                }
                $shared[] = $t;
            }
        }
        $sheet = $z->getFromName('xl/worksheets/sheet1.xml');
        if ($sheet === false) {
            for ($i = 0; $i < $z->numFiles; $i++) {
                if (preg_match('#^xl/worksheets/[^/]+\.xml$#', $z->getNameIndex($i))) {
                    $sheet = $z->getFromIndex($i);
                    break;
                }
            }
        }
        $z->close();
        $xml = $sheet ? @simplexml_load_string($sheet) : null;
        if (! $xml) {
            return [];
        }
        $rows = [];
        foreach ($xml->sheetData->row ?? [] as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                preg_match('/^([A-Z]+)/', (string) $c['r'], $m);
                $idx = 0;
                foreach (str_split($m[1] ?? 'A') as $ch) {
                    $idx = $idx * 26 + (ord($ch) - 64);
                }
                $t = (string) $c['t'];
                $v = match ($t) {
                    's' => $shared[(int) $c->v] ?? '',
                    'inlineStr' => (string) $c->is->t,
                    default => (string) $c->v,
                };
                $cells[$idx - 1] = $v;
            }
            $max = $cells ? max(array_keys($cells)) : -1;
            $line = [];
            for ($i = 0; $i <= $max; $i++) {
                $line[] = $cells[$i] ?? '';
            }
            $rows[] = $line;
        }
        return $rows;
    }
}
