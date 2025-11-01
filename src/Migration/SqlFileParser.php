<?php

namespace Nguemoue\LaravelDbObject\Migration;

use Symfony\Component\Yaml\Yaml;

class SqlFileParser
{
    /**
     * Parse un fichier SQL d'objet et renvoie un tableau contenant:
     * - 'type': object_type (string)
     * - 'group': group (string)
     * - 'depends': depends_on (array de noms)
     * - 'tags': tags (array)
     * - 'description': description (string)
     * - 'name': nom de l'objet (d'après le nom de fichier, ou meta)
     * - 'up_sql': SQL de création (string)
     * - 'down_sql': SQL de suppression (string)
     */
    public static function parseFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Impossible de lire le fichier: $filePath");
        }

        // Extraire front-matter YAML (entre les premiers ---)
        $yamlPart = '';
        $upSql = '';
        $downSql = '';
        $lines = preg_split("/(\r?\n)/", $content);
        $i = 0;
        if (isset($lines[$i]) && trim($lines[$i]) === '---') {
            // Parcourir jusqu'à la fermeture '---'
            $i++;
            while ($i < count($lines) && trim($lines[$i]) !== '---') {
                $yamlPart .= $lines[$i] . "\n";
                $i++;
            }
            // Passer la ligne de fermeture '---'
            if ($i < count($lines) && trim($lines[$i]) === '---') {
                $i++;
            }
        }

        // Chercher la section -- up:
        $mode = null;
        for ($iMax = count($lines); $i < $iMax; $i++) {
            $line = $lines[$i];
            if (preg_match('/^--\s*up:/i', $line)) {
                $mode = 'up';
                continue;
            }
            if (preg_match('/^--\s*down:/i', $line)) {
                $mode = 'down';
                continue;
            }
            if ($mode === 'up') {
                $upSql .= $line . "\n";
            } elseif ($mode === 'down') {
                $downSql .= $line . "\n";
            }
        }

        // Enlever d'éventuels commentaires ou espaces en trop en fin de requêtes
        $upSql = trim($upSql);
        $downSql = trim($downSql);

        // Parser le YAML
        $meta = [];
        if (!empty(trim($yamlPart))) {
            $meta = Yaml::parse($yamlPart);
            if (!is_array($meta)) {
                $meta = [];
            }
        }

        // Déterminer le nom de l'objet:
        // Par convention le nom du fichier (sans extension) est le nom de l'objet
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $name = $filename;
        // (optionnel) si le YAML contient un champ "name", on pourrait l'utiliser à la place
        if (!empty($meta['name'])) {
            $name = $meta['name'];
        }

        // Object_type, group, etc depuis meta
        $type = $meta['object_type'] ?? '';
        $group = $meta['group'] ?? '';
        $depends = $meta['depends_on'] ?? [];
        $tags = $meta['tags'] ?? [];
        $description = $meta['description'] ?? '';

        return [
            'name' => $name,
            'type' => $type,
            'group' => $group,
            'depends' => $depends,
            'tags' => $tags,
            'description' => $description,
            'up_sql' => $upSql,
            'down_sql' => $downSql,
        ];
    }
}
