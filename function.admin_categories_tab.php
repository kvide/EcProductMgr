<?php

# BEGIN_LICENSE
# -------------------------------------------------------------------------
# Module: EcProductMgr (c) 2023 by CMS Made Simple Foundation
#
# An addon module for CMS Made Simple to allow users to create, manage
# and display products in a variety of ways.
# -------------------------------------------------------------------------
# A fork of:
#
# Module: Products (c) 2008-2019 by Robert Campbell
# (calguy1000@cmsmadesimple.org)
#
# -------------------------------------------------------------------------
#
# CMSMS - CMS Made Simple is (c) 2006 - 2023 by CMS Made Simple Foundation
# CMSMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# Visit the CMSMS Homepage at: http://www.cmsmadesimple.org
#
# -------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple. You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin
# section that the site was built with CMS Made simple.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
# -------------------------------------------------------------------------
# END_LICENSE

namespace EcProductMgr;

if (! isset($gCms))
{
    exit();
}

if (! $this->CheckPermission('Modify Products'))
{
    exit();
}

# Put together a list of current categories...
$entryarray = array();

$query = "SELECT * FROM " . cms_db_prefix() . "module_ec_prodmgr_categories ORDER BY name";
$dbresult = $db->Execute($query);

$rowclass = 'row1';
$theme = \cms_utils::get_theme_object();
while ($dbresult && $row = $dbresult->FetchRow())
{
    $onerow = new \stdClass();
    $onerow->id = $row['id'];
    $onerow->name = $row['name'];
    $entryarray[] = $onerow;
}

$smarty->assign('items', $entryarray);
echo $this->ProcessTemplate('categorylist.tpl');

#
# EOF
#
?>
