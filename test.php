<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>test okvCache</title>
	</head>

	<body>
		
		
<?php
/**
 * ตัวอย่างการใช้งานในรูปแบบต่างๆ
 * 
 * @license GPL v.3
 * @author vee w.
 * @link http://www.okvee.net 
 * 
 * การใช้งาน
 * include ( 'okvcache.php' ); ทำการ include file okvcache.php เข้ามาก่อน
 * $cache = new okvcache(); กำหนด new object 
 * จากนั้นทำตามตัวอย่างในแบบต่างๆด้านล่าง โดยการกำหนดค่า save() จะมี parameter 3 ค่า คือ id(string), data(mixed), ttl(integer)
 * ซึ่ง ttl คือจำนวนวินาทีที่จะหมดอายุ ค่าเดิมคือ 60 วินาที สามารถกำหนดเพิ่มได้ตามต้องการ
 * ตัวอย่างการลบ cache อยู่ด้านล่างของหน้า.
 */


include( 'okvcache.php' );

// start new class
$cache = new okvcache();

/*
 * ทดสอบ string cache
 */
echo '<h3>string cache</h3>';
if ( false === $val = $cache->get( 'string-cache' ) ) {
	$data = 'this is string text.';
	$val = $data;
	$cache->save( 'string-cache', $data );
} else {
	echo '<strong>cached:</strong> ';
}
echo $val;


echo '<hr />';
######################################################


/*
 * ทดสอบ array cache
 */
echo '<h3>array cache</h3>';
if ( false === $val = $cache->get( 'array-cache' ) ) {
	$data = array( 'apple', 'banana', 'mango' );
	$val = $data;
	$cache->save( 'array-cache', $data );
} else {
	echo '<strong>cached:</strong> ';
}
print_r( $val );


echo '<hr />';
######################################################


/*
 * ทดสอบ object cache
 */
echo '<h3>object cache</h3>';
if ( false === $val = $cache->get( 'obj-cache' ) ) {
	$data = new stdClass();
	$data->fruit = array( 'apple', 'banana', 'mango' );
	$data->text = 'this is string text.';
	$val = $data;
	$cache->save( 'obj-cache', $data );
} else {
	echo '<strong>cached:</strong> ';
}
print_r( $val );


echo '<hr />';
######################################################


/*
 * ทดสอบ mixed cache
 */
echo '<h3>mixed cache</h3>';
if ( false === $val = $cache->get( 'mixed-cache' ) ) {
	$data1 = array( 'apple', 'banana', 'mango' );
	$data2 = new stdClass();
	$data2->cars = array( 'mercedes', 'chevrolet', 'toyota' );
	$data2->text = 'this is string text.';
	$data2->boolean = false;
	$data = array_merge( $data1, array( $data2 ) );
	$val = $data;
	$cache->save( 'mixed-cache', $data );
} else {
	echo '<strong>cached:</strong> ';
}
print_r( $val );


echo '<hr />';
######################################################


/*
 * ทดสอบ boolean cache
 */
echo '<h3>boolean cache</h3>';
if ( false === $val = $cache->get( 'bool-cache' ) ) {
	$data = true;
	$val = $data;
	$cache->save( 'bool-cache', $data );
} else {
	echo '<strong>cached:</strong> ';
}
var_dump( $val );


echo '<hr />';
######################################################


// end, clear variables (not required)
unset( $cache, $data, $data1, $data2, $val );

?>
		<h3>การลบแคช 1 แคช</h3>
		<p>ใช้ <code>$cache-&gt;delete( 'cache-id(string)' );</code></p>
		
		
		<h3>การล้างแคชทั้งหมด</h3>
		<p>ใช้ <code>$cache->clear();</code></p>


	</body>
</html>