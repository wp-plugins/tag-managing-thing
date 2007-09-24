<?php 
/*
Plugin Name: Tag Managing Thing
Plugin URI: http://www.neato.co.nz/wordpress-things/tag-managing-thing
Description: A thing for managing your tags.  Things like renaming and deletions.
Version: beta 2
Author: Christine From The Internet
Author URI: http://www.neato.co.nz
*/

function tmt_thing_admin() {
	$taxonomy = $_GET["selectedTaxonomy"];
	if (!is_taxonomy($taxonomy)) $taxonomy = 'post_tag';

	$siteurl = get_option('siteurl');

	echo '<div class="wrap">';

	if ($_GET["action"] == "savetagupdate") {
		$tagid =$_GET["edittag"];

		if (!is_numeric($tagid)) {
			echo "<div class=\"error\"><p>An invalid term ID was passed in.</p></div>";
			return;
		}
		
		if($_GET["updateaction"] == "Save") {
			$tag = $_GET["renametag"];
			$slug = $_GET["renameslug"];

			$args = array();
			$args["slug"]=$slug;
			$args["name"]=$tag;
			wp_update_term($tagid, $taxonomy,$args);
		}
		
		if ($_GET["updateaction"] == "Change Taxonomy") {
			$newtaxo = $_GET["newtaxonomy"];
			if (!is_taxonomy($newtaxo)) {
				echo "<div class='error'>$newtaxo is not a valid taxonomy</div>";
				return;
			}
			
			$term = get_term($tagid, $taxonomy);
			$taggedObjects = get_objects_in_term($tagid, $taxonomy);

			wp_delete_term($tagid, $taxonomy);
			
			$check = is_term($term->name, $newtaxo);
			if (is_null($check)) {
				$args =array();
				$args['slug'] = $term->slug;
				$newterm = wp_insert_term($term->name,$newtaxo, $args);
			} else {
				$newterm = get_term($check, $newtaxo);
			}
			
			if ($taggedObjects) {
				foreach ($taggedObjects as $taggedObjId) {
					wp_set_object_terms($taggedObjId,$newterm[term_id],$newtaxo,true);
				}
			}
		}

		if ($_GET["updateaction"] == "Split") {
			$taggedObjects = get_objects_in_term($tagid, $taxonomy);
			$tag = $_GET["split"];

			$tagset = explode(",", $tag);
			$tagids = array();

			foreach ($tagset as $tag) {
				$check = is_term($tag,$taxonomy);
				if (is_null($check)){
					$tagObj = wp_insert_term($tag,$taxonomy);
					$tagids[] = $tagObj[term_id];
				} else {
					echo "$tag already exists as a tag.  You should merge it.<br />";
				}
			}

			$keepold = false;
			foreach($tagids as $newtagid) {
				if ($taggedObjects) {
					foreach ($taggedObjects as $taggedObjId) {
						wp_set_object_terms($taggedObjId,$newtagid,$taxonomy,true);
					}
				} 
				
				if ($newtagid == $tagid) {
					$keepold = true;
				}
			}

			// If a tag is split, and not retained, then remove it.
			if (!$keepold) {
				wp_delete_term($tagid, $taxonomy);
			}
			echo "<div id=\"message\"  class=\"updated fade\"><p>Tags have been updated.</p></div>";
		}
		
		if ($_GET["updateaction"] == __("Merge")) {
			$mergeTags = $_GET["mergeTags"];
			$postids = array();

			foreach($mergeTags as $mergeTag) {
				if ($mergeTag != $tagid) {
					$postids = array_merge($postids, get_objects_in_term($mergeTag, $taxonomy));
					wp_delete_term($mergeTag, $taxonomy);
				}
			}

 			$postids = array_flip(array_flip($postids));
 			$tag = get_term($tagid, $taxonomy);
			foreach($postids as $postid) {
				wp_set_object_terms($postid,$tag->slug,$taxonomy,true);
			}
		}

		if ($_GET["updateaction"] ==__("Delete Term")) {
			wp_delete_term($tagid, $taxonomy);
			echo "<div id=\"message\" class=\"updated fade\"><p>Term has been deleted.</p></div>";
		}
	}

	$taxonomies = get_object_taxonomies('post');
	
	$tags = (array) get_terms($taxonomy,'get=all');

	echo '<fieldset class="options"><legend>' . __("Edit Post Taxonomy") .'</legend>';

	if ($taxonomies) {
		?>
		<h3><?php _e('Select Taxonomy') ?></h3>
		<form action="<?php echo $siteurl ?>/wp-admin/edit.php">
		<select name="selectedTaxonomy" id="selectedTaxonomy">
			<option value="">-- <?php _e('Please Select') ?> --</option>
			<?php foreach($taxonomies as $taxo) {
				echo "<option value=\"$taxo\"";
				if ($taxo == $taxonomy) echo " selected";
				echo ">$taxo</option>";
			} ?>
		</select>
		<input type="hidden" name="action" value="switchtaxonomy">
		<input type="hidden" name="page" value="TagManagingThing.php">
		<input type="submit" value="<?php _e('Switch') ?>" />
		</form>
		<?php
	}

	if ($tags) {
	?>
		<h3><?php _e('Select Term') ?></h3>
		<form action="<?php echo $siteurl ?>/wp-admin/edit.php">
		<select name="edittag" id="edittag" onChange="Things_GetTagData()">
		<option value="">-- <?php _e('Please Select') ?> --</option>
		<?php
		foreach($tags as $tag) {
			echo "<option value=\"$tag->term_id\">$tag->name</option>";
		}?>
		</select>
		<div id="editTagPanel" style="display:none">
		
		<table>
		<tr><td><label for="renametag">Name</label></td>
			<td><input type="text" name="renametag" id="renametag"></td>
		</tr>
		<tr><td><label for="renameslug">Slug</label></td>
			<td><input type="text" name="renameslug" id="renameslug"></td>
		</tr>
		</table>

		<input type="submit" name="updateaction" value="<?php _e("Save") ?>"> <input type="submit" name="updateaction" value="<?php _e("Delete Term") ?>" OnClick="javascript:return(confirm('<?php _e("Are you sure you want to delete this term?")?>'))">

		<h4>Change Term Taxonomy</h4>
		<p>Changing the taxonomy of a term will change it to belong to the selected taxonomy, move the existing associations, and remove it from this taxonomy.  If the term already exists,  it will be merged into the existing term.</p>
		<select name="newtaxonomy">
		<?php foreach($taxonomies as $taxo) {
			if ($taxo != $taxonomy) {
				echo "<option value=\"$taxo\">$taxo</option>";
			}
		}?>
		</select>
		<input type="submit" name="updateaction" value="<?php _e("Change Taxonomy")?>" />

		<h4>Split a Tag</h4>
		<input type="text" name="split" id="split"><input type="submit" name="updateaction" value="<?php _e("Split") ?>">

		<h4>Merge Tags</h4>
		<p>Merging will delete the selected tags and associate their posts with this tag.</p>
		<select name="mergeTags[]" multiple="true" rows="1">
		<?php
		foreach($tags as $tag) {
			echo "<option value=\"$tag->term_id\">$tag->name</option>";
		}?>
		</select><input type="submit" name="updateaction" value="<?php _e("Merge") ?>" />

		<input type="hidden" id="updateTaxonomy" name="selectedTaxonomy" value="<?php echo $taxonomy?>">
		<input type="hidden" name="action" value="savetagupdate">
		<input type="hidden" name="page" value="TagManagingThing.php">
		</div>
		</form>
		<?php
	} else {
		echo '<p>' . __('No tags are in use at the moment.') . '</p>';
	}
	echo "</fieldset>";
}

	function tmt_thing_ajax_client() {
	  // use JavaScript SACK library for AJAX
	  wp_print_scripts( array( 'sack' ));

	  // Define custom JavaScript functions
	?>
	<script type="text/javascript">
	function Things_GetTagData()
	{
		var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    
		var tag_id = document.getElementById("edittag").value;
		if (tag_id != "") {
			mysack.execute = 1;
			mysack.method = 'POST';
			mysack.setVar( "action", "tmt_things_getTag" );
			mysack.setVar( "tagid", document.getElementById("edittag").value);
			mysack.setVar( "taxonomy", document.getElementById("updateTaxonomy").value);			
			mysack.encVar( "cookie", document.cookie, false );
			mysack.onError = function() { alert('AJAX error in retrieving synonyms' )};
			mysack.runAJAX();
		} else {
			alert("no tag");
		}
		return true;
	}

	function Things_ProcessTag(tag,slug) {
		editTagPanel = document.getElementById("editTagPanel");
		editTagPanel.style.display = "block";
		
		renametag = document.getElementById("renametag");
		renameslug = document.getElementById("renameslug");
		
		renametag.value = tag;
		renameslug.value = slug;
	}
	</script>
	<?php
	}
	
	function tmt_thing_ajax_server_tag() {
		$tagid = $_REQUEST['tagid'];
		$taxo = $_REQUEST['taxonomy'];
		
		$tag = get_term($tagid, $taxo);
		
		die("Things_ProcessTag('$tag->name','$tag->slug');");
	}

/**
 */
function tmt_thing_admin_menus() {
	// Add a new menu under Manage:
	add_management_page('Tag Management', 'Tags', 8, basename(__FILE__), 'tmt_thing_admin');
}

// Admin menu items
add_action('admin_menu', 'tmt_thing_admin_menus');
add_action('admin_print_scripts', 'tmt_thing_ajax_client');
add_action('wp_ajax_tmt_things_getTag', 'tmt_thing_ajax_server_tag');
?>