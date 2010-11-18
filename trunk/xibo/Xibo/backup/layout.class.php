<?php
/*
 * Xibo - Digitial Signage - http://www.xibo.org.uk
 * Copyright (C) 2006,2007,2008 Daniel Garner and James Packer
 *
 * This file is part of Xibo.
 *
 * Xibo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version. 
 *
 * Xibo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Xibo.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('XIBO') or die("Sorry, you are not allowed to directly access this page.<br /> Please press the back button in your browser.");
 
class layoutDAO 
{
	private $db;
	private $user;
	private $has_permissions = true;
	
	private $isadmin = false;
	private $sub_page = "";
	
	private $layoutid;
	private $layout;
	private $permissionid;
	private $retired;
	private $description;
	private $tags;
	
	private $xml;
	
	//background properties
	private $backgroundImage;
	private $backgroundColor;
	
	/**
	 * Layout Page Logic
	 * @return 
	 * @param $db Object
	 */
	function __construct(database $db, user $user) 
	{
		$this->db 	=& $db;
		$this->user =& $user;
		
		$usertype 		= Kit::GetParam('usertype', _SESSION, _INT);
		$this->sub_page	= Kit::GetParam('sp', _GET, _WORD, 'view');
		$ajax			= Kit::GetParam('ajax', _GET, _WORD, 'false');
		
		$this->layoutid	= Kit::GetParam('layoutid', _REQUEST, _INT);

		// Include the layout data class		
		include_once("lib/data/layout.data.class.php");
		
		//set the information that we know
		if ($usertype == 1) $this->isadmin = true;
		
		//if we have modify selected then we need to get some info
		if ($this->layoutid != "") 
		{
			$this->sub_page = "edit";

			$sql  = " SELECT layout, description, permissionID, userid, retired, tags, xml FROM layout ";
			$sql .= sprintf(" WHERE layoutID = %d ", $this->layoutid);

			if(!$results = $db->query($sql)) 
			{
				trigger_error($db->error());
				trigger_error(__("Cannot retrieve the Information relating to this layout. The layout may be corrupt."), E_USER_ERROR);
			}

			if ($db->num_rows($results) == 0) $this->has_permissions = false;
			
			while($aRow = $db->get_row($results)) 
			{
				$this->layout 		= Kit::ValidateParam($aRow[0], _STRING);
				$this->description 	= Kit::ValidateParam($aRow[1], _STRING);
				$this->permissionid = Kit::ValidateParam($aRow[2], _INT);	
				$ownerid 			= Kit::ValidateParam($aRow[3], _INT);	
				$this->retired 		= Kit::ValidateParam($aRow[4], _INT);	
				$this->tags	 		= Kit::ValidateParam($aRow[5], _STRING);	
				$this->xml			= $aRow[6];	
				
				// get the permissions
				global $user;
				list($see_permission , $this->has_permissions) = $user->eval_permission($ownerid, $this->permissionid);
				
				// check on permissions
				if ($ajax == "true" && (!$this->has_permissions)) 
				{
					trigger_error(__("You do not have permissions to edit this layout"), E_USER_ERROR);
				}
			}
		}
	}

	function on_page_load() 
	{
		return "";
	}

	function echo_page_heading() 
	{
		echo __("Layouts");
		return true;
	}
	
	function displayPage() 
	{
		$db =& $this->db;
		
		switch ($this->sub_page) 
		{	
			case 'view':
				require("template/pages/layout_view.php");
				break;
				
			case 'edit':
				require("template/pages/layout_edit.php");
				break;
				
			default:
				break;
		}
		
		return false;
	}
	
	function LayoutFilter() 
	{
		$db 	=& $this->db;
		
		$layout = ""; //3
		if (isset($_SESSION['layout']['filter_layout'])) $layout = $_SESSION['layout']['filter_layout'];
		
		//sharing list
		$shared = "All";
		if (isset($_SESSION['layout']['permissionid'])) $shared = $_SESSION['layout']['permissionid'];
		$shared_list = dropdownlist("SELECT 'all','All' UNION SELECT permissionID, permission FROM permission", "permissionid", $shared);
		
		//retired list
		$retired = "0";
		if(isset($_SESSION['layout']['filter_retired'])) $retired = $_SESSION['layout']['retired'];
		$retired_list = listcontent("all|All,1|Yes,0|No","filter_retired",$retired);
		
		//owner list
		$filter_userid = "";
		if(isset($_SESSION['layout']['filter_userid'])) $filter_userid = $_SESSION['layout']['filter_userid'];
		$user_list = listcontent("all|All,".userlist("SELECT DISTINCT userid FROM layout"),"filter_userid", $filter_userid);
		
		//tags list
		$filter_tags = "";
		if(isset($_SESSION['layout']['filter_tags'])) $filter_tags = $_SESSION['layout']['filter_tags'];

		$msgName	= __('Name');
		$msgOwner	= __('Owner');
		$msgShared	= __('Shared');
		$msgTags	= __('Tags');
		$msgRetired	= __('Retired');

		$filterForm = <<<END
		<div class="FilterDiv" id="LayoutFilter">
			<form onsubmit="return false">
				<input type="hidden" name="p" value="layout">
				<input type="hidden" name="q" value="LayoutGrid">
		
			<table class="layout_filterform">
				<tr>
					<td>$msgName</td>
					<td><input type="text" name="filter_layout"></td>
					<td>$msgOwner</td>
					<td>$user_list</td>
					<td>$msgShared</td>
					<td>$shared_list</td>
				</tr>
				<tr>
					<td>$msgTags</td>
					<td><input type="text" name="filter_tags" value="$filter_tags" /></td>
					<td>$msgRetired</td>
					<td>$retired_list</td>
				</tr>
			</table>
			</form>
		</div>
END;
		
		$id = uniqid();
		
		$xiboGrid = <<<HTML
		<div class="XiboGrid" id="$id">
			<div class="XiboFilter">
				$filterForm
			</div>
			<div class="XiboData">
			
			</div>
		</div>
HTML;
		echo $xiboGrid;
	}
	

	/**
	 * Adds a layout record to the db
	 * @return 
	 */
	function add() 
	{
		$db 			=& $this->db;
		$response		= new ResponseManager();

		$layout 		= Kit::GetParam('layout', _POST, _STRING);
		$description 	= Kit::GetParam('description', _POST, _STRING);
		$permissionid 	= Kit::GetParam('permissionid', _POST, _INT);
		$tags		 	= Kit::GetParam('tags', _POST, _STRING);
		$templateid		= Kit::GetParam('templateid', _POST, _STRING, 'none');
		$userid 		= Kit::GetParam('userid', _SESSION, _INT);
		$currentdate 	= date("Y-m-d H:i:s");
		
		//validation
		if (strlen($layout) > 50 || strlen($layout) < 1) 
		{
			$response->SetError(__("Layout Name must be between 1 and 50 characters"));
			$response->Respond();
		}
		
		if (strlen($description) > 254) 
		{
			$response->SetError(__("Description can not be longer than 254 characters"));
			$response->Respond();
		}
		
		if (strlen($tags) > 254) 
		{
			$response->SetError(__("Tags can not be longer than 254 characters"));
			$response->Respond();
		}
		
		$check 	= sprintf("SELECT layout FROM layout WHERE layout = '%s' AND userID = %d ", $layout, $userid);
		$result = $db->query($check) or trigger_error($db->error());
		
		//Layouts with the same name?
		if($db->num_rows($result) != 0) 
		{
			$response->SetError(sprintf(__("You already own a layout called '%s'. Please choose another."), $layout));
			$response->Respond();
		}
		//end validation
		
		//What do we do with the template...
		if ($templateid == "none")
		{
			//make some default XML
			$xmlDoc = new DOMDocument("1.0");
			$layoutNode = $xmlDoc->createElement("layout");
			
			$layoutNode->setAttribute("width", 800);
			$layoutNode->setAttribute("height", 450);
			$layoutNode->setAttribute("bgcolor", "#000000");
			$layoutNode->setAttribute("schemaVersion", Config::Version($db, 'XlfVersion'));
			
			$xmlDoc->appendChild($layoutNode);
			
			$xml = $xmlDoc->saveXML();
		}
		else
		{
			//get the template XML
			$SQL = sprintf("SELECT xml FROM template WHERE templateID = %d ", $templateid);
			if (!$result = $db->query($SQL))
			{
				$response->SetError(__("Error getting this template."));
				$response->Respond();
			}
			
			$row = $db->get_row($result);
			$xml = $row[0];
		}

		if(!$id = $this->db_add($layout, $description, $permissionid, $tags, $userid, $xml)) 
		{
			//otherwise we need to take them back and tell them why the playlist has failed.
			$response->SetError(__("Unknown error adding layout."));
			$response->Respond();
		}
		
		// Create an array out of the tags
		$tagsArray = explode(' ', $tags);
		
		// Add the tags XML to the layout
		$layoutObject = new Layout($db);
		
		if (!$layoutObject->EditTags($id, $tagsArray))
		{
			//there was an ERROR
			trigger_error($layoutObject->GetErrorMessage(), E_USER_ERROR);
		}

		$response->SetFormSubmitResponse(__('Layout Details Changed.'), true, sprintf("index.php?p=layout&layoutid=%d&modify=true", $id));
		$response->Respond();
	}

	function db_add($layout, $description, $permissionid, $tags, $userid, $xml) 
	{
		$db =& $this->db;

		$currentdate 	= date("Y-m-d H:i:s");

		$query = <<<END
		INSERT INTO layout (layout, description, userID, permissionID, createdDT, modifiedDT, tags, xml)
		 VALUES ('%s', '%s', %d, %d, '%s', '%s', '%s', '%s')
END;

		$query = sprintf($query, 
							$db->escape_string($layout), 
							$db->escape_string($description), $userid, $permissionid, 
							$db->escape_string($currentdate), 
							$db->escape_string($currentdate), 
							$db->escape_string($tags), 
							$xml);

		if(!$id = $db->insert_query($query)) 
		{
			trigger_error($db->error());
			return false;
		}

		return $id;
	}


	/**
	 * Modifies a layout record
	 *
	 * @param int $id
	 */
	function modify ()
	{
		$db 			=& $this->db;
		$response		= new ResponseManager();

		$layout 		= Kit::GetParam('layout', _POST, _STRING);
		$description 	= Kit::GetParam('description', _POST, _STRING);
		$permissionid 	= Kit::GetParam('permissionid', _POST, _INT);
		$tags		 	= Kit::GetParam('tags', _POST, _STRING);
		$retired 		= Kit::GetParam('retired', _POST, _INT, 0);
		
		$userid 		= Kit::GetParam('userid', _SESSION, _INT);
		$currentdate 	= date("Y-m-d H:i:s");
		
		//validation
		if (strlen($layout) > 50 || strlen($layout) < 1) 
		{
			$response->SetError(__("Layout Name must be between 1 and 50 characters"));
			$response->Respond();
		}
		
		if (strlen($description) > 254) 
		{
			$response->SetError(__("Description can not be longer than 254 characters"));
			$response->Respond();
		}
		
		if (strlen($tags) > 254) 
		{
			$response->SetError(__("Tags can not be longer than 254 characters"));
			$response->Respond();
		}
		
		$check = sprintf("SELECT layout FROM layout WHERE layout = '%s' AND userID = %d AND layoutid <> %d ", $db->escape_string($layout), $userid, $this->layoutid);
		$result = $db->query($check) or trigger_error($db->error());
		
		//Layouts with the same name?
		if($db->num_rows($result) != 0) 
		{
			$response->SetError(sprintf(__("You already own a layout called '%s'. Please choose another."), $layout));
			$response->Respond();
		}
		//end validation

		$SQL = <<<END

		UPDATE layout SET
			layout = '%s',
			permissionID = %d,
			description = '%s',
			modifiedDT = '%s',
			retired = %d,
			tags = '%s'
		
		WHERE layoutID = %s;		
END;

		$SQL = sprintf($SQL, 
						$db->escape_string($layout), $permissionid, 
						$db->escape_string($description), 
						$db->escape_string($currentdate), $retired, 
						$db->escape_string($tags), $this->layoutid);
		
		Debug::LogEntry($db, 'audit', $SQL);

		if(!$db->query($SQL)) 
		{
			trigger_error($db->error());
			$response->SetError(sprintf(__("Unknown error editing %s"), $layout));
			$response->Respond();
		}
		
		// Create an array out of the tags
		$tagsArray = explode(' ', $tags);
		
		// Add the tags XML to the layout
		$layoutObject = new Layout($db);
		
		if (!$layoutObject->EditTags($this->layoutid, $tagsArray))
		{
			//there was an ERROR
			trigger_error($layoutObject->GetErrorMessage(), E_USER_ERROR);
		}

		$response->SetFormSubmitResponse(__('Layout Details Changed.'));
		$response->Respond();
	}
	
	function delete_form() 
	{
		$db 		=& $this->db;
		$response 	= new ResponseManager();
		
		
		//expect the $layoutid to be set
		$layoutid = $this->layoutid;
		
		//Are we going to be able to delete this?
		// - Has it been scheduled
		$SQL = sprintf("SELECT layoutid FROM schedule WHERE layoutid = %d", $layoutid);
		
		if (!$results = $db->query($SQL)) 
		{
			trigger_error($db->error());
			trigger_error(__("Can not get layout information"), E_USER_ERROR);
		}

		$msgYes		= __('Yes');
		$msgNo		= __('No');
		
		if ($db->num_rows($results) == 0) 
		{
			//we can delete
			$msgWarn	= __('Are you sure you want to delete this layout? All media will be unassigned. Any layout specific media such as text/rss will be lost.');
			
			$form = <<<END
			<form class="XiboForm" method="post" action="index.php?p=layout&q=delete">
				<input type="hidden" name="layoutid" value="$layoutid">
				<p>$msgWarn</p>
				<input type="submit" value="$msgYes">
				<input type="submit" value="$msgNo" onclick="$('#div_dialog').dialog('close');return false; ">
			</form>
END;
		}
		else 
		{
			//we can only retire
			$msgWarn	= __('Sorry, unable to delete this layout.');
			$msgWarn2	= __('Retire this layout instead?');
			
			$form = <<<END
			<form class="XiboForm" method="post" action="index.php?p=layout&q=retire">
				<input type="hidden" name="layoutid" value="$layoutid">
				<p>$msgWarn</p>
				<p>$msgWarn2</p>
				<input type="submit" value="$msgYes">
				<input type="submit" value="$msgNo" onclick="$('#div_dialog').dialog('close');return false; ">
			</form>
END;
		}
		
		$response->SetFormRequestResponse($form, __('Delete this layout?'), '260px', '180px');
		$response->Respond();
	}

	/**
	 * Deletes a layout record from the DB
	 */
	function delete() 
	{
		$db 			=& $this->db;
		$response		= new ResponseManager();
		$layoutid 		= Kit::GetParam('layoutid', _POST, _INT, 0);
		
		if ($layoutid == 0) 
		{
			$response->SetError(__("No Layout selected"));
			$response->Respond();
		}
		
		// Unassign all the Media
		$SQL = sprintf("DELETE FROM lklayoutmedia WHERE layoutID = %d", $layoutid);
		
		if (!$db->query($SQL)) 
		{
			$response->SetError(__("Cannot unassign this layouts media. Please manually unassign."));
			$response->Respond();
		}

		$SQL = " ";
		$SQL .= "DELETE FROM layout ";
		$SQL .= sprintf(" WHERE layoutID = %d", $layoutid);

		if (!$db->query($SQL)) 
		{
			$response->SetError(__("Cannot delete this layout. You may retire it from the Edit form."));
			$response->Respond();
		}

		$response->SetFormSubmitResponse(__("The Layout has been Deleted"));
		$response->Respond();
	}
	
	/**
	 * Retire a Layout
	 * @return 
	 */
	function retire() 
	{
		$db 			=& $this->db;
		$response		= new ResponseManager();
		$layoutid 		= Kit::GetParam('layoutid', _POST, _INT, 0);
		
		if ($layoutid == 0) 
		{
			$response->SetError(__("No Layout selected"));
			$response->Respond();
		}
		
		$SQL = sprintf("UPDATE layout SET retired = 1 WHERE layoutID = %d", $layoutid);
	
		
		if (!$db->query($SQL)) 
		{
			trigger_error($db->error());
			
			$response->SetError(__("Failed to retire, Unknown Error."));
			$response->Respond();
		}

		$response->SetFormSubmitResponse(__('Layout Retired.'));
		$response->Respond();
	}
	
	/**
	 * Shows the Layout Grid
	 * @return 
	 */
	function LayoutGrid() 
	{
		$db 		=& $this->db;
		$user		=& $this->user;
		$response	= new ResponseManager();
		
		$name = Kit::GetParam('filter_layout', _POST, _STRING, '');
		setSession('layout', 'filter_layout', $name);
		
		// Sharing
		$permissionid = Kit::GetParam('permissionid', _POST, _STRING, 'all');
		setSession('layout', 'permissionid', $permissionid);
		
		// User ID
		$filter_userid = Kit::GetParam('filter_userid', _POST, _STRING, 'all');
		setSession('layout', 'filter_userid', $filter_userid);
		
		// Show retired
		$filter_retired = $_REQUEST['filter_retired'];
		setSession('layout', 'filter_userid', $filter_userid);
		
		// Tags list
		$filter_tags = Kit::GetParam("filter_tags", _POST, _STRING);
		setSession('layout', 'filter_tags', $filter_tags);
		
		$SQL = "";
		$SQL .= "SELECT  layout.layoutID, ";
		$SQL .= "        layout.layout, ";
		$SQL .= "        layout.description, ";
		$SQL .= "        layout.userID, ";
		$SQL .= "        permission.permission, ";
		$SQL .= "        permission.permissionID ";
		$SQL .= "FROM    layout ";
		$SQL .= "INNER JOIN permission ON layout.permissionID = permission.permissionID ";
		$SQL .= "WHERE   1                   = 1";
		//name filter
		if ($name != "") 
		{
			$SQL.= " AND  (layout.layout LIKE '%" . sprintf('%s', $name) . "%') ";
		}
		//sharing filter
		if ($permissionid != "all") 
		{
			$SQL .= sprintf(" AND (layout.permissionID = %d) ", $permissionid);
		}
		//owner filter
		if ($filter_userid != "all") 
		{
			$SQL .= sprintf(" AND layout.userid = %d ", $filter_userid);
		}
		//retired options
		if ($filter_retired == "1") 
		{
			$SQL .= " AND layout.retired = 1 ";
		}
		elseif ($filter_retired == "0") 
		{
			$SQL .= " AND layout.retired = 0 ";			
		}
		if ($filter_tags != "")
		{
			$SQL .= " AND layout.tags LIKE '%" . sprintf('%s', $filter_tags) . "%' ";
		}

		if(!$results = $db->query($SQL))
		{
			trigger_error($db->error());
			trigger_error(__("An Unknown error occured when listing the layouts."), E_USER_ERROR);			
		}

		$output = <<<END
		<div class="info_table">
		<table style="width:100%">
			<thead>
				<tr>
				<th>Name</th>
				<th>Description</th>
				<th>Permissions</th>
				<th>Owner</th>
				<th>Group</th>
				<th>Action</th>	
				</tr>
			</thead>
			<tbody>
END;

                $msgCopy = __('Copy');

		while($aRow = $db->get_row($results)) 
		{
			//get the query results
			$layout 		= Kit::ValidateParam($aRow[1], _STRING);
			$description 	= Kit::ValidateParam($aRow[2], _STRING);
			$layoutid 		= Kit::ValidateParam($aRow[0], _INT);
			$userid 		= Kit::ValidateParam($aRow[3], _INT);
			
			//get the username from the userID using the user module
			$username 		= $user->getNameFromID($userid);
			$group			= $user->getGroupFromID($userid);
			
			//assess the permissions of each item
			$permission		= Kit::ValidateParam($aRow[4], _STRING);
			$permissionid	= Kit::ValidateParam($aRow[5], _INT);
			
			//get the permissions
			list($see_permissions , $edit_permissions) = $user->eval_permission($userid, $permissionid);
			
			if ($see_permissions) 
			{
				if ($edit_permissions) 
				{			
					$title = <<<END
					<tr ondblclick="return XiboFormRender('index.php?p=layout&q=displayForm&layoutid=$layoutid')">
END;
				}
				else 
				{
					$msgNoPermission = __('You do not have permission to design this layout');
					
					$title = <<<END
					<tr ondblclick="alert('$msgNoPermission')">
END;
				}
				
				$output .= <<<END
				$title
				<td>$layout</td>
				<td>$description</td>
				<td>$permission</td>
				<td>$username</td>
				<td>$group</td>
END;
				
				if ($edit_permissions) 
				{
					$output .= '<td class="nobr">';
					$output .= '<button href="index.php?p=layout&modify=true&layoutid=' . $layoutid . '" onclick="window.location = $(this).attr(\'href\')"><span>Design</span></button>';
					$output .= '<button class="XiboFormButton" href="index.php?p=layout&q=displayForm&modify=true&layoutid=' . $layoutid . '"><span>Edit</span></button>';
					$output .= '<button class="XiboFormButton" href="index.php?p=layout&q=CopyForm&layoutid=' . $layoutid . '&oldlayout=' . $layout . '"><span>' . $msgCopy . '</span></button>';
					$output .= '<button class="XiboFormButton" href="index.php?p=layout&q=delete_form&layoutid=' . $layoutid . '"><span>Delete</span></button>';
					$output .= '</td>';
				}
				else 
				{
					$output .= '<td class="centered">' . __('None') . '</td>';
				}
				
				$output .= '</tr>';
			}
		}
		$output .= '</tbody></table></div>';
		
		$response->SetGridResponse($output);
		$response->Respond();
	}

	function displayForm () 
	{
		$db 			=& $this->db;
		$user			=& $this->user;
		$response		= new ResponseManager();
		
		$helpManager            = new HelpManager($db, $user);

		$action 		= "index.php?p=layout&q=add";
		
		$layoutid 		= $this->layoutid; 
		$layout 		= $this->layout;
		$description            = $this->description;
		$permissionid           = $this->permissionid;
		$retired		= $this->retired;
		$tags			= $this->tags;
		
		// Help icons for the form
		$nameHelp	= $helpManager->HelpIcon(__("The Name of the Layout - (1 - 50 characters)"), true);
		$descHelp	= $helpManager->HelpIcon(__("An optional description of the Layout. (1 - 250 characters)"), true);
		$tagsHelp	= $helpManager->HelpIcon(__("Tags for this layout - used when searching for it. Space delimited. (1 - 250 characters)"), true);
		$sharedHelp	= $helpManager->HelpIcon(__("The permissions to associate with this Layout"), true);
		$retireHelp	= $helpManager->HelpIcon(__("Retire this layout or not? It will no longer be visible in lists"), true);
		$templateHelp	= $helpManager->HelpIcon(__("Template to create this layout with."), true);
		
		//init the retired option
		$retired_option 	= '';
		$template_option 	= '';
		
		if ($this->layoutid != "") 
		{ 
                        // assume an edit
			$action = "index.php?p=layout&q=modify";
			
			// build the retired option
			$retired_list = listcontent("1|Yes,0|No","retired",$retired);
			$retired_option = <<<END
			<tr>
				<td><label for='retired'>Retired<span class="required">*</span></label></td>
				<td>$retireHelp $retired_list</td>
			</tr>
END;
		}
		else
		{
			//build the template list
			$template_list = dropdownlist("SELECT 'none','None',3,1 UNION SELECT templateID, template, permissionID, userID FROM template ORDER BY 2","templateid", "none", "", false, true, $_SESSION['userid']);
			
			$template_option = <<<END
			<tr>
				<td><label for='templateid'>Template<span class="required">*</span></label></td>
				<td>$templateHelp $template_list</td>
			</tr>
END;
		}
		
		if($permissionid == "") 
		{
			$default = Config::GetSetting($db, "defaultPlaylist");
		}
		else 
		{
			$default = $permissionid;
		}
		
		if($default=="private") $default = 1;
		
		$shared_list = dropdownlist("SELECT permissionID, permission FROM permission", "permissionid", $default);
		
		$msgName	= __('Name');
		$msgName2	= __('The Name of the Layout - (1 - 50 characters)');
		$msgDesc	= __('Description');
		$msgDesc2	= __('An optional description of the Layout. (1 - 250 characters)');
		$msgTags	= __('Tags');
		$msgTags2	= __('Tags for this layout - used when searching for it. Space delimited. (1 - 250 characters)');
		$msgShared	= __('Shared');
		$msgShared2	= __('The permissions to associate with this Layout');
		
		$form = <<<END
		<form id="LayoutForm" class="XiboForm" method="post" action="$action">
			<input type="hidden" name="layoutid" value="$this->layoutid">
		<table>
			<tr>
				<td><label for="layout" accesskey="n" title="$msgName2">$msgName<span class="required">*</span></label></td>
				<td>$nameHelp <input name="layout" type="text" id="layout" value="$layout" tabindex="1" /></td>
			</tr>
			<tr>
				<td><label for="description" accesskey="d" title="$msgDesc2">$msgDesc</label></td>
				<td>$descHelp <input name="description" type="text" id="description" value="$description" tabindex="2" /></td>
			</tr>
			<tr>
				<td><label for="tags" accesskey="d" title="$msgTags2">$msgTags</label></td>
				<td>$tagsHelp <input name="tags" type="text" id="tags" value="$tags" tabindex="3" /></td>
			</tr>
			<tr>
				<td><label for='permissionid' title="$msgShared2">$msgShared<span class="required">*</span></label></td>
				<td>$sharedHelp $shared_list</td>
			</tr>
			$retired_option
			$template_option
		</table>
		</form>
END;

		$response->SetFormRequestResponse($form, __('Add/Edit a Layout.'), '350px', '275px');
                $response->AddButton(__('Help'), 'XiboHelpRender("' . $helpManager->Link('Layout', 'Add') . '")');
		$response->AddButton(__('Cancel'), 'XiboDialogClose()');
		$response->AddButton(__('Save'), '$("#LayoutForm").submit()');
		$response->Respond();
	}
	
	/**
	 * Generates a form for the background edit
	 * @return 
	 */
	function BackgroundForm() 
	{
		$db 		=& $this->db;
		$user		=& $this->user;

		$helpManager	= new HelpManager($db, $user);
		$response	= new ResponseManager();


		//load the XML into a SimpleXML OBJECT
		$xml                = simplexml_load_string($this->xml);

		$backgroundImage    = (string) $xml['background'];
		$backgroundColor    = (string) $xml['bgcolor'];
		$width              = (string) $xml['width'];
		$height             = (string) $xml['height'];
                $bgImageId          = 0;

                // Do we need to override the background with one passed in?
                $bgOveride          = Kit::GetParam('backgroundOveride', _GET, _STRING);

                if ($bgOveride != '')
                    $backgroundImage = $bgOveride;
		
		// Manipulate the images slightly
		if ($backgroundImage != "")
		{
                    // Get the ID for the background image
                    $bgImageInfo = explode('.', $backgroundImage);
                    $bgImageId = $bgImageInfo[0];

                    $thumbBgImage = "index.php?p=module&q=GetImage&id=$bgImageId&width=80&height=80&dynamic";
		}
		else
		{
                    $thumbBgImage = "img/forms/filenotfound.png";
		}

		//A list of available backgrounds
		$backgroundList = dropdownlist("SELECT '0', 'None', 3, 1 AS name, 0 As sort_order UNION SELECT mediaID, name, permissionID, userID, 1 AS sort_order FROM media WHERE type = 'image' AND IsEdited = 0 AND retired = 0 AND storedAs IS NOT NULL ORDER BY sort_order, 1","bg_image", $bgImageId, "onchange=\"background_button_callback()\"", false, true);
		
		//A list of web safe colors
		//Strip the # from the currently set color
		$backgroundColor = trim($backgroundColor,'#');
		
		$webSafeColors = gwsc("bg_color", $backgroundColor);
		
		//Get the ID of the current resolution
		$SQL = sprintf("SELECT resolutionID FROM resolution WHERE width = %d AND height = %d", $width, $height);
		
		if (!$results = $db->query($SQL)) 
		{
			trigger_error($db->error());
			trigger_error(__("Unable to get the Resolution information"), E_USER_ERROR);
		}
		
		$row 		= $db->get_row($results) ;
		$resolutionid 	=  Kit::ValidateParam($row[0], _INT);
		
		//Make up the list
		$resolution_list = dropdownlist("SELECT resolutionID, resolution FROM resolution ORDER BY width", "resolutionid", $resolutionid);
		
		// Help text for fields
		$resolutionHelp = $helpManager->HelpIcon(__("Pick the resolution"), true);
		$bgImageHelp	= $helpManager->HelpIcon(__("Select the background image from the library."), true);
		$bgColorHelp	= $helpManager->HelpIcon(__("Use the color picker to select the background color."), true);
		
		$helpButton 	= $helpManager->HelpButton("content/layout/layouteditor", true);
		
		$msgBg				= __('Background Color');
		$msgBgTitle			= __('Use the color picker to select the background color');
		$msgBgImage			= __('Background Image');
		$msgBgImageTitle	= __('Select the background image from the library');
		$msgRes				= __('Resolution');
		$msgResTitle		= __('Pick the resolution');
		
		// Begin the form output
		$form = <<<FORM
		<form id="LayoutBackgroundForm" class="XiboForm" method="post" action="index.php?p=layout&q=EditBackground">
			<input type="hidden" id="layoutid" name="layoutid" value="$this->layoutid">
			<table>
				<tr>
					<td><label for="bg_color" title="$msgBgTitle">$msgBg</label></td>
					<td>$bgColorHelp $webSafeColors</td>
				</tr>
				<tr>
					<td><label for="bg_image" title="$msgBgImageTitle">$msgBgImage</label></td>
					<td>$bgImageHelp $backgroundList</td>
					<td rowspan="3"><img id="bg_image_image" src="$thumbBgImage" alt="Thumb" />
				</tr>
				<tr>
					<td><label for="resolutionid" title="$msgResTitle">$msgRes<span class="required">*</span></label></td>
					<td>$resolutionHelp $resolution_list</td>
				</tr>
				<tr>
					<td></td>
				</tr>
			</table>
		</form>
FORM;
		
		$response->SetFormRequestResponse($form, __('Change the Background Properties'), '550px', '240px');
                $response->AddButton(__('Help'), 'XiboHelpRender("' . $helpManager->Link('Layout', 'Background') . '")');
                $response->AddButton(__('Add Image'), 'XiboFormRender("index.php?p=module&q=Exec&mod=image&method=AddForm&backgroundImage=true&layoutid=' . $this->layoutid . '")');
		$response->AddButton(__('Cancel'), 'XiboDialogClose()');
		$response->AddButton(__('Save'), '$("#LayoutBackgroundForm").submit()');
		$response->Respond();
	}
	
	/**
	 * Edits the background of the layout
	 * @return 
	 */
	function EditBackground()
	{
		$db 			=& $this->db;
		$user 			=& $this->user;
		$response		= new ResponseManager();

		$layoutid 		= Kit::GetParam('layoutid', _POST, _INT);
		$bg_color 		= '#'.Kit::GetParam('bg_color', _POST, _STRING);
		$mediaID 		= Kit::GetParam('bg_image', _POST, _INT);
		$resolutionid		= Kit::GetParam('resolutionid', _POST, _INT);

                // Get the file URI
                $SQL = sprintf("SELECT StoredAs FROM media WHERE MediaID = %d", $mediaID);

                // Allow for the 0 media idea (no background image)
                if ($mediaID == 0)
                {
                    $bg_image = '';
                }
                else
                {
                    // Look up the bg image from the media id given
                    if (!$bg_image = $db->GetSingleValue($SQL, 'StoredAs', _STRING))
                        trigger_error('No media found for that media ID', E_USER_ERROR);
                }

		// Look up the width and the height
		$SQL = sprintf("SELECT width, height FROM resolution WHERE resolutionID = %d ", $resolutionid);
		
		if (!$results = $db->query($SQL)) 
		{
			trigger_error($db->error());
			$response->SetError(__("Unable to get the Resolution information"));
			$response->Respond();
		}
		
		$row 	= $db->get_row($results) ;
		$width  =  Kit::ValidateParam($row[0], _INT);
		$height =  Kit::ValidateParam($row[1], _INT);
		
		include_once("lib/pages/region.class.php");
		
		$region = new region($db, $user);
		
		if (!$region->EditBackground($layoutid, $bg_color, $bg_image, $width, $height))
		{
			//there was an ERROR
			$response->SetError($region->errorMsg);
			$response->Respond();
		}
		
		// Update the layout record with the new background
		$SQL = sprintf("UPDATE layout SET background = '%s' WHERE layoutid = %d ", $bg_image, $layoutid);
		
		if (!$db->query($SQL)) 
		{
			trigger_error($db->error());
			$response->SetError(__("Unable to update background information"));
			$response->Respond();
		}
		
		$response->SetFormSubmitResponse(__('Layout Details Changed.'), true, sprintf("index.php?p=layout&layoutid=%d&modify=true", $this->layoutid));
		$response->Respond();
	}
	
	/**
	 * Adds a new region for a layout
	 * @return 
	 */
	function AddRegion()
	{
		$db 	=& $this->db;
		$user 	=& $this->user;
		
		//ajax request handler
		$response = new ResponseManager();
		
		$layoutid = Kit::GetParam('layoutid', _REQUEST, _INT, 0);
		
		if ($layoutid == 0)
		{
			trigger_error(__("No layout information available, please refresh the page."), E_USER_ERROR);
		}
		
		include_once("lib/pages/region.class.php");
		
		$region = new region($db, $user);
		
		if (!$region->AddRegion($this->layoutid))
		{
			//there was an ERROR
			trigger_error($region->errorMsg, E_USER_ERROR);
		}
		
		$response->SetFormSubmitResponse(__('Region Added.'), true, "index.php?p=layout&modify=true&layoutid=$layoutid");
		$response->Respond();
	}
	
	/**
	 * Deletes a region and all its media
	 * @return 
	 */
	function DeleteRegion()
	{
		$db 		=& $this->db;
		$user 		=& $this->user;
		$response 	= new ResponseManager();
		
		$layoutid 	= Kit::GetParam('layoutid', _REQUEST, _INT, 0);
		$regionid 	= Kit::GetParam('regionid', _REQUEST, _STRING);
		
		if ($layoutid == 0 || $regionid == '')
		{
			$response->SetError(__("No layout/region information available, please refresh the page and try again."));
			$response->Respond();
		}
		
		include_once("lib/pages/region.class.php");
		
		$region = new region($db, $user);
		
		if (!$region->DeleteRegion($this->layoutid, $regionid))
		{
			//there was an ERROR
			$response->SetError($region->errorMsg);
			$response->Respond();
		}
		
		$response->SetFormSubmitResponse(__('Region Deleted.'), true, sprintf("index.php?p=layout&layoutid=%d&modify=true", $this->layoutid));
		$response->Respond();
	}

        /*
         * Form called by the layout which shows a manual positioning/sizing form.
         */
        function ManualRegionPositionForm()
        {
            $db 	=& $this->db;
            $user 	=& $this->user;
            $response = new ResponseManager();

            $regionid 	= Kit::GetParam('regionid', _GET, _STRING);
            $layoutid 	= Kit::GetParam('layoutid', _GET, _INT);
            $top 	= Kit::GetParam('top', _GET, _INT);
            $left 	= Kit::GetParam('left', _GET, _INT);
            $width 	= Kit::GetParam('width', _GET, _INT);
            $height 	= Kit::GetParam('height', _GET, _INT);
            $layoutWidth = Kit::GetParam('layoutWidth', _GET, _INT);
            $layoutHeight = Kit::GetParam('layoutHeight', _GET, _INT);

            $form = <<<END
		<form class="XiboForm" method="post" action="index.php?p=layout&q=ManualRegionPosition">
                    <input type="hidden" name="layoutid" value="$layoutid">
                    <input type="hidden" name="regionid" value="$regionid">
                    <input id="layoutWidth" type="hidden" name="layoutWidth" value="$layoutWidth">
                    <input id="layoutHeight" type="hidden" name="layoutHeight" value="$layoutHeight">
                    <table>
			<tr>
                            <td><label for="top" title="Offset from the Top Corner">Top Offset</label></td>
                            <td><input name="top" type="text" id="top" value="$top" tabindex="1" /></td>
			</tr>
			<tr>
                            <td><label for="left" title="Offset from the Left Corner">Left Offset</label></td>
                            <td><input name="left" type="text" id="left" value="$left" tabindex="2" /></td>
			</tr>
			<tr>
                            <td><label for="width" title="Width of the Region">Width</label></td>
                            <td><input name="width" type="text" id="width" value="$width" tabindex="3" /></td>
			</tr>
			<tr>
                            <td><label for="height" title="Height of the Region">Height</label></td>
                            <td><input name="height" type="text" id="height" value="$height" tabindex="4" /></td>
			</tr>
                        <tr>
                            <td></td>
                            <td>
                                <input type='submit' value="Save" / >
                                <input id="btnCancel" type="button" title="No / Cancel" onclick="$('#div_dialog').dialog('close');return false; " value="Cancel" />
                                <input id="btnFullScreen" type='button' value="Full Screen" / >
                            </td>
                        </tr>
                    </table>
		</form>
END;

            $response->SetFormRequestResponse($form, 'Manual Region Positioning', '350px', '275px', 'manualPositionCallback');
            $response->Respond();
        }

        function ManualRegionPosition()
        {
            $db 	=& $this->db;
            $user 	=& $this->user;
            $response   = new ResponseManager();

            $layoutid   = Kit::GetParam('layoutid', _POST, _INT);
            $regionid   = Kit::GetParam('regionid', _POST, _STRING);
            $top        = Kit::GetParam('top', _POST, _INT);
            $left       = Kit::GetParam('left', _POST, _INT);
            $width      = Kit::GetParam('width', _POST, _INT);
            $height 	= Kit::GetParam('height', _POST, _INT);

            Debug::LogEntry($db, 'audit', sprintf('Layoutid [%d] Regionid [%s]', $layoutid, $regionid), 'layout', 'ManualRegionPosition');

            // Remove the "px" from them
            $width  = str_replace('px', '', $width);
            $height = str_replace('px', '', $height);
            $top    = str_replace('px', '', $top);
            $left   = str_replace('px', '', $left);

            include_once("lib/pages/region.class.php");

            $region = new region($db, $user);

            if (!$region->EditRegion($layoutid, $regionid, $width, $height, $top, $left))
                trigger_error($region->errorMsg, E_USER_ERROR);

            $response->SetFormSubmitResponse('Region Resized', true, "index.php?p=layout&modify=true&layoutid=$layoutid");
            $response->Respond();
        }
	
	/**
	 * Edits the region information
	 * @return 
	 */
	function RegionChange()
	{
		$db 	=& $this->db;
		$user 	=& $this->user;
		
		// ajax request handler
		$response = new ResponseManager();
		
		//Vars
		$regionid 	= Kit::GetParam('regionid', _REQUEST, _STRING);
		$top            = Kit::GetParam('top', _POST, _INT);
                $left           = Kit::GetParam('left', _POST, _INT);
                $width          = Kit::GetParam('width', _POST, _INT);
                $height 	= Kit::GetParam('height', _POST, _INT);

		// Remove the "px" from them
		$width 	= str_replace("px", '', $width);
		$height = str_replace("px", '', $height);
		$top 	= str_replace("px", '', $top);
		$left 	= str_replace("px", '', $left);
		
		include_once("lib/pages/region.class.php");
		
		$region = new region($db, $user);
		
		if (!$region->EditRegion($this->layoutid, $regionid, $width, $height, $top, $left))
		{
			//there was an ERROR
			trigger_error($region->errorMsg, E_USER_ERROR);
		}
		
		$response->SetFormSubmitResponse('');
		$response->hideMessage = true;
		$response->Respond();
	}
	
	/**
	 * Re-orders a medias regions
	 * @return 
	 */
	function RegionOrder()
	{
		$db 	=& $this->db;
		$user 	=& $this->user;
		
		// ajax request handler
		$response = new ResponseManager();
		
		//Vars
		$regionid 		= Kit::GetParam('regionid', _POST, _STRING);
		$mediaid 		= Kit::GetParam('mediaid', _POST, _STRING);
		$lkid        		= Kit::GetParam('lkid', _POST, _STRING, '');
		$sequence 		= Kit::GetParam('sequence', _POST, _INT);
		$callingPage            = Kit::GetParam('callingpage', _POST, _STRING);

		$sequence--; //zero based

		include_once("lib/pages/region.class.php");

		$region = new region($db, $user);

		if (!$region->ReorderMedia($this->layoutid, $regionid, $mediaid, $sequence, $lkid))
		{
			//there was an ERROR
			trigger_error($region->errorMsg, E_USER_ERROR);
		}
		
		//Return a code here that reopens the options window
		if ($callingPage == "mediamanager")
		{
			$url = "index.php?p=mediamanager&layoutid=$this->layoutid&modify=true&regionid=$regionid&trigger=tRegionOptions";
		}
		else 
		{
			$url = "index.php?p=layout&layoutid=$this->layoutid&modify=true&regionid=$regionid&trigger=tRegionOptions";
		}
		
		$response->SetFormSubmitResponse(__('Order Changed'), true, $url);
		$response->Respond();
	}
	
	/**
	 * Return the Delete Form as HTML
	 * @return 
	 */
	public function DeleteRegionForm()
	{
		$db 		=& $this->db;
		$response	= new ResponseManager();
		$layoutid 	= Kit::GetParam('layoutid', _REQUEST, _INT, 0);
		$regionid 	= Kit::GetParam('regionid', _REQUEST, _STRING);
		
		// Translate messages
		$msgDelete		= __('Are you sure you want to remove this region?');
		$msgDelete2		= __('All media files will be unassigned and any context saved to the region itself (such as Text, Tickers) will be lost permanently.');
		$msgYes			= __('Yes');
		$msgNo			= __('No');
		
		//we can delete
		$form = <<<END
		<form class="XiboForm" method="post" action="index.php?p=layout&q=DeleteRegion">
			<input type="hidden" name="layoutid" value="$layoutid">
			<input type="hidden" name="regionid" value="$regionid">
			<p>$msgDelete $msgDelete2</p>
			<input type="submit" value="$msgYes">
			<input type="submit" value="$msgNo" onclick="$('#div_dialog').dialog('close');return false; ">
		</form>
END;
		
		$response->SetFormRequestResponse($form, __('Delete this region?'), '260px', '180px');
		$response->Respond();
	}
	
	function RenderDesigner() 
	{
		$db =& $this->db;
		
		// Assume we have the xml in memory already
		// Make a DOM from the XML
		$xml = new DOMDocument();
		$xml->loadXML($this->xml);
		
		// get the width and the height
		$width 	= $xml->documentElement->getAttribute('width');
		$height = $xml->documentElement->getAttribute('height');
		
		//do we have a background? Or a background color (or both)
		$bgImage = $xml->documentElement->getAttribute('background');
		$bgColor = $xml->documentElement->getAttribute('bgcolor');

		//Library location
		$libraryLocation = Config::GetSetting($db, "LIBRARY_LOCATION");
		
		//Fix up the background css
		if ($bgImage == "")
		{
                    $background_css = "$bgColor";
		}
		{
                    // Get the ID for the background image
                    $bgImageInfo = explode('.', $bgImage);
                    $bgImageId = $bgImageInfo[0];

                    $background_css = "url('index.php?p=module&q=GetImage&id=$bgImageId&width=$width&height=$height&dynamic&proportional=0') top center no-repeat; background-color:$bgColor";
		}
		
		$width 	= $width . "px";
		$height = $height . "px";
		
		// Get all the regions and draw them on
		$regionHtml 	= "";
		$regionNodeList = $xml->getElementsByTagName('region');

		//get the regions
		foreach ($regionNodeList as $region)
		{
			// get dimensions
                        $tipWidth       = $region->getAttribute('width');
                        $tipHeight      = $region->getAttribute('height');
                        $tipTop         = $region->getAttribute('top');
                        $tipLeft        = $region->getAttribute('left');

			$regionWidth 	= $region->getAttribute('width') . "px";
			$regionHeight 	= $region->getAttribute('height') . "px";
			$regionLeft	= $region->getAttribute('left') . "px";
			$regionTop	= $region->getAttribute('top') . "px";
			$regionid	= $region->getAttribute('id');

			$paddingTop	= $regionHeight / 2 - 16;
			$paddingTop	= $paddingTop . "px";

			$regionTransparency  = '<div class="regionTransparency" style="width:100%; height:100%;">';
			$regionTransparency .= '</div>';

			$doubleClickLink = "XiboFormRender($(this).attr('href'))";
			$regionHtml .= "<div id='region_$regionid' regionid='$regionid' layoutid='$this->layoutid' href='index.php?p=layout&layoutid=$this->layoutid&regionid=$regionid&q=RegionOptions' ondblclick=\"$doubleClickLink\"' class='region' style=\"position:absolute; width:$regionWidth; height:$regionHeight; top: $regionTop; left: $regionLeft;\">
					  $regionTransparency
                                           <div class='regionInfo'>
                                                $tipWidth x $tipHeight ($tipLeft,$tipTop)
                                           </div>
								<div class='preview'>
									<div class='previewContent'></div>
									<div class='previewNav'></div>
								</div>
							</div>";
		}
		
		// Translate messages
		$msgTimeLine			= __('Timeline');
		$msgOptions			= __('Options');
		$msgDelete			= __('Delete');
		$msgSetAsHome		= __('Set as Home');
		
		$msgAddRegion		= __('Add Region');
		$msgEditBg			= __('Edit Background');
		$msgProperties		= __('Properties');
		$msgSaveTemplate	= __('Save Template');
		
		//render the view pane
		//$layoutid = 4;
		$surface = <<<HTML
                <!--<div id="aspectRatioOption">
                    <input id="lockAspectRatio" type="checkbox" /><label for="lockAspectRatio">Lock Aspect Ratio?</label>
                </div>-->
		<div id="layout" layoutid="$this->layoutid" style="position:relative; width:$width; height:$height; border: 1px solid #000; background:$background_css;">
		$regionHtml
		</div>
		<div class="contextMenu" id="regionMenu">
			<ul>
                                <li id="btnTimeline">$msgTimeLine</li>
				<li id="options">$msgOptions</li>
				<li id="deleteRegion">$msgDelete</li>
				<li id="setAsHomepage">$msgSetAsHome</li>
			</ul>
		</div>
		<div class="contextMenu" id="layoutMenu">
			<ul>
				<li id="addRegion">$msgAddRegion</li>
				<li id="editBackground">$msgEditBg</li>
				<li id="layoutProperties">$msgProperties</li>
				<li id="templateSave">$msgSaveTemplate</li>
			</ul>
		</div>
HTML;
		echo $surface;
		return true;
	}
	
	/**
	 * Shows the Timeline for this region
	 * Also shows any Add/Edit options
	 * @return 
	 */
	function RegionOptions()
	{
		$db 	=& $this->db;
		$user 	=& $this->user;
                $helpManager    = new HelpManager($db, $user);
		
		$regionid = Kit::GetParam('regionid', _REQUEST, _STRING);
		
		//ajax request handler
		$arh = new ResponseManager();
		
		//Library location
		$libraryLocation = Config::GetSetting($db, "LIBRARY_LOCATION");
		
		//Buttons down the side - media across the top, absolutly positioned in the canvas div
		$mediaHtml = "";
		
		// Make a DOM from the XML
		$xml = new DOMDocument();
		$xml->loadXML($this->xml);
		
		//We need to set the duration per pixel...
		$maxMediaDuration 	= 1;
		$numMediaNodes 		= 0;
		
		$xpath = new DOMXPath($xml);
		$mediaNodes = $xpath->query("//region[@id='$regionid']/media");
		
		foreach ($mediaNodes as $mediaNode)
		{
			$numMediaNodes++;
			
			// Get the duration of this media node
			$mediaDuration = $mediaNode->getAttribute('duration');
			
			if ($mediaDuration == 0) 
			{
				$maxMediaDuration = $maxMediaDuration + 30; //default width for things that have no duration
			}
			else
			{
				$maxMediaDuration = $maxMediaDuration + $mediaDuration;
			}
		}
		
		//Work out how much room the grid can take up
		$availableWidth = 780;
		$left 			= 10;
		
		$availableWidth = $availableWidth - $left;
		
		$mediaBreakWidth = 9;
		$mediaBreakWidthVal = $mediaBreakWidth."px";
		
		//take the width of the media bars away from the available width
		$availableWidth = $availableWidth - ($mediaBreakWidth * $numMediaNodes);
		
		//Work out the duration per pixel
		$durationPerPixel = $availableWidth / $maxMediaDuration;
		
		if ($durationPerPixel < 2) 
		{
			//If the duration per pixel fall below 1, then we would like to increate the width of the window.
			$durationPerPixel = 2;
			$availableWidth = $durationPerPixel * $maxMediaDuration;
		}
		
		$availableWidthPx = $availableWidth . "px";
		
		//Set the starting point for left to 0;
		
		$tableWidthPx = $availableWidth . "px";
		$count = 0;
		
		// Go through the media nodes again
		$countNodes = $mediaNodes->length;
		
		//Find the RegionXml and then query each media node
		foreach ($mediaNodes as $mediaNode)
		{
			$count++;
			
			//Build up a button with position information
			$mediaName		= '';
			$mediaType		= '';
			$mediaid 		= $mediaNode->getAttribute('id');
			$lkid 			= $mediaNode->getAttribute('lkid');
			$mediaType 		= $mediaNode->getAttribute('type');
			$mediaFileName 	= $mediaNode->getAttribute('filename');
			$mediaDuration  = $mediaNode->getAttribute('duration');

                        // Artifically cap a duration so we dont break the timeline
                        if ($mediaDuration > 350)
                            $mediaDuration = 350;

			// Get media name
			require_once("modules/$mediaType.module.php");
			
			// Create the media object without any region and layout information
			$tmpModule 		= new $mediaType($db, $user, $mediaid);
			$mediaName 		= $tmpModule->GetName();
			
			Debug::LogEntry($db, 'audit', sprintf('Module name returned for MediaID: %s is %s', $mediaid, $mediaName), 'layout', 'RegionOptions');
						
			//Do we have a thumbnail for this media?
			if ($mediaType == "image" && file_exists($libraryLocation."tn_$mediaFileName"))
			{
				//make up a list of the media, with an image showing the media type
				$mediaList = "<img alt='$mediaFileName' src='index.php?p=module&q=GetImage&file=tn_$mediaFileName'>";
			}
			else
			{
				//make up a list of the media, with an image showing the media type
				$mediaList = "<img alt='$mediaType thumbnail' src='img/forms/$mediaType.png'>";
			}
			
			// Media duration check
			if ($mediaDuration == 0)
			{
				$mediaDuration = 30;
				$mediaDurationText = __("Media Controlled");
			}
			else
			{
				$mediaDurationText = $mediaDuration;
			}
			
			//Calculate the dimensions of this thumb
			$leftVal 		= $left . "px";
			$thumbWidth 	= $mediaDuration * $durationPerPixel;
			
			// If the thumb width falls below a certain value, increment it - knowing it will create a scroll bar
			if ($thumbWidth < 100) $thumbWidth = 100;
			
			$thumbWidthVal 	= $thumbWidth . "px";
			$top			= $this->GetTopForMediaType($mediaType, $count);
			$leftMediaBreakVal = ($left + $thumbWidth)."px";
			
			$editLink = <<<LINK
				<a class="XiboFormButton" style="color:#FFF" href="index.php?p=module&mod=$mediaType&q=Exec&method=EditForm&layoutid=$this->layoutid&regionid=$regionid&mediaid=$mediaid&lkid=$lkid" title="Click to edit this media">
					Edit
				</a><br />
LINK;
			
			$mediaBreakHtml = <<<END
			<div class="mediabreak" breakid="$count" style="position:absolute; top:20px; left:$leftMediaBreakVal; width:$mediaBreakWidthVal;"></div>
END;
			//If the count is == to the number of nodes then dont put this bar (its the last)
			if ($count == $countNodes) $mediaBreakHtml = "";
			
			
			$leftClass = "timebar_".$mediaType."_left";
			$rightClass = "timebar_".$mediaType."_right";
			
			//
			// Translate Messages
			//
			$msgDelete		= __('Delete');
			$msgType		= __('Type');
			$msgName		= __('Name');
			$msgDuration	= __('Duration');
			
			$mediaHtml .= <<<BUTTON
			<div class="timebar_ctl" style="position:absolute; top:$top; left:$leftVal; width:$thumbWidthVal;" mediaid="$mediaid" lkid="$lkid">
				<div class="timebar">
					<div class="$rightClass">
					<div class="$leftClass"></div>
						<br />
						$editLink
						<a class="XiboFormButton" style="color:#FFF" href="index.php?p=module&mod=$mediaType&q=Exec&method=DeleteForm&layoutid=$this->layoutid&regionid=$regionid&mediaid=$mediaid&lkid=$lkid" title="Click to delete this media">
							$msgDelete
						</a>
					</div>
				</div>
				<div class="tooltip_hidden" style="position:absolute; z-index:5;">
					<div class="thumbnail">$mediaList</div>
					<div class="info">
						<ul>
							<li>$msgType: $mediaType</li>
BUTTON;
			if ($mediaName != "")
			{
				$mediaHtml .= "<li>$msgName: $mediaName</li>";
			}
			$mediaHtml .= <<<BUTTON
							<li>$msgDuration: $mediaDurationText</li>
						</ul>
					</div>
				</div>
			</div>
			$mediaBreakHtml			
BUTTON;

			//Move the left along by the about of width this last one had
			$left = $left + $thumbWidth + $mediaBreakWidth;
		}
		
		//Defect what margin to put on the timebar_ctl
		if ($pos = stripos($_SERVER['HTTP_USER_AGENT'], "MSIE 6.0;"))
		{
			$timelineCtlMargin = "margin-left: -12%; margin-top: -9%;";
		}
		else
		{
			$timelineCtlMargin = "margin-left:5px;";
		}
		
		$timelineCtlMargin = "margin-left:5px;";
		
		// Get a list of the enabled modules and then create buttons for them
		if (!$enabledModules = new ModuleManager($db, $user)) trigger_error($enabledModules->message, E_USER_ERROR);
		
		$buttons = '';
		
		// Loop through the buttons we have and output store HTML for each one in $buttons.
		while ($modulesItem = $enabledModules->GetNextModule())
		{
			$mod 		= Kit::ValidateParam($modulesItem['Module'], _STRING);
			$caption 	= '+ ' . $mod;
			$mod		= strtolower($mod);
			$title 		= Kit::ValidateParam($modulesItem['Description'], _STRING);
			$img 		= Kit::ValidateParam($modulesItem['ImageUri'], _STRING);
			
			$uri		= 'index.php?p=module&q=Exec&mod=' . $mod . '&method=AddForm&layoutid=' . $this->layoutid . '&regionid=' . $regionid;
			
			$buttons .= <<<HTML
			<div class="regionicons">
				<a class="XiboFormButton" title="$title" href="$uri">
				<img class="dash_button moduleButtonImage" src="$img" />
				<span class="dash_text">$caption</span></a>
			</div>
HTML;
		}
		
		// Translate Messages
		$msgLibrary		= __('Library');
		
		$options = <<<END
		<div id="canvas">
			<div id="buttons">
				<div class="regionicons">
					<a class="XiboFormButton" href="index.php?p=content&q=LibraryAssignForm&layoutid=$this->layoutid&regionid=$regionid" title="Library">
					<img class="dash_button moduleButtonImage region_button" src="img/forms/library.gif"/>
					<span class="region_text">$msgLibrary</span></a>
				</div>
				$buttons
			</div>
			<div id="timeline" style="clear:left; float:none;">
				<div id="timeline_ctl" style="$timelineCtlMargin width:790px; position:relative; overflow-y:hidden; overflow-x:scroll;" layoutid="$this->layoutid" regionid="$regionid">
					<div style="width:$tableWidthPx; height:200px;">
						$mediaHtml
					</div>
				</div>
				<div id="tooltip_hover" style="position:absolute; top: 275px; left:0px; display:none"></div>
			</div>
		</div>
END;
		
		$arh->html 		= $options;
		$arh->callBack 		= 'region_options_callback';
		$arh->dialogTitle 	= __('Region Options');
		$arh->dialogSize 	= true;
		$arh->dialogWidth 	= '830px';
		$arh->dialogHeight 	= '450px';
                $arh->AddButton(__('Close'), 'XiboDialogClose()');
                $arh->AddButton(__('Help'), 'XiboHelpRender("' . $helpManager->Link('Layout', 'RegionOptions') . '")');
		
		$arh->Respond();
	}
	
	/**
	 * Gets the height for the given media type
	 * @return 
	 */
	private function GetTopForMediaType($type, $count)
	{
		$height = 0;
		
		if ($count % 2)
		{
			$height = 20;
		}
		else
		{
			$height = 70;
		}
		
		return $height."px";
	}
	
	/**
	 * Adds the media into the region provided
	 * @return 
	 */
	function AddFromLibrary()
	{
		$db 		=& $this->db;
		$user 		=& $this->user;
		$response 	= new ResponseManager();
		
		$regionid 	= Kit::GetParam('regionid', _REQUEST, _STRING);
		$mediaids 	= $_POST['mediaids'];
		
		foreach ($mediaids as $mediaid)
		{
			$mediaid = Kit::ValidateParam($mediaid, _INT);
			
			// Get the type from this media
			$SQL = sprintf("SELECT type FROM media WHERE mediaID = %d", $mediaid);
			
			if (!$result = $db->query($SQL))
			{
				trigger_error($db->error());
				$response->SetError(__('Error getting type from a media item.'));
				$response->keepOpen = false;
				return $response;
			}
			
			$row = $db->get_row($result);
			$mod = $row[0];
			
			require_once("modules/$mod.module.php");
			
			// Create the media object without any region and layout information
			$this->module = new $mod($db, $user, $mediaid);
			
			if ($this->module->SetRegionInformation($this->layoutid, $regionid))
			{
				$this->module->UpdateRegion();
			}
			else
			{
				$response->SetError(__('Cannot set region information.'));
				$response->keepOpen = true;
				return $response;
			}
		}
		
		// We want to load a new form
		$response->loadForm	= true;
		$response->loadFormUri= "index.php?p=layout&layoutid=$this->layoutid&regionid=$regionid&q=RegionOptions";;
		
		$response->Respond();
	}

	/**
	 * Properties Edit
	 * @return 
	 */
	function EditPropertiesHref() 
	{		
		//output the button
		echo "index.php?p=layout&q=displayForm&modify=true&layoutid=$this->layoutid";
	}

	function EditBackgroundHref() 
	{		
		//output the button
		echo "index.php?p=layout&q=BackgroundForm&modify=true&layoutid=$this->layoutid";
	}
	
	/**
	 * Called by AJAX
	 * @return 
	 */
	public function RegionPreview()
	{
		$db 		=& $this->db;
		$user 		=& $this->user;
		
		include_once("lib/pages/region.class.php");
		
		//ajax request handler
		$response	= new ResponseManager();
		
		//Expect
		$layoutid 	= Kit::GetParam('layoutid', _POST, _INT, 0);
		$regionid 	= Kit::GetParam('regionid', _POST, _STRING);
		
		$seqGiven 	= Kit::GetParam('seq', _POST, _INT, 0);
		$seq	 	= Kit::GetParam('seq', _POST, _INT, 0);
		$width	 	= Kit::GetParam('width', _POST, _INT, 0);
		$height	 	= Kit::GetParam('height', _POST, _INT, 0);
		
		// The sequence will not be zero based, so adjust it
		$seq--;
		
		// Get some region imformation
		$return		= "";
		$xml		= new DOMDocument("1.0");
		$region 	= new region($db, $user);
		
		if (!$xmlString = $region->GetLayoutXml($layoutid))
		{
                    trigger_error($region->errorMsg, E_USER_ERROR);
		}
		
		$xml->loadXML($xmlString);
		
		// This will be all the media nodes in the region provided
		$xpath 		= new DOMXPath($xml);
		$nodeList 	= $xpath->query("//region[@id='$regionid']/media");
		
		$return = "<input type='hidden' id='maxSeq' value='{$nodeList->length}' />";
		$return .= "<div class='seqInfo' style='position:absolute; right:15px; top:31px; color:#FFF; background-color:#000; z-index:50; padding: 5px;'>
                                <span style='font-family: Verdana;'>$seqGiven / {$nodeList->length}</span>
                            </div>";
		
		if ($nodeList->length == 0)
		{
			// No media to preview
			$return .= "<h1>" . __('Empty Region') . "</h1>";
			
			$response->html = $return;
			$response->Respond();
		}
		
		$node = $nodeList->item($seq);
			
		// We have our node.
		$type 			= (string) $node->getAttribute("type");
		$mediaDurationText 	= (string) $node->getAttribute("duration");
                $mediaid                = (string) $node->getAttribute("id");

		$return .= "
                   <div class='previewInfo' style='position:absolute; right:15px; top:61px; color:#FFF; background-color:#000; z-index:50; padding: 5px; font-family: Verdana;'>
                        <span style='font-family: Verdana;'>Type: $type <br />
                        Duration: $mediaDurationText (s)</span>
                    </div>";

		// Create a module to deal with this
                if (!file_exists('modules/' . $type . '.module.php'))
                {
                    $return .= 'Unknow module type';
                }

                require_once("modules/$type.module.php");

                $moduleObject = new $type($db, $user, $mediaid, $layoutid, $regionid);

                $return .= $moduleObject->Preview($width, $height);

		$response->html = $return;
		$response->Respond();
	}

    /**
     * Copy layout form
     */
    public function CopyForm()
    {
        $db             =& $this->db;
        $user		=& $this->user;
        $response	= new ResponseManager();

        $helpManager    = new HelpManager($db, $user);

        $layoutid       = Kit::GetParam('layoutid', _REQUEST, _INT);
        $oldLayout      = Kit::GetParam('oldlayout', _REQUEST, _STRING);

        $msgName        = __('New Name');
        $msgName2       = __('The name for the new layout');

        $form = <<<END
        <form id="LayoutCopyForm" class="XiboForm" method="post" action="index.php?p=layout&q=Copy">
            <input type="hidden" name="layoutid" value="$layoutid">
            <table>
                <tr>
                    <td><label for="layout" accesskey="n" title="$msgName2">$msgName<span class="required">*</span></label></td>
                    <td><input name="layout" class="required" type="text" id="layout" value="$oldLayout 2" tabindex="1" /></td>
                </tr>
            </table>
        </form>
END;

        $response->SetFormRequestResponse($form, __('Copy a Layout.'), '350px', '275px');
        $response->AddButton(__('Help'), 'XiboHelpRender("' . $helpManager->Link('Layout', 'Copy') . '")');
        $response->AddButton(__('Cancel'), 'XiboDialogClose()');
        $response->AddButton(__('Copy'), '$("#LayoutCopyForm").submit()');
        $response->Respond();
    }

    /**
     * Copys a layout
     */
    public function Copy()
    {
        $db             =& $this->db;
        $user		=& $this->user;
        $response	= new ResponseManager();

        $layoutid       = Kit::GetParam('layoutid', _POST, _INT);
        $layout         = Kit::GetParam('layout', _POST, _STRING);

        Kit::ClassLoader('Layout');

        $layoutObject = new Layout($db);

        if (!$layoutObject->Copy($layoutid, $layout, $user->userid))
            trigger_error($layoutObject->GetErrorMessage(), E_USER_ERROR);

        $response->SetFormSubmitResponse(__('Layout Copied'));
        $response->Respond();
    }
}
?>
