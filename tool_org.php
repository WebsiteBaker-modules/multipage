<?php

// $Id: tool.php 399 2006-12-24 07:50:44Z Ruebenwurzel $

/*


*/

// Direct access prevention
defined('WB_PATH') OR die(header('Location: ../index.php'));
$MSGMP['NoName'] = "No Files Were Given Names";
$MSGMP['PagesCreated'] = "Pages Created Are:";


if (isset($_POST['button'])) {

	// Include the WB functions file
	require_once(WB_PATH.'/framework/functions.php');

	$namearr = $_POST['title'];
	$typearr = $_POST['type'];
	$parentarr = $_POST['parent'];
	$visibilityarr = $_POST['visibility'];
	$error= '';

	for($i = 0; $i < count($namearr) ; $i++) {
		$title = trim($namearr[$i]);
		$title = htmlspecialchars($title);
		$module = $typearr[$i];
		$parent = $parentarr[$i];
		$visibility = $visibilityarr[$i];
		$admin_groups = '';
		$viewing_groups = '';
		$admin_groups[0] = '1';
		$viewing_groups[0] = '1';

		if($title != '' and substr($title,0,1)!='.') {
			if ($parent!=0) {
				if (!$admin->get_page_permission($parent,'admin')) {
					$error[] .= ($MESSAGE['PAGES']['INSUFFICIENT_PERMISSIONS']);
					$flag = false;
				} elseif (!$admin->get_permission('pages_add_l0','system')) {
					$error[] .= ($MESSAGE['PAGES']['INSUFFICIENT_PERMISSIONS']);
					$flag = false;
				}	
			}
			// Check to see if page created has needed permissions
			if(!in_array(1, $admin->get_groups_id())) {
				$admin_perm_ok = false;
				foreach ($admin_groups as $adm_group) {
					if (in_array($adm_group, $admin->get_groups_id())) {
						$admin_perm_ok = true;
					}
				}
				if ($admin_perm_ok == false) {
					$error[] .= ($MESSAGE['PAGES']['INSUFFICIENT_PERMISSIONS']);
					$flag = false;
				}
				$admin_perm_ok = false;
				foreach ($viewing_groups as $view_group) {
					if (in_array($view_group, $admin->get_groups_id())) {
						$admin_perm_ok = true;
					}
				}
				if ($admin_perm_ok == false) {
					$error[] .= ($MESSAGE['PAGES']['INSUFFICIENT_PERMISSIONS']);
					$flag = false;
				}
			}
			
			// Work-out what the link and page filename should be
			if($parent == '0') {
				$link = '/'.page_filename($title);
				$filename = WB_PATH.PAGES_DIRECTORY.'/'.page_filename($title).'.php';
			} else {
				$parent_section = '';
				$parent_titles = array_reverse(get_parent_titles($parent));
				foreach($parent_titles AS $parent_title) {
					$parent_section .= page_filename($parent_title).'/';
				}
				if($parent_section == '/') { $parent_section = ''; }
				$link = '/'.$parent_section.page_filename($title);
				$filename = WB_PATH.PAGES_DIRECTORY.'/'.$parent_section.page_filename($title).'.php';
				make_dir(WB_PATH.PAGES_DIRECTORY.'/'.$parent_section);
			}
			
			// Check if a page with same page filename exists
			$flag = true;
			$get_same_page = $database->query("SELECT page_id FROM ".TABLE_PREFIX."pages WHERE link = '$link'");
			if($get_same_page->numRows() > 0 OR file_exists(WB_PATH.PAGES_DIRECTORY.$link.'.php') OR file_exists(WB_PATH.PAGES_DIRECTORY.$link.'/')) {
				$error[] .= "<br /><span class='red'><b>#$i: " ."</b> ". $MESSAGE['PAGES']['PAGE_EXISTS'] ." ($link)<span><br />";

				$flag = false;
			}
			if($flag) {
				$admin_groups = implode(',', $admin_groups);
				$viewing_groups = implode(',', $viewing_groups);

				$run[$i]['msg'] = "$i) Title: $title Module: $module Parent: $parent<br />";
				$language = DEFAULT_LANGUAGE;
				$time = mktime();
				$uid = $admin->get_user_id();
				$sql = "INSERT INTO ".TABLE_PREFIX."pages 
						SET
							page_title='$title',
							menu_title='$title',
							parent='$parent',
							template='',
							target='_top',
							position='2000',
							visibility='$visibility',
							searching='1',
							link='$link',
							menu='1',
							language='$language',
							admin_groups='$admin_groups',
							viewing_groups='$viewing_groups',
							modified_when='$time',
							modified_by='$uid'";
				$run[$i]['sql'] = $sql;
				$run[$i]['num'] = $i;
				$run[$i]['module'] = $module;
				$run[$i]['filename'] = $filename;
				$created[] = "$title";

			}
			$flag = true;
		} else if (substr($title,0,1) =='.') {
			$error[] .= ($MESSAGE['PAGES']['BLANK_PAGE_TITLE'] ." - $title");
		}
	}
	if(!isset($error[0]) or $error[0] == '') {
		if (isset($run)) {
/*			foreach($run as $row) {
				$sql = $row['sql'];
				echoh("==>$sql<br />");
				$database->query($sql);
				$id = $database->get_one("SELECT LAST_INSERT_ID()");
				$run[$row['num']]['page_id'] = $id;
				$cellid = $row['num'] +1;
				$pagenum[] = "UPDATE pages set parent = '$id' where parent = '-{$cellid}'";
			}
			echoh( "<hr />");
			foreach($pagenum as $row)  {
				$database->query($row);
				echoh( $row . "<br />");
			}
*/			foreach($run as $row)  {
				require_once (WB_PATH.'/framework/class.order.php');
				$sql = $row['sql'];
				echoh("==>$sql<br />");
				$database->query($sql);
				$id = $database->get_one("SELECT LAST_INSERT_ID()");
				
/*				$order = new order(TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
				// First clean order
				$order->clean($parent);
				
//				$id = $database->get_one("SELECT LAST_INSERT_ID()");
*/				
				$query = "SELECT * FROM ".TABLE_PREFIX."pages WHERE page_id = '{$id}'";
				echoh($query . "<br />");
				$pagequery = $database->query($query);
				$page = $pagequery->fetchRow();
				$parent = $page['parent'];
				$module = $row['module'];
				$page_id = $page['page_id'];
				$filename = $row['filename'];

				// Get new order
//				$position = $order->get_new($parent);
//				echoh("Position: $position<br>");
				// Work-out if the page parent (if selected) has a seperate template to the default
				$query = "SELECT template FROM ".TABLE_PREFIX."pages WHERE page_id = '$parent'";
				echoh($query . "<br />Module: $module<br />");
				$query_parent = $database->query($query);
				if($query_parent->numRows() > 0) {
					$fetch_parent = $query_parent->fetchRow();
					$template = $fetch_parent['template'];
				} else {
					$template = '';
				}
		
				// Work out level
				$level = level_count($page_id);
				// Work out root parent
				$root_parent = root_parent($page_id);
				// Work out page trail
				$page_trail = get_page_trail($page_id);
				
				// Update page with new level and link
				$query = "UPDATE ".TABLE_PREFIX."pages SET level = '$level', root_parent = '$root_parent', page_trail = '$page_trail', template = '$template' WHERE page_id = '$page_id'";
				echoh($query . "<br />");
				$database->query($query);
		
				// Create a new file in the /pages dir
				create_access_file($filename, $page_id, $level);
				
				/* clean up page order */
				$order = new order(TABLE_PREFIX.'pages', 'position', 'page_id', 'parent');
				// First clean order
				$order->clean($parent);

				// Get new order for section
				$order = new order(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
				$position = $order->get_new($parent);
				
				// Add new record into the sections table
				$query = "INSERT INTO ".TABLE_PREFIX."sections (page_id,position,module,block) VALUES ('$page_id','$position', '$module','1')";
				echoh($query . "<br />");
				$database->query($query);
				
				// Get the section id
				$section_id = $database->get_one("SELECT LAST_INSERT_ID()");
				
				// Include the selected modules add file if it exists
				if(file_exists(WB_PATH.'/modules/'.$module.'/add.php')) {
					require(WB_PATH.'/modules/'.$module.'/add.php');
				}
			}	
			echo "<br /><strong>". $MESSAGE['PAGES']['ADDED'] .":</strong><ul class='listcreated'>";
			foreach($created as $row) {
				echo ( "<li>" . $row . "</li>");
			}
			echo '</ul>';	
			$namearr = '';
		} else {
			echo $MESSAGE['PAGES']['BLANK_PAGE_TITLE'];
		}
	} else {
		foreach($error as $row) {
			echo $row . "";
		}
	}
}

$num = 10;
$self= $_SERVER['PHP_SELF'] ."?tool=multiplepage";
$typeoptions = '';

echo "	<br />
		<form action='$self' method='post'>
		<table border='0' class='row_a' width='100%'>
		<thead><tr><td>#</td><td>{$TEXT['TITLE']}</td><td>{$TEXT['TYPE']}</td><td>{$TEXT['PARENT']}</td><td>{$TEXT['VISIBILITY']}</td></tr></thead>
		<tfoot><td colspan='5'></td></tfoot>";

$parentoptions = parent_list(0);

// Set module permissions
$module_permissions = $_SESSION['MODULE_PERMISSIONS'];

$result = $database->query("SELECT * FROM ".TABLE_PREFIX."addons WHERE type = 'module' AND function = 'page' order by name");
if($result->numRows() > 0) {
	while($module = $result->fetchRow()) {
		// Check if user is allowed to use this module
		if(!is_numeric(array_search($module['directory'], $module_permissions))) {
			$selected = ($module['directory'] == 'wysiwyg') ? "selected": "";
			$typeoptions .= <<<END
			<option value="{$module['directory']}" $selected>{$module['name']}</option>
END;
			
		}
	}
}

$namevalue = '';
for ($i = 0; $i < $num; $i++) {
	if (isset($namearr[$i])) $namevalue = $namearr[$i];
	echo <<<END

	<td align='center'><small>$i </small></td>
		<td><input type=text name=title[] value='$namevalue' style='width: 250px;'></td>
		<td>
			<select name=type[] style=''>
				$typeoptions
			</select>
		</td>
		<td>
			<select name=parent[] style=''>
				<option value='0'>none</option>
				$parentoptions
			</select>
		</td>
		<td>
			<select name=visibility[] style=''>
				<option value=public>public</option>
				<option value=hidden>hidden</option>
				<option value=none>none</option>				
			</select>
		</td>
	</tr>		

END;
}
echo "</table>
<table width='100%'><tr><td></td>
<td align='right'>
	 			<button name='button' type='submit' class='button save'><img src='".WB_URL."/modules/multiplepage/ok.gif' alt='' width='16' height='16' border='0' />&nbsp;{$TEXT['SAVE']}</button>
</td>
</tr></table>
</form>
";

function echoh($str) {
//	echo $str;
}

// Parent page list
function parent_list($parent) {
	global $admin, $database, $template, $results_array;
	
	$options = '';
	$query = "SELECT * FROM ".TABLE_PREFIX."pages WHERE parent = '$parent' ORDER BY position ASC";
	$get_pages = $database->query($query);
	while($page = $get_pages->fetchRow()) {
		if($admin->page_is_visible($page)==false)
			continue;
		// If the current page cannot be parent, then its children neither
		$list_next_level = true;
		// Stop users from adding pages with a level of more than the set page level limit
		if($page['level']+1 < PAGE_LEVEL_LIMIT) {
			// Get user perms
			$admin_groups = explode(',', str_replace('_', '', $page['admin_groups']));
			$admin_users = explode(',', str_replace('_', '', $page['admin_users']));

			$in_group = FALSE;
			foreach($admin->get_groups_id() as $cur_gid){
			    if (in_array($cur_gid, $admin_groups)) {
			        $in_group = TRUE;
			    }
			}
			
			if(($in_group) OR is_numeric(array_search($admin->get_user_id(), $admin_users))) {
				$can_modify = true;
			} else {
				$can_modify = false;
			}
			// Title -'s prefix
			$title_prefix = '';
			for($i = 1; $i <= $page['level']; $i++) { $title_prefix .= ' - '; }
			$options .= "<option value={$page['page_id']}>{$title_prefix}{$page['page_title']}</option>";
		}
		if ($list_next_level)
			$options .= parent_list($page['page_id']);
	}
	return( $options);
}


?>
