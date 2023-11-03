<?php
/**
 * @license http://opensource.org/licenses/MIT MIT
 */


namespace Rundiz\SimpleCache\Drivers;


use Psr\SimpleCache\CacheInterface;


use Rundiz\SimpleCache\Exceptions\InvalidArgumentException;


/**
 * File system driver.
 * 
 * @since 3.0
 */
class FileSystem implements CacheInterface
{


    use MultipleTrait;


    /**
     * Full path to cache directory.
     * @var string Path to cache directory.
     */
    protected $cachePath;

    /**
     * umask
     * @var integer
     */
    protected $umask;


    /**
     * Class constructor
     * 
     * @param string $cachePath Path to cache folder. It will be automatically create if not exists or throw an error if it stuck at some where.
     * @param int $umask See more at http://php.net/manual/en/function.umask.php
     * @throws \Exception 
     */
    public function __construct(string $cachePath = '', int $umask = 0002)
    {
        if ($umask === null) {
            $umask = 0002;
        }
        $this->umask = $umask;

        if (empty($cachePath)) {
            $cachePath = dirname(dirname(__DIR__)).'/cache';
        } else {
            $cachePath = rtrim($cachePath, '/');
        }
        $this->cachePath = $cachePath;

        // Create cache folder if not exists.
        if (!$this->createFolderIfNotExists($this->cachePath)) {
            throw new \Exception(sprintf('The cache directory "%s" does not exists and could not be created.', $this->cachePath));
        }

        // Check folder is writable. (so it can be write & delete.)
        if (!is_writable($this->cachePath)) {
            throw new \Exception(sprintf('The directory "%s" is not writable.', $this->cachePath));
        }

        $this->cachePath = realpath($this->cachePath);
    }// __construct


    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        $this->deleteCacheSubfolderRecursively($this->cachePath);
        if (is_dir($this->cachePath)) {
            return true;
        }
        return false;
    }// clear


    /**
     * Create folder or path if it is not exists.
     * 
     * This method was called from `__construct()`, `set()`.
     * 
     * @param string $cachePath Path to check and create.
     * @return bool Return `true` if exists or created, return `false` for otherwise.
     */
    protected function createFolderIfNotExists(string $cachePath): bool
    {
        if (!is_dir($cachePath)) {
            if (false === @mkdir($cachePath, 0777 & (~$this->umask), true) && !is_dir($cachePath)) {
                return false;
            }
        }
        return true;
    }// createFolderIfNotExists


    /**
     * {@inheritDoc}
     */
    public function delete($key): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        $filename = $this->cachePath . DIRECTORY_SEPARATOR . $this->keyToPathAndFileName($key);

        if (!is_file($filename)) {
            // cache file is not exists.
            return true;
        } elseif (is_file($filename) && !is_writable($filename)) {
            // Cache file is exists but unable to delete.
            unset($filename);
            return false;
        }

        $deleteResult = unlink($filename);
        if ($deleteResult === false) {
            unset($deleteResult, $filename);
            return false;
        }
        unset($deleteResult);

        $filepath = pathinfo($filename, PATHINFO_DIRNAME);
        if (is_dir($filepath)) {
            $this->deleteCacheSubfoldersIfEmpty($filepath);
        }
        unset($filepath);

        return true;
    }// delete


    /**
     * Delete cache files and all sub folders recursively.
     * 
     * This method was called from `clear()`.
     * 
     * @param string $dir Path to main cache folder.
     */
    protected function deleteCacheSubfolderRecursively(string $dir)
    {
        $dir = str_replace(['../', '..\\', '...', '..'], '', $dir);
        $Iterator = new \DirectoryIterator($dir);

        foreach($Iterator as $FileInfo) {
            if($FileInfo->isFile()) {
                unlink($FileInfo->getRealPath());
            } else if(!$FileInfo->isDot() && $FileInfo->isDir()) {
                $this->deleteCacheSubfolderRecursively($FileInfo->getRealPath());
            }
        }

        if (realpath($dir) !== realpath($this->cachePath)) {
            rmdir($dir);
        }
    }// deleteCacheSubfolderRecursively


    /**
     * Delete cache sub folder (recursively using `\FilesystemIterator`) if empty.
     * 
     * This method was called from `delete()`.
     * 
     * @param string $filepath Path to cache sub folder.
     * @return bool Return `true` on success, `false` if there is something error.
     */
    protected function deleteCacheSubfoldersIfEmpty(string $filepath): bool
    {
        $filepath = str_replace(['../', '..\\', '...', '..'], '', $filepath);

        if ($this->cachePath == $filepath) {
            // Do not delete main cache folder itself.
            return true;
        }

        $filepathExp = explode(DIRECTORY_SEPARATOR, $filepath);
        if (is_array($filepathExp)) {
            for ($i = count($filepathExp)-1; $i >= 0 ; $i--) {
                $dir = implode(DIRECTORY_SEPARATOR, $filepathExp);
                $iterator = new \FilesystemIterator($dir);
                $isDirEmpty = !$iterator->valid();
                unset($iterator);

                if ($this->cachePath == $dir) {
                    // Do not delete main cache folder.
                    return true;
                } elseif (is_dir($dir) && !is_writable($dir)) {
                    // Directory is unable to delete.
                    return false;
                } elseif (is_dir($dir) && $isDirEmpty !== true) {
                    // Directory is not empty.
                    return false;
                } elseif (is_dir($dir) && $isDirEmpty === true && is_writable($dir) && $this->cachePath != $dir) {
                    rmdir($dir);
                    unset($filepathExp[$i]);
                } else {
                    return true;
                }
            }
            unset($i);
        }
        unset($filepathExp);

        return true;
    }// deleteCacheSubfoldersIfEmpty


    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        $filename = $this->cachePath . DIRECTORY_SEPARATOR . $this->keyToPathAndFileName($key);

        if (!is_file($filename)) {
            return $default;
        }

        $fp = fopen($filename, 'r');
        if ($fp === false) {
            // There is no cache or file system error.
            unset($filename, $fp);
            return $default;
        }

        $content = fread($fp, filesize($filename));
        fclose($fp);
        unset($filename, $fp);

        // prepare data
        $lifetime = 0;
        $data_type = null;
        $data = null;

        $content_exp = explode("\n", $content);
        preg_match('/data: (.+?):enddata/ius', $content, $match_data);
        unset($content);

        if (is_array($content_exp)) {
            foreach ($content_exp as $content_line) {
                if (strpos($content_line, 'expire: ') !== false) {
                    preg_match('/^expire: (.+?)$/iu', $content_line, $match_lifetime);
                } elseif (strpos($content_line, 'data_type: ') !== false) {
                    preg_match('/^data_type: (.+?)$/iu', $content_line, $match_data_type);
                }
            }// endforeach;
            unset($content_line);

            if (isset($match_lifetime[1])) {
                $lifetime = $match_lifetime[1];
            }
            if (isset($match_data_type[1])) {
                $data_type = $match_data_type[1];
            }
            if (isset($match_data[1]) && is_scalar($match_data[1])) {
                $data = unserialize(trim($match_data[1]));
            }
            unset($match_data, $match_data_type, $match_lifetime);
        }
        unset($content_exp);

        if ($lifetime < time()) {
            unset($data, $data_type, $lifetime);
            return $default;
        }
        unset($data_type, $lifetime);

        return $data;
    }// get


    /**
     * {@inheritDoc}
     */
    public function has($key): bool
    {
        return $this->get($key, $this) !== $this;
    }// has


    /**
     * Convert key to path and file name.
     * For example: If key is "accounts.model.get_user_1" then it will be convert to accounts/model/get_user_1/md5(key)
     * 
     * This method was called from `get()`, `set()`, `delete()`.
     * 
     * @param string $key The cache key
     * @return string Return the cache subfolders (if dot in the cache key exists) with file cache file name.
     */
    protected function keyToPathAndFileName(string $key): string
    {
        if (strpos($key, '.') !== false) {
            // Found . in cache key, convert to folders.
            $keyExp = explode('.', $key);
            $pathArray = [];
            if (is_array($keyExp)) {
                foreach ($keyExp as $keyPath) {
                    $pathArray[] = mb_substr($this->sanitizeFileName($keyPath), 0, 255);
                }
                unset($keyPath);
            }
            $keyToPath = implode(DIRECTORY_SEPARATOR, $pathArray) . DIRECTORY_SEPARATOR;
            $keyToPath .= mb_substr(md5($key), 0, 255);
            unset($keyExp, $pathArray);
        } else {
            $keyToPath = mb_substr(md5($key), 0, 255);
        }

        return $keyToPath . '.php';
    }// keyToPathAndFileName
    
    
    /**
     * Returns a sanitized string, typically for URLs.
     * 
     * This method was called from `keyToPathAndFileName()`.
     *
     * @link https://github.com/vito/chyrp/blob/35c646dda657300b345a233ab10eaca7ccd4ec10/includes/helpers.php#L515 Copy from here.
     * @param string $string The string to sanitize.
     * @param bool $forceLowerCase Set to true to force the string to lowercase
     * @return string Sanitized string.
     */
    protected function sanitizeFileName(string $string, bool $forceLowerCase = false): string
    {
        $strip = [
            "~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?"
        ];
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = preg_replace("/[^a-zA-Z0-9\-_]/", "", $clean);

        if ($forceLowerCase === true) {
            if (function_exists('mb_strtolower')) {
                $output = mb_strtolower($clean);
            } else {
                $output = strtolower($clean);
            }
        } else {
            $output = $clean;
        }

        unset($clean, $strip);
        return $output;
    }// sanitizeFileName


    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null): bool
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('$key must be a string');
        }

        if ($ttl === null || (!is_int($ttl) && !$ttl instanceof \DateInterval)) {
            $ttl = 60;
        }

        if (is_int($ttl)) {
            $expires = (time() + $ttl);
        } elseif ($ttl instanceof \DateInterval) {
            $expires = date_create_from_format("U", time())->add($ttl)->getTimestamp();
        } else {
            throw new InvalidArgumentException('The $ttl must be integer or \\DateInterval object.');
        }

        $filepath = pathinfo($this->cachePath . DIRECTORY_SEPARATOR . $this->keyToPathAndFileName($key), PATHINFO_DIRNAME);

        if (!$this->createFolderIfNotExists($filepath)) {
            return false;
        }

        if (!is_writable($filepath)) {
            return false;
        }

        $tmpFile = tempnam($filepath, 'tmp');
        @chmod($tmpFile, 0666 & (~$this->umask));

        // generate cache content
        $cache_content = '<?php' . "\n";
        $cache_content .= '/**' . "\n\n\n";
        $cache_content .= 'create: ' . time() . "\n";
        $cache_content .= 'create_readable: ' . date('Y-m-d H:i:s') . "\n";
        $cache_content .= 'ttl: ' . var_export($ttl, true) . "\n";
        $cache_content .= 'expire: ' . $expires . "\n";
        $cache_content .= 'expire_readable: ' . date('Y-m-d H:i:s', $expires) . "\n";
        $cache_content .= 'data_type: ' . gettype($value) . "\n";
        $cache_content .= 'data: ' . serialize($value) . "\n" . ':enddata' . "\n";
        $cache_content .= "\n\n\n" . '*/';
        unset($expires);

        if (file_put_contents($tmpFile, $cache_content) !== false) {
            if (@rename($tmpFile, $this->cachePath . DIRECTORY_SEPARATOR . $this->keyToPathAndFileName($key))) {
                $output = true;
            } else {
                $output = false;
            }

            if (is_file($tmpFile)) {
                @unlink($tmpFile);
            }
        } else {
            $output = false;
        }

        unset($cache_content, $filepath, $tmpFile);
        return $output;
    }// set


}
