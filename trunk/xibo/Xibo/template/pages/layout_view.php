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
				<a title="Go back a step"  href="index.php?p=content&wizard=1">
					<span>Back</span>
				</a>
			</div>	
			
			<div align="right">		
				<a title="Go to the next step"  href="index.php?p=schedule&wizard=1">
					<span>Next</span>
				</a>
			</div>
	</div>				
END;

?>
<div id="form_container">
	<div id="form_header">
		<div id="form_header_left"></div>
		<div id="form_header_right"></div>
	</div>
	<div id="form_body" >
		<!-- FT Edit: If the user is going through the wizard, display the progress bar -->
		<?php
			if( $_GET['wizard'] > 0 ) 
			{
				echo $progressBar;
			}
		?>
		
		<div class="SecondNav" >
			<ul>
			<li><a title="<?php echo $msgLayout; ?>" class="XiboFormButton" href="index.php?p=layout&q=displayForm" ><span><?php echo $msgLayout; ?></span></a></li>
			<li><a href="index.php?p=layout&" ><span>Save</span></a></li>
			<li><a href="index.php?p=dashboard&" ><span>Exit</span></a></li>
			</ul>
		</div>
			<table>		
				<tr>
					<td></td>
					<td>
						<div class="title">
							<h4>Layout Design - <?php echo $this->layout ?></h4>
						</div>
						<div class="formbody">
							<div class='buttons'>
								<a id="background_button" class="XiboFormButton" href="<?php //echo $this->EditBackgroundHref(); ?>" title="Background"><div class="button_text">Background</div></a> 
								<a id="edit_button" class="XiboFormButton" href="<?php //echo $this->EditPropertiesHref(); ?>" title="Layout Properties"><div class="button_text">Properties</div></a> 		
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td>	
						<?php $this->LayoutFilter();?>
							
					</td>
					<td>
						
						<div class="formbody">
						<!-- FT Edit:  -->
						<?php
							if( $_GET['layoutid'] > 0 ) 
							{
								echo $this->RenderDesigner();
							}	
						?>
						</div>
					</td>
					<td></td>
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
		</div>
		
	<div id="form_footer">
		<div id="form_footer_left">
		</div>
		<div id="form_footer_right">
		</div>
</div>