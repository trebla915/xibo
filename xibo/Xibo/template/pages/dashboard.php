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
?>
<div id="form_container">
	<div id="form_header">
		<div id="form_header_left">
		</div>
		<div id="form_header_right">
		</div>
	</div>

	<div id="form_body">
			<div id="dashbuttons">
			<!-- FT Edit: Changed what items appear on the dashboard.  Used static links instead of dynamically generated
			links so no db changes need to be made.  May revert back to dynamically generated later. -->
				<?php
				
				
					// Put a menu here
					/*if (!$menu = new MenuManager($db, $user, 'Dashboard')) trigger_error($menu->message, E_USER_ERROR);

					while ($menuItem = $menu->GetNextMenuItem())
					{
						$uri 	= Kit::ValidateParam($menuItem['name'], _WORD);
						$args 	= Kit::ValidateParam($menuItem['Args'], _STRING);
						$class 	= Kit::ValidateParam($menuItem['Class'], _WORD);
						$title 	= Kit::ValidateParam($menuItem['Text'], _STRING);
						$title 	= __($title);
						$img 	= Kit::ValidateParam($menuItem['Img'], _STRING);

						$href = 'index.php?p=' . $uri . '&' . $args;

						// Override the HREF for the Manual Button
						if ($uri == 'manual')
							$href = $args;

						$out = <<<END
							<div class="dashicons">
								<a id="$class" alt="$title" href="$href">
								<img class="dash_button" src="$img"/>
								<span class="dash_text">$title</span></a>
							</div>
END;
						echo $out;
					}*/
				?>
				<div class="dashicons">
					<a id="content_button" alt="Get Started Here" href="index.php?p=content&wizard=1">
					<img class="dash_button" src="img/dashboard/wizard.png?">
					<span class="dash_text">Get Started Here</span></a>
				</div>
				<div class="dashicons">
					<a id="content_button" alt="Upload Media" href="index.php?p=content&">
					<img class="dash_button" src="img/dashboard/content.png">
					<span class="dash_text">Upload Media</span></a>
				</div>
				<div class="dashicons">
					<a id="playlist_button" alt="Create Layout" href="index.php?p=layout&">
					<img class="dash_button" src="img/dashboard/presentations.png">
					<span class="dash_text">Create Layout</span></a>
				</div>
				<div class="dashicons">
					<a id="schedule_button" alt="Publish to Displays" href="index.php?p=schedule&">
					<img class="dash_button" src="img/dashboard/scheduleview.png">
					<span class="dash_text">Schedule Displays</span></a>
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