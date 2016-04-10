<?php
/**
 * okvCache is php file caching.
 *
 * @license GPL v.3
 * @author vee w.
 * @link http://www.okvee.net 
 */


class okvcache {
	
	
	public $cache_path;
	
	
	function __construct() {
		// set cache path
		$this->cache_path = dirname(__FILE__).'/cache/';
		// check if folder is not exists.
		if ( !file_exists( $this->cache_path ) ) {
			mkdir( $this->cache_path );
		}
	}// __construct
	
	
	/**
	 * clear all cache
	 * @return boolean 
	 */
	function clear() {
		$scd = scandir( $this->cache_path );
		if ( is_array( $scd ) ) {
			foreach ( $scd as $file ) {
				if ( $file != '.' && $file != '..' && $file != 'index.html' && $file != '.htaccess' ) {
					unlink( $this->cache_path.$file );
				}
			}
			return true;
		}
		return false;
	}// clean
	
	
	/**
	 * delete a single cache
	 * @param string $id
	 * @return boolean 
	 */
	function delete( $id = '' ) {
		if ( file_exists( $this->cache_path.md5( $id ) ) ) {
			unlink( $this->cache_path.md5( $id ) );
		}
		return true;
	}// delete
	
	
	/**
	 * get cached
	 * @param string $id
	 * @return boolean 
	 */
	function get( $id = '' ) {
		// check file exists
		if ( !file_exists( $this->cache_path.md5( $id ) ) ) {
			return false;
		}
		// open cached
		$fp = fopen( $this->cache_path.md5( $id ), 'r' );
		if ( $fp === false ) {
			// no cached, something error like permission error
			return false;
		}
		$content = fread( $fp, filesize( $this->cache_path.md5( $id ) ) );
		fclose( $fp );
		// unserialize values
		$content = unserialize( $content );
		if ( !isset( $content['data'] ) && !isset( $content['cache_expire'] ) ) {
			return false;
		}
		// check expire cache?
		$file_date = filemtime( $this->cache_path.md5( $id ) );
		$now = time();
		if ( ( $now-$file_date ) > $content['cache_expire'] ) {
			// expired
			$this->delete( $id );
			return false;
		}
		//
		return $content['data'];
	}// get
	
	
	/**
	 * save cache
	 * @param string $id
	 * @param mixed $data
	 * @param integer $ttl
	 * @return boolean 
	 */
	function save( $id = '', $data = '', $ttl = 60 ) {
		$fp = fopen( $this->cache_path.md5( $id ), 'w+' );
		fwrite( $fp, serialize( array( 'data' => $data, 'cache_expire' => $ttl ) ) );
		fclose( $fp );
		return true;
	}// save
	
	
}


?>