<?php

// $Id: info.php 399 2006-12-24 07:50:44Z Ruebenwurzel $

/*

 Website Baker Project <http://www.websitebaker.org/>
 Copyright (C) 2004-2007, Ryan Djurovich

 Website Baker is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Website Baker is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Website Baker; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

change history

07/14/2009: rlj - added more permissions for page editing 
				  more checks for errouneous titles
				  fixed ordering problem
				  allow only submenu from pages that are already created
03/06/2009: Stefek - added style
03/04/2009: rlj - added variable definition to get rid of errors and warnings
03/16-2010: Argos - changed tool.php so that you can add separate menu title and page title

KNOWN BUGS
- can define pages with same name in list need to add functiont to check for that

*/

$module_directory = 'multiplepage';
$module_name = 'Multiple Page Creator';
$module_function = 'tool';
$module_version = '0.9.9';
$module_platform = '2.8.x';
$module_author = 'Robert Joseph';
$module_license = 'Free for any Website Baker CMS user.';
$module_description = 'This module allows you to create multiple pages all at one time';
$module_home		= '';
$module_guid		= '79B9C2C2-2101-498F-9693-3982C84C02EC';

?>