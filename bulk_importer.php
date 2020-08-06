<?php
//custom wordpress importer script
//The script objective is to be able to import a database from a custom CMS to wordpress.
//This importer is focused to import text only. All the pictures will be called via custom functions on the wordpress site.

//Config
//Database we are importing data
$server = '';
$username = '';
$password = '';
$database = '';

//WP database we are exporting posts.
$server2 = '';
$username2 = '';
$password2 = '';
$database2 = '';

//Behaivor.
$amount = '50000'; //Amount of posts to be imported per iteration. You can configure the amount of posts depending on what your server can handle.
$from = '0'; //Starting Amount. 

//1st step - Create a loop with the posts from the original database -ODB From now on- These values will be stored in an array which are we going to use to format and store properly on WP

//This query gets all the content posts from the OGDB
$query = "SELECT * from your_post_table order by ID asc limit $from, $amount";

$connect = mysqli_connect($server, $username, $password, $database);
if(!$connect) {
echo "Can't make connection, Error No. ".mysqli_connect_errno()."";
}
else {
echo "Datbase connection, Success!";
}

$result = $connect->query($query);
while($row=$result->fetch_assoc())
{
//Fixes!
//The database has some coding issues as its a very old database that has been ported over and over during 14 years.
//These fixes will be done here.

//replace array has all the characters that must be corrected on the text.
$replace = array("Ã¡" => "á", "Ã©" => "é" , "Ã" => "í", "Ã³" => "ó", "Ã±" => "ñ", "Â" => "", "â€" => "");


$ID = $row['art_ID'];
$title = strtr($row['art_Titulo'], $replace);
$text = strtr($row['art_Contenido'], $replace);
$date = date("Y-m-d H:i:s", $row['art_Fecha']); //Date translated from integer to date
$art_Foto = $row['art_Foto'];
$art_Foto2 = $row['art_Foto2'];
$art_Foto3 = $row['art_Foto3'];
$art_Foto4 = $row['art_Foto4'];
$art_Foto5 = $row['art_Foto5'];
$art_Coment = $row['art_Coment'];
$cat_ID2 = $row['cat_ID2'];
$cat_ID = $row['cat_ID'];
$author = $row['NOT_REPORTERO'];

echo "<br>cat_ID2 ="; 
echo $cat_ID2;
//category Swap - This will switch the old category for the new one
$original_categories = array('116', '115', '114', '113', '112', '111', '110', '107', '104', '103', '102', '98', '95', '94', '86', '85', '78', '76', '74', '69', '68', '67', '66', '65', '62', '60', '58', '57', '56', '55', '53', '52', '51', '50', '49', '48');
//new categories
$new_categories = array('172', '171', '132', '170', '168', '167', '163', '165', '161', '142', '164', '134', '133', '98', '134', '154', '160', '139', '136', '157', '147', '150', '151', '146', '155', '169', '148', '145', '156', '130', '137', '143', '140', '144', '138', '131'); 
//echo "<br>Original:";
//echo $cat_ID;
//echo "<br> ID of the Category Array: ";
$migrate_category= array_search($cat_ID, $original_categories);
//echo $migrate_category;
//echo "<br> New category ID: ";
//echo $new_categories[$migrate_category];
$insert_category = "INSERT INTO wp_term_relationships (object_id, term_taxonomy_id) VALUES (".$ID.", ".$new_categories[$migrate_category].")";
//echo "<br>";
//echo $insert_category;
//echo "<br>";

//cat_ID2 categories - if the post has an cat_ID2 category, it will add an additional category.
if($cat_ID2 == 0)
{
 echo "No category 2 detected";
}
else
{
$original_categories2 = array('107', '76', '53', '52', '50', '49');
$new_categories2 = array('165', '139', '124', '182', '183', '121');
$migrate_category2 = array_search($cat_ID2, $original_categories2);
//echo $migrate_category2;
$insert_category2 = "INSERT INTO wp_term_relationships (object_id, term_taxonomy_id) VALUES (".$ID.", ".$new_categories2[$migrate_category2].")";
//echo "<br>";
//echo $insert_category2;
}	


//Users assign - This variable goes to the insert_post area to set author of the posts. Doing same procedure as with the categories.
$original_users = array("0", "8", "9", "10", "13", "16", "17", "18", "19", "25", "30", "31", "32", "33", "34", "35", "36", "40", "43", "44", "45", "46", "47", "49", "51", "52", "53", "57", "59", "60", "62", "75", "78", "79", "80", "81", "82", "83", "84", "85", "86");
$new_users = array("1", "3", "4","5",  "6", "7", "1", "9", "10", "1", "38", "11", "12", "1", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "26", "27", "28", "29", "30", "31", "32", "33", "34", "34", "35", "36", "37", "38");

$migrate_users = array_search($author, $original_users);

$fotos = array($row['art_Foto'], $row['art_Foto2'], $row['art_Foto3'], $row['art_Foto4'], $row['art_Foto5']);
//All set!, all the info we need is set on variables. Now we need to set up the query.
//Post query will insert post info in the wp_posts table
$insert_post = "INSERT INTO wp_posts (ID, post_author, post_date, post_date_gmt, post_content, post_title, post_status, comment_status, ping_status, post_name, post_modified, post_modified_gmt, guid, post_type, post_excerpt, to_ping, pinged, comment_count, post_content_filtered) 
VALUES 
($ID, $new_users[$migrate_users], '$date', '$date', '$text', '$title', 'publish', 'open', 'open', '$ID', '$date', '$date', 'https://enlineadirecta.info/$ID', 'post', '', '', '', 0, '')";

//category query - will insert category in the wp_term_relationships table
//if there's an cat_ID2 set it will add it to the corresponding section.


//Custom Fields: The Custom fields covers the photos (Art_Foto to art_Foto5) and the URL art_Coment
$insert_customf = "insert into wp_postmeta (post_id, meta_key, meta_value) values ('$ID', 'art_Foto', '$fotos[0]')";
$insert_customf2 = "insert into wp_postmeta (post_id, meta_key, meta_value) values ('$ID', 'art_Foto2', '$fotos[1]')";
$insert_customf3 = "insert into wp_postmeta (post_id, meta_key, meta_value) values ('$ID', 'art_Foto3', '$fotos[2]')";
$insert_customf4 = "insert into wp_postmeta (post_id, meta_key, meta_value) values ('$ID', 'art_Foto4', '$fotos[3]')";
$insert_customf5 = "insert into wp_postmeta (post_id, meta_key, meta_value) values ('$ID', 'art_Foto5', '$fotos[4]')";
$insert_customfurl = "insert into wp_postmeta (post_id, meta_key, meta_value) values ('$ID', 'art_Coment', '$art_Coment')";


$connect2 = mysqli_connect($server2, $username2, $password2, $database2);
if(!$connect2) {
echo "Can't make connection, Error No. ".mysqli_connect_errno()."";
}
else {
echo "Datbase connection, Success!";
}

//Insert post query
if(!$connect2->query($insert_post))
{
 echo("Error description: " . $connect2 -> error);
}
else
{
echo "Post ID $ID - $title posted";
}

//Insert category query
if(!$connect2->query($insert_category) and )
{
 echo("Error description: " . $connect2 -> error);
}
else
{
echo " / Category Posted";
}


//Insert category2 query
if(!$connect2->query($insert_category2))
{
 echo(" / Error description: " . $connect2 -> error);
 echo " / cat_ID2 Not set";
}
else
{
echo " / Category 2 (cat_ID2) Posted";
}

//Insert Photo 1 query
if(!$connect2->query($insert_customf))
{
 echo("Error description: " . $connect2 -> error);
}
else
{
echo " / Photo 1 Posted";
}

//Insert Photo 2 query
if(!$connect2->query($insert_customf2))
{
 echo("Error description: " . $connect2 -> error);
}
else
{
echo " / Photo 2 Posted";
}

//Insert Photo 3 query
if(!$connect2->query($insert_customf3))
{
 echo("Error description: " . $connect2 -> error);
}
else
{
echo " / Photo 3 Posted";
}

//Insert Photo 4 query
if(!$connect2->query($insert_customf4))
{
 echo("Error description: " . $connect2 -> error);
}
else
{
echo " / Photo 4";
}

//Insert Photo 5 query
if(!$connect2->query($insert_customf5))
{
 echo("Error description: " . $connect2 -> error);
}
else
{
echo " / Photo 5 posted";
}

//Insert Custom URL query
if(!$connect2->query($insert_customfurl))
{
 echo("Error description: " . $connect2 -> error);
}
else
{
echo " / Custom URL $id posted";
}
echo "<br><br>";
}




