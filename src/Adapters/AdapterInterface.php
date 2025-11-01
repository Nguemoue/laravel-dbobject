<?php

namespace Nguemoue\LaravelDbObject\Adapters;

interface AdapterInterface
{
    /**
     * Remplace les macros personnalisées dans une chaîne SQL selon le dialecte du SGBD.
     */
    public function processSql(string $sql): string;

    /**
     * Ajoute le quote approprié aux identifiants (nom d'objet, de colonne, etc.).
     */
    public function quoteIdentifier(string $name): string;
}
