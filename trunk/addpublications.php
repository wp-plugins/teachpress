<?php  
/* New publications /edit publications
 * from publications.php (GET):
 * @param $pub_ID (INT) - Bestimmt die Publikation die geladen wird
 * @param $search (String) - Dient zum Ruecksprung zum vorherigen Suchergebnis
*/
function teachpress_addpublications_page() {
global $teachpress_pub; 
global $teachpress_beziehung; 
global $teachpress_tags;
global $teachpress_user;
global $pagenow;
global $current_user;
// WordPress current unser info
get_currentuserinfo();
$user = $current_user->ID;
// form variables from editpub.php
$typ = htmlentities(utf8_decode($_GET[typ]));
$name = htmlentities(utf8_decode($_GET[name]));
$autor = htmlentities(utf8_decode($_GET[autor]));
$erschienen = htmlentities(utf8_decode($_GET[erschienen]));
$jahr = htmlentities(utf8_decode($_GET[jahr]));
$isbn = htmlentities(utf8_decode($_GET[isbn]));
$links = htmlentities(utf8_decode($_GET[links]));
$sort = htmlentities(utf8_decode($_GET[sortierung]));
$comment = htmlentities(utf8_decode($_GET[comment]));
$tags = htmlentities(utf8_decode($_GET[tags]));
$image_url = htmlentities(utf8_decode($_GET[image_url]));
$rel_page = htmlentities(utf8_decode($_GET[rel_page]));
$is_isbn = htmlentities(utf8_decode($_GET[isisbn]));
$delbox = $_GET[delbox];
$erstellen = $_GET[erstellen];
$bookmark = $_GET[bookmark];
$speichern = $_GET[speichern];
// from publications.php or editpub.php
$pub_ID = htmlentities(utf8_decode($_GET[pub_ID]));
$search = htmlentities(utf8_decode($_GET[search]));
?>
<div class="wrap">
<?php
// if publications was created
if (isset($erstellen)) {
	add_pub($name, $typ, $autor, $erschienen, $jahr, $isbn, $links, $sort, $tags, $bookmark, $user, $comment, $image_url, $rel_page, $is_isbn);
	$message = __('Publication added','teachpress');
	$site = 'admin.php?page=teachpress/addpublications.php';
	tp_get_message($message, $site);
}
// if publication was saved
if (isset($speichern)) {
	change_pub($name, $typ, $autor, $erschienen, $jahr, $isbn, $links, $sort, $tags, $comment, $image_url, $rel_page, $is_isbn, $pub_ID, $delbox);
	$message = __('Publication changed','teachpress');
	$site = 'admin.php?page=teachpress/addpublications.php&amp;pub_ID=' . $pub_ID . '&amp;search=' . $search . '';
	tp_get_message($message, $site);
}
if ($pub_ID != '') {
	echo '<p><a href="admin.php?page=publications.php&amp;search=' . $search . '" class="teachpress_back" title="' . __('Show all publications','teachpress') . '">&larr; ' . __('All publications','teachpress') . '</a> <a href="admin.php?page=teachpress/publications.php&amp;search=' . $search . '" class="teachpress_back" title="' . __('Show own publications','teachpress') . '">&larr; ' . __('Your publications','teachpress') . '</a></p>';
}
?>
<h2><?php if ($pub_ID == '') {_e('Add publications','teachpress'); } else { _e('Edit publications','teachpress'); } ?><span class="tp_break">|</span> <small><a onclick="teachpress_showhide('hilfe_anzeigen')" style="cursor:pointer;"><?php _e('Help','teachpress'); ?></a></small></h2>
<div id="hilfe_anzeigen">
    <h3 class="teachpress_help"><?php _e('Help','teachpress'); ?></h3>
    <p class="hilfe_headline"><?php _e('Bookmarks','teachpress'); ?></p>
    <p class="hilfe_text"><?php _e('Add the publication to different publication lists.','teachpress'); ?></p>
    <p class="hilfe_headline"><?php _e('Image &amp; Related page','teachpress'); ?></p>
    <p class="hilfe_text"><?php _e('Both fields are for the teachPress Books widget. With the related page you can link a publication with a normal post/page.','teachpress'); ?></p>
    <p class="hilfe_close"><strong><a onclick="teachpress_showhide('hilfe_anzeigen')" style="cursor:pointer;"><?php _e('close','teachpress'); ?></a></strong></p>
</div>
<form name="form1" method="get" action="<?php echo $PHP_SELF ?>" id="form1">
  <input name="page" type="hidden" value="teachpress/addpublications.php">
  <?php if ($pub_ID != '') { 
  $row = "SELECT * FROM " . $teachpress_pub . " WHERE pub_id = '$pub_ID'";
  $row = tp_results($row);
  foreach ($row as $row) {
  	$daten[0] = $row->pub_ID;
	$daten[1] = $row->name;
	$daten[2] = $row->typ;
	$daten[3] = $row->autor;
	$daten[4] = $row->verlag;
	$daten[5] = $row->jahr;
	$daten[6] = $row->isbn;
	$daten[7] = $row->url;
	$daten[8] = $row->sort;
	$daten[9] = $row->comment;
	$daten[10] = $row->image_url;
	$daten[11] = $row->rel_page;
	$daten[12] = $row->is_isbn;
  }
  ?>
  <input name="pub_ID" type="hidden" value="<?php echo $pub_ID; ?>">
  <input name="search" type="hidden" value="<?php echo $search; ?>">
  <?php } ?>
  <div style="min-width:780px; width:100%; max-width:1100px;">
  <div style="width:30%; float:right; padding-right:2%; padding-left:1%;">
  <?php if ($pub_ID == '') { ?>
  <table class="widefat">
  	<thead>
  	<tr>
   		<th><strong><?php _e('Bookmarks','teachpress'); ?></strong></th>
    </tr>
    <tr>
      <td>
      <input type="checkbox" name="bookmark[]" id="bookmark" value="<?php echo $user; ?>" title="<?php _e('click to add the publication in your own list','teachpress'); ?>"/> <label for="bookmark" title="<?php _e('click to add the publication in your own list','teachpress'); ?>"><?php _e('add to your own list','teachpress'); ?></label>
      <p>
      <?php
	   // Abfrage der User mit Bookmark auf mindestens 1 Publikation
	   $abfrage = "SELECT DISTINCT user FROM " . $teachpress_user . "";
	   $row = tp_results($abfrage);
	   foreach($row as $row) {
		  if ($user != $row->user) { 
			  $user_info = get_userdata($row->user);
			  echo '<input type="checkbox" name="bookmark[]" id="bookmark_' . $user_info->ID . '" value="' . $user_info->ID . '" title="' . __('Bookmark for','teachpress') . ' ' . $user_info->display_name . '"/> <label for="bookmark_' . $user_info->ID . '" title="' . __('Bookmark for','teachpress') . ' ' . $user_info->display_name . '">' . $user_info->display_name . '</label> | '; 
		  } 
	   }
	   ?>
      </p>
      </td>
    </tr>
    </thead>
  </table>
  <p style="font-size:2px; margin:0px;">&nbsp;</p>
  <?php } ?>
  <table class="widefat">
  	<thead>
    <tr>
    	<th><?php _e('Image &amp; Related page','teachpress'); ?></th>
    </tr>
  	<tr>
        <td>
        <?php if ($image_url != '') {
			echo '<p><img name="tp_pub_image" src="' . $row->image_url . '" alt="' . $row->name . '" title="' . $row->name . '"/></p>';
        } ?>
        <p><strong><?php _e('Image URL','teachpress'); ?></strong></p>
        <input name="image_url" id="image_url" type="text" style="width:90%;"/>
         <a id="add_image" class="thickbox" href="media-upload.php?post_id=0&type=image&TB_iframe=true&width=640&height=440" title="<?php _e('Add Image','teachpress'); ?>" onclick="return false;"><img src="images/media-button-image.gif" alt="<?php _e('Add Image','teachpress'); ?>" /></a>
        <p><strong><?php _e('Related page','teachpress'); ?></strong></p>
        <div style="overflow:hidden;">
        <select name="rel_page" id="rel_page" style="width:90%;">
        <?php teachpress_wp_pages("menu_order","ASC",$daten[11],0,0); ?>
        </select>
        </div>
        </td>
    </tr>
    </thead>
  </table>
  </div>
    <div style="width:67%; float:left;">
    <table class="widefat">
    <thead>
    <tr>
    	<th><?php _e('Publication','teachpress'); ?></th>
    </tr>
    <tr>
      <td>
      <p><label for="typ"><strong><?php _e('Type','teachpress'); ?></strong></label></p>
      <select name="typ" id="typ">
      	 <?php echo get_tp_publication_type_options ($daten[2], $mode = 'list'); ?>
      </select>
      <p><label for="name"><strong><?php _e('Name','teachpress'); ?></strong></label></p>
      <textarea name="name" wrap="virtual" id="name" style="width:95%"><?php echo $daten[1]; ?></textarea>
      <p><label for="autor"><strong><?php _e('Author(s)','teachpress'); ?></strong></label></p>
      <textarea name="autor" wrap="virtual" id="autor" style="width:95%"><?php echo $daten[3]; ?></textarea>
      <p><label for="erschienen"><strong><?php _e('Published by','teachpress'); ?></strong></label></p>
      <textarea name="erschienen" rows="3" wrap="virtual" id="erschienen" style="width:95%"><?php echo $daten[4]; ?></textarea>
      <p><label for="jahr"><strong><?php _e('Year','teachpress'); ?></strong></label></p>
      <input type="text" name="jahr" id="jahr" value="<?php echo $daten[5]; ?>">
      <p><label for="isbn"><strong><?php _e('ISBN/ISSN','teachpress'); ?></strong></label></p>
      <input type="text" name="isbn" id="isbn" value="<?php echo $daten[6]; ?>">
        <span style="padding-left:7px;">
          <label><input name="isisbn" type="radio" id="isisbn_0" value="1" <?php if ($daten[12] == '1' || $pub_ID == '') { echo 'checked="checked"'; }?>/><?php _e('ISBN','teachpress'); ?></label>
          <label><input name="isisbn" type="radio" value="0" id="isisbn_1" <?php if ($daten[12] == '0') { echo 'checked="checked"'; }?>/><?php _e('ISSN','teachpress'); ?></label>
        </span>
      <p><label for="links"><strong><?php _e('Link','teachpress'); ?></strong></label></p>
      <input name="links" type="text" id="links" style="width:95%" value="<?php echo $daten[7]; ?>">
      <p><label for="sortierung"><strong><?php _e('Sorting date','teachpress'); ?></strong></label></p>
      <input type="text" name="sortierung" id="sortierung" value="<?php if ($pub_ID != '') { echo $daten[8]; } else {_e('JJJJ-MM-TT','teachpress'); } ?>" onblur="if(this.value=='') this.value='<?php _e('JJJJ-MM-TT','teachpress'); ?>';" onfocus="if(this.value=='<?php _e('JJJJ-MM-TT','teachpress'); ?>') this.value='';"/>
      <input type="submit" name="calendar" id="calendar" value="..." class="teachpress_button"/>
      <p><label for="comment"><strong><?php _e('Comment','teachpress'); ?></strong></label></p>
      <textarea name="comment" wrap="virtual" id="comment" style="width:95%"><?php echo $daten[9]; ?></textarea></td>
    </tr>
    </thead>
    </table>
    <p style="font-size:2px; margin:0px;">&nbsp;</p>
    <table class="widefat">
    <thead>
    <tr>
    	<th><?php _e('Tags','teachpress'); ?></th>
    </tr>
    <tr>
      <td>
      <?php if ($pub_ID != '') {
      echo '<p><strong>' . __('Current','teachpress') . '</strong></p>';
	  $sql = "SELECT " . $teachpress_tags . ".name, " . $teachpress_beziehung . ".belegungs_id 
			FROM " . $teachpress_beziehung . " 
			INNER JOIN " . $teachpress_tags . " ON " . $teachpress_tags . ".tag_id=" . $teachpress_beziehung . ".tag_id
			INNER JOIN " . $teachpress_pub . " ON " . $teachpress_pub . ".pub_id=" . $teachpress_beziehung . ".pub_id
			WHERE " . $teachpress_pub . ".pub_id = '$pub_ID'
			ORDER BY " . $teachpress_tags . ".name";	
	  $sql = tp_results($sql);
	  foreach ($sql as $row3){
	  	echo'<input name="delbox[]" type="checkbox" value="' . $row3->belegungs_id . '" title="Tag &laquo;' . $row3->name . '&raquo; ' . __('delete','teachpress') . '" id="checkbox_' . $row3->belegungs_id . '"/> <span style="font-size:12px;" ><label for="checkbox_' . $row3->belegungs_id . '" title="Tag &laquo;' . $row3->name . '&raquo; ' . __('delete','teachpress') . '">' . $row3->name . '</label></span> | ';
	  } 
	  }?>  
      <p><label for="tags"><strong><?php _e('New (seperate by comma)','teachpress'); ?></strong></label></p>
      <input name="tags" type="text" id="tags" style="width:95%">
      <div class="teachpress_cloud" style="padding-top:15px;">
         <?php
	   // Anzahl darzustellender Tags
	   	$limit = 50;
		// Schriftgroessen
		$maxsize = 35;
		$minsize = 11;
	   	// Ermittle Anzahl der Tags absteigend sortiert
		$sql = "SELECT anzahlTags FROM ( SELECT COUNT(*) AS anzahlTags FROM " . $teachpress_beziehung . " GROUP BY " . $teachpress_beziehung . ".`tag_id` ORDER BY anzahlTags DESC ) as temp1 GROUP BY anzahlTags ORDER BY anzahlTags DESC";
		// Ermittle einzelnes Vorkommen der Tags, sowie Min und Max
		$sql = "SELECT MAX(anzahlTags) AS max, min(anzahlTags) AS min, COUNT(anzahlTags) as gesamt FROM (".$sql.") AS temp";
		$tagcloud_temp = mysql_fetch_array(mysql_query($sql));
		$max = $tagcloud_temp['max'];
		$min = $tagcloud_temp['min'];
		$insgesamt = $tagcloud_temp['gesamt'];
		// Tags und Anzahl zusammenstellen
		$sql = "SELECT tagPeak, name, tag_id FROM ( SELECT COUNT(b.tag_id) as tagPeak, t.name AS name,  t.tag_id as tag_id FROM " . $teachpress_beziehung . " b LEFT JOIN " . $teachpress_tags . " t ON b.tag_id = t.tag_id GROUP BY b.tag_id ORDER BY tagPeak DESC LIMIT " . $limit . " ) AS temp WHERE tagPeak>=".$min." ORDER BY name";
		$temp = mysql_query($sql);
		// Endausgabe der Cloud zusammenstellen
		while ($tagcloud = mysql_fetch_array($temp)) {
			// Schriftgröße berechnen
			// Minimum ausgleichen
			if ($min == 1) {
				$min = 0;
			}
			// Formel: max. Schriftgroesse*(aktuelle anzahl - kleinste Anzahl)/ (groeßte Anzahl - kleinste Anzahl)
			$size = floor(($maxsize*($tagcloud['tagPeak']-$min)/($max-$min)));
			// Ausgleich der Schriftgröße
			if ($size < $minsize) {
				$size = $minsize ;
			}
			?>
			<span style="font-size:<?php echo $size; ?>px;"><a href="javascript:teachpress_inserttag('<?php echo $tagcloud['name']; ?>')" title="&laquo;<?php echo $tagcloud['name']; ?>&raquo; <?php _e('add as tag','teachpress'); ?>"><?php echo $tagcloud['name']; ?> </a></span> 
            <?php 
		  }
		  ?>
           </div>       
      </td>
    </tr>
    </thead>
  </table>
  <p>
  	<?php if ($pub_ID == '') {?>
    <input name="erstellen" type="submit" class="teachpress_button" id="publikation_erstellen" onclick="teachpress_validateForm('tags','','R','name','','R','autor','','R');return document.teachpress_returnValue" value="<?php _e('create','teachpress'); ?>">
    <input type="reset" name="Reset" value="<?php _e('reset','teachpress'); ?>" id="teachpress_reset" class="teachpress_button">
    <?php } else { ?>
     <input type="submit" name="speichern" id="publikation_erstellen" value="<?php _e('save','teachpress'); ?>" class="teachpress_button">
    <?php } ?>
  </p>
  </div>
</form>
<script type="text/javascript">
  Calendar.setup(
    {
      inputField  : "sortierung", // ID of the input field
      ifFormat    : "%Y-%m-%d",   // the date format
      button      : "calendar"    // ID of the button
    }
  );
</script>
</div>
<?php } ?>