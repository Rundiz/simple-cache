<?php
/**
 * PHP Simple Cache file system driver.
 * 
 * @package Simple Cache
 * @author Vee W.
 * @license http://opensource.org/licenses/MIT
 */


namespace Rundiz\SimpleCache\Drivers;

use Rundiz\SimpleCache\SimpleCacheInterface;

/**
 * File system driver class. For to create, read, update, delete the cache data in file system.
 *
 * @since 2.0
 */
class FileSystem implements SimpleCacheInterface
{


    /**
     * Full path to cache directory.
     * @var string Path to cache directory.
     */
    protected $cache_path;

    /**
     * umask
     * @var integer
     */
    protected $umask;


    /**
     * Class constructor
     * 
     * @param string $cache_path Path to cache folder. It will be automatically create if not exists or throw an error if it stuck at some where.
     * @param int $umask See more at http://php.net/manual/en/function.umask.php
     * @throws \Exception 
     */
    public function __construct($cache_path = '', $umask = 0002)
    {
        if (!is_int($umask)) {
            $umask = 0002;
        }
        $this->umask = $umask;

        if ($cache_path == null) {
            $cache_path = dirname(dirname(dirname(__DIR__))).'/cache';
        } else {
            $cache_path = rtrim($cache_path, '/');
        }
        $this->cache_path = $cache_path;

        // Create cache folder if not exists.
        if (!$this->createFolderIfNotExists($this->cache_path)) {
            throw new \Exception(sprintf('The cache directory "%s" does not exists and could not be created.', $this->cache_path));
        }

        // Check folder is writable. (so it can be write & delete.)
        if (!is_writable($this->cache_path)) {
            throw new \Exception(sprintf('The directory "%s" is not writable.', $this->cache_path));
        }

        $this->cache_path = realpath($this->cache_path);
    }// __construct


    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $result = $this->deleteCacheSubfolderRecursively($this->cache_path);
        if ($result === false) {
            return false;
        }
        return true;
    }// clear


    /**
     * Create folder or path if it is not exists.
     * 
     * @param string $cache_path Path to check and create.
     * @return boolean Return true if exists or created, return false for otherwise.
     */
    private function createFolderIfNotExists($cache_path)
    {
        if (!is_dir($cache_path)) {
            if (false === @mkdir($cache_path, 0777 & (~$this->umask), true) && !is_dir($cache_path)) {
                return false;
            }
        }
        return true;
    }// createFolderIfNotExists


    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $filename = $this->cache_path.DIRECTORY_SEPARATOR.$this->idToPathAndFileName($id);

        if (!is_file($filename) || (is_file($filename) && !is_writable($filename))) {
            // Cache file is not exists, or exists but unable to delete.
            unset($filename);
            return false;
        }

        $delete_cache_file_result = unlink($filename);
        if ($delete_cache_file_result === false) {
            unset($delete_cache_file_result, $filename);
            return false;
        }
        unset($delete_cache_file_result);

        $filepath = pathinfo($filename, PATHINFO_DIRNAME);
        if (is_dir($filepath)) {
            $this->deleteCacheSubfoldersIfEmpty($filepath);
        }
        unset($filepath);

        return true;
    }// delete


    /**
     * Delete cache sub folder recursively if empty.
     * 
     * @param string $filepath Path to cache sub folder.
     * @return boolean Return true on success, false if there is something error.
     */
    private function deleteCacheSubfoldersIfEmpty($filepath)
    {
        if ($this->cache_path == $filepath) {
            // Do not delete main cache folder itself.
            return true;
        }

        $filepath_exp = explode(DIRECTORY_SEPARATOR, $filepath);
        if (is_array($filepath_exp)) {
            for ($i = count($filepath_exp)-1; $i >= 0 ; $i--) {
                $dir = implode(DIRECTORY_SEPARATOR, $filepath_exp);
                $iterator = new \FilesystemIterator($dir);
                $isDirEmpty = !$iterator->valid();
                unset($iterator);

                if ($this->cache_path == $dir) {
                    // Do not delete main cache folder.
                    return true;
                } elseif (is_dir($dir) && !is_writable($dir)) {
                    // Directory is unable to delete.
                    return false;
                } elseif (is_dir($dir) && $isDirEmpty !== true) {
                    // Directory is not empty.
                    return true;
                } elseif (is_dir($dir) && $isDirEmpty === true && is_writable($dir) && $this->cache_path != $dir) {
                    rmdir($dir);
                    unset($filepath_exp[$i]);
                } else {
                    return true;
                }
            }
            unset($i);
        }
        unset($filepath_exp);

        return true;
    }// deleteCacheSubfoldersIfEmpty


    /**
     * Delete cache files and all sub folders recursively.
     * 
     * @param string $dir Path to main cache folder.
     * @return boolean Return false on something error.
     */
    private function deleteCacheSubfolderRecursively($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($dir . DIRECTORY_SEPARATOR . $object == $this->cache_path) {
                    return false;
                } elseif ($object != '.' && $object != '..') {
                    if (is_writable($dir . DIRECTORY_SEPARATOR . $object)) {
                        if (is_dir($dir . DIRECTORY_SEPARATOR . $object)) {
                            $this->deleteCacheSubfolderRecursively($dir . DIRECTORY_SEPARATOR . $object);
                        } else {
                            unlink($dir . DIRECTORY_SEPARATOR . $object);
                        }
                    } else {
                        return false;
                    }
                }
            }

            if ($dir !== $this->cache_path) {
                rmdir($dir);
            }
        }
    }// deleteCacheSubfolderRecursively


    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        $filename = $this->cache_path.DIRECTORY_SEPARATOR.$this->idToPathAndFileName($id);

        if (!is_file($filename)) {
            return false;
        }

        $fp = fopen($filename, 'r');
        if ($fp === false) {
            // There is no cache or file system error.
            unset($filename, $fp);
            return false;
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
            if (isset($match_data[1])) {
                $data = unserialize($match_data[1]);
            }
            unset($match_data, $match_data_type, $match_lifetime);
        }
        unset($content_exp);

        if ($lifetime < time()) {
            unset($data, $data_type, $lifetime);
            return false;
        }

        return $data;
    }// get


    /**
     * Convert id to path and file name.
     * For example: If id is "accounts.model.get_user_1" then it will be convert to accounts/model/get_user_1/md5(id)
     * 
     * @param string $id The cache id
     * @return string Return the cache subfolders (if dot in the cache id exists) with file cache file name.
     */
    private function idToPathAndFileName($id)
    {
        if (strpos($id, '.') !== false) {
            // Found . in cache id, convert to folders.
            $id_exp = explode('.', $id);
            $path_array = array();
            if (is_array($id_exp)) {
                foreach ($id_exp as $id_path) {
                    $path_array[] = mb_substr($this->sanitizeFileName($id_path), 0, 255);
                }
                unset($id_path);
            }
            $id_to_path = implode(DIRECTORY_SEPARATOR, $path_array).DIRECTORY_SEPARATOR;
            $id_to_path .= mb_substr(md5($id), 0, 255);
            unset($id_exp, $path_array);
        } else {
            $id_to_path = mb_substr(md5($id), 0, 255);
        }

        return $id_to_path.'.php';
    }// idToPathAndFileName
    
    
    /**
     * Returns a sanitized string, typically for URLs.
     *
     * @link https://github.com/vito/chyrp/blob/35c646dda657300b345a233ab10eaca7ccd4ec10/includes/helpers.php#L515 Copy from here.
     * @param string $string The string to sanitize.
     * @param boolean $force_lowercase Set to true to force the string to lowercase
     * @return string Sanitized string.
     */
    private function sanitizeFileName($string, $force_lowercase = false)
    {
        $strip = array(
            "~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?"
        );
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = preg_replace("/[^a-zA-Z0-9\-_]/", "", $clean);

        if ($force_lowercase === true) {
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
    public function save($id, $data, $lifetime = 60)
    {
        if (!is_int($lifetime) || is_int($lifetime) && $lifetime <= 0) {
            $lifetime = 60;
        }

        $filepath = pathinfo($this->cache_path.DIRECTORY_SEPARATOR.$this->idToPathAndFileName($id), PATHINFO_DIRNAME);

        if (!$this->createFolderIfNotExists($filepath)) {
            return false;
        }

        if (!is_writable($filepath)) {
            return false;
        }

        $tmpFile = tempnam($filepath, 'tmp');
        @chmod($tmpFile, 0666 & (~$this->umask));

        // generate cache content
        $cache_content = '<?php'."\n";
        $cache_content .= '/**'."\n\n\n";
        $cache_content .= 'expire: '.(time()+$lifetime)."\n";
        $cache_content .= 'data_type: '.gettype($data)."\n";
        $cache_content .= 'data: '.serialize($data)."\n".':enddata'."\n";
        $cache_content .= "\n\n\n".'*/';

        if (file_put_contents($tmpFile, $cache_content) !== false) {
            if (@rename($tmpFile, $this->cache_path.DIRECTORY_SEPARATOR.$this->idToPathAndFileName($id))) {
                $output = true;
            } else {
                $output = false;
            }
            @unlink($tmpFile);
        } else {
            $output = false;
        }

        unset($cache_content, $filepath, $tmpFile);
        return $output;
    }// save


}
