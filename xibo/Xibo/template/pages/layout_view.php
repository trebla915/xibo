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

?>
<div id="form_container">
	<div id="form_header">
		<div id="form_header_left"></div>
		<div id="form_header_right"></div>
	</div>
	<div id="form_body" >
		<div class="SecondNav" >
			<ul>
			<li><a title="<?php echo $msgLayout; ?>" class="XiboFormButton" href="index.php?p=layout&q=displayForm" ><span><?php echo $msgLayout; ?></span></a></li>
			<li><a href="index.php?p=layout&" ><span>Save</span></a></li>
			<li><a href="index.php?p=dashboard&" ><span>Exit</span></a></li>
			</ul>
			<table>		
			<tr><td></td>
			<td>
				<div class="title">
					<h4>Layout Design - <?php echo $this->layout ?></h4>
					</div>
					<div class="formbody">
						<div class='buttons'>
						<a id="background_button" class="XiboFormButton" href="<?php echo $this->EditBackgroundHref(); ?>" title="Background"><div class="button_text">Background</div></a> 
						<a id="edit_button" class="XiboFormButton" href="<?php echo $this->EditPropertiesHref(); ?>" title="Layout Properties"><div class="button_text">Properties</div></a> 		
					</div></div></td>
			</tr>
			<tr>
				<td><?php $this->LayoutFilter();?></td>
				<td>
					
					<div class="formbody">
					<?php $this->RenderDesigner();?>
					</div>
				</td>
				<td><?php /*$this->RegionOptions();*/?></td>
					
			</table>
		</div>
	
			</div>
		</div>
		
	<div id="form_footer">
		<div id="form_footer_left">
		</div>
		<div id="form_footer_right">
		</div>
	</div>
</div>