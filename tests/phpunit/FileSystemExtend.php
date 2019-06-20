<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\SimpleCache\Tests;


/**
 * FileSystem extended.
 */
class FileSystemExtend extends \Rundiz\SimpleCache\Drivers\FileSystem
{


    public function createFolderIfNotExists(string $cachePath): bool
    {
        return parent::createFolderIfNotExists($cachePath);
    }// createFolderIfNotExists


    public function deleteCacheSubfolderRecursively(string $dir)
    {
        return parent::deleteCacheSubfolderRecursively($dir);
    }// deleteCacheSubfolderRecursively


    public function deleteCacheSubfoldersIfEmpty(string $filepath): bool
    {
        return parent::deleteCacheSubfoldersIfEmpty($filepath);
    }// deleteCacheSubfoldersIfEmpty


    public function keyToPathAndFileName(string $key): string
    {
        return parent::keyToPathAndFileName($key);
    }// keyToPathAndFileName


    public function sanitizeFileName(string $string, bool $forceLowerCase = false): string
    {
        return parent::sanitizeFileName($string, $forceLowerCase);
    }// sanitizeFileName


}
