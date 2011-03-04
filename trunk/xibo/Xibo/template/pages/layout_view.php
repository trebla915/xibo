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

$msgLayout		= __('Add Layout');
$msgFilter		= __('Filter');
$msgShowFilter	= __('Show Filter');

//FT Edit:  Added next/back button
$buttons = <<<END
	<div class="SecondNav">
			<div style= "float:left">
				<a title="Go back a step"  href="index.php?p=content&wizard=1">
					<span>Back</span>
				</a>
			</div>	
			
			<div style= "float:right">		
				<a title="Go to the next step"  href="index.php?p=schedule&wizard=1">
					<span>Next</span>
				</a>
			</div>
	</div>				
END;


if( $_GET['layoutid'] > 0 ) 
{
	$otherButtons = <<<END
	<div class="SecondNav">
			<div style= "float:left">
				<a title="Go back a step"  href="index.php?p=content&wizard=1">
					<span>Back</span>
				</a>
			</div>	
			
			<div style= "float:right">		
				<a title="Go to the next step"  href="index.php?p=schedule&wizard=1">
					<span>Next</span>
				</a>
			</div>
	</div>				
END;

	$layoutTitleAndButtons = <<<END
	<div class="title">
		<h4>Layout Design - $this->layout </h4>
	</div>
	<div class="formbody">
			<div class='buttons'>
			<a id="background_button" class="XiboFormButton" href="<?php echo $this->EditBackgroundHref(); ?>" title="Background"><div class="button_text">Background</div></a> 
			<a id="edit_button" class="XiboFormButton" href="<?php echo $this->EditPropertiesHref(); ?>" title="Layout Properties"><div class="button_text">Properties</div></a> 		
		</div>
	</div>		
END;


}

?>
<div id="form_container">
	<div id="form_header">
		<div id="form_header_left"></div>
		<div id="form_header_right"></div>
	</div>
	<div id="form_body">
		<div class="SecondNav">
			<ul>
			<li>
				<!--<img src="img/forms/info_icon.gif" alt="Hover for more info" title="Click this button to create a new layout."></img> -->
				<a title="<?php echo $msgLayout; ?>" class="XiboFormButton" href="index.php?p=layout&q=displayForm" ><span><?php echo "New Layout"; ?></span></a></li>
			<li>
			<!-- <img src="img/forms/info_icon.gif" alt="Hover for more info" title="Click this button to save the current layout."></img> -->
			<?php
					echo "<a href='index.php?p=layout&'><span>Save</span></a>";
			?>
			</li>
			<li>
			<!--  <img src="img/forms/info_icon.gif" alt="Hover for more info" title="Click to exit the lay out designer."></img>  -->
			<a href="index.php?p=dashboard&" ><span>Exit</span></a></li>
			</ul>
		</div>
		<div style="position:relative;left:  5%;width=200px">
			<table id="table" style="position:static; background-color:#e2f0ff; width: 60%">		
				<tr>
					<td></td>
					<td>
						<?php echo $layoutTitleAndButtons ?>
					</td>
				</tr>
				<tr>
					<td>	
						<?php $this->LayoutFilter();?>
					</td>
					<td>
						
						<div class="formbody">
						<!-- FT Edit:  If the user is not editing a layout don't display the designer-->
						
						<?php
							if( $_GET['layoutid'] > 0 ) 
							{
								echo "<b>Right click to start editing this layout.</b>";
								echo $this->RenderDesigner();
							}	
						?>
						</div>
					</td>
				</tr>		
			</table>
		</div>	
			 
		
			<!-- FT Edit: If the user is going through the wizard, display next/back buttons -->
			<?php
					echo $buttons;
			?>
	<div id="form_footer">
		<div id="form_footer_left">
		</div>
		<div id="form_footer_right">
		</div>
	</div>
</div>