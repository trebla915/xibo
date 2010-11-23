<?php
/*
 * Xibo - Digitial Signage - http://www.xibo.org.uk
 * Copyright (C) 2009 Daniel Garner
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

//FT Edit: Added progress bar
$progressBar = <<<END

		<div align="center">
			<img src = "img/progressbar/progressbarxibo1.jpg" usemap = "#progressbar"></img>
			<map name = "progressbar">
				<area shape = "rect" href = "index.php?p=content&wizard=1" coords = "24,158,199,301" title="Upload Media"></area>
				<area shape = "rect" href = "index.php?p=layout&wizard=1" coords = "200,158,394,301" title="Create Layout"></area>
				<area shape = "rect" href = "index.php?p=schedule&wizard=1" coords = "395,158,604,301" title="Schedules Displays"></area>
			</map>
		</div>		
END;

//FT Edit:  Added next/back button
$buttons = <<<END
	<div class="SecondNav">
			<div align="left">
				<a title="Go back a step"  href="index.php?p=layout&wizard=1">
					<span>Back</span>
				</a>
			</div>	
			
			<div align="right">		
				<a title="Finish"  href="index.php?p=dashboard">
					<span>Finish</span>
				</a>
			</div>
	</div>				
END;
?>
<div id="form_container">
	<div id="form_header">
		<div id="form_header_left">
		</div>
		<div id="form_header_right">
		</div>
	</div>
	
	<div id="form_body">
		<!-- FT Edit: If the user is going through the wizard, display the progress bar -->
		<?php
			if( $_GET['wizard'] > 0 ) 
			{
				echo $progressBar;
			}
		?>
		
		<table id="CalendarTable" width="100%" cellpadding=0 cellspacing=0>
			<tr>
				<td id="Nav">
					<div id="NavCalendar">
						
					</div>
					<div id="Displays">
						<?php echo $this->DisplayFilter(); ?>
					</div>
				</td>
				<td valign="top" style="vertical-align: top;">
					<div id="Calendar">
			
					</div>
				</td>
			</tr>
		</table>
		<!-- FT Edit: If the user is going through the wizard, display next/back buttons -->
			<?php
				if( $_GET['wizard'] > 0 ) 
				{
					echo $buttons;
				}
			?>		
	</div>
	<div id="form_footer">
		<div id="form_footer_left">
		</div>
		<div id="form_footer_right">
		</div>
	</div>
</div>	