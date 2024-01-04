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

$thetemplate = product_utils::get_template($params, 'categorylisttemplate', 'EcProductMgr::Category View');
$sortby = 'A.name';
$sortorder = 'asc';
$category_id = \xt_param::get_int($params, 'categoryid');
$category = \xt_param::get_string($params, 'category');
$categoryfield = \xt_param::get_string($params, 'categoryfield');
$resultpage = \xt_param::get_string($params, 'resultpage');

$resultpage = $this->GetPreference('resultpage', '');
if (! empty($resultpage))
{
    $manager = $gCms->GetHierarchyManager();
    $node = $manager->sureGetNodeByAlias($resultpage);
    if (isset($node))
    {
        $content = $node->GetContent();
        if (isset($content))
        {
            $resultpage = $content->Id();
        }
    }
    else
    {
        $node = $manager->sureGetNodeById($resultpage);
        if (! isset($node))
        {
            $resultpage = '';
        }
    }
}

$tmp = strtolower(\xt_param::get_string($params, 'sortby'));
switch ($tmp)
{
    case 'id':
    case 'name':
        $sortby = 'A.' . $tmp;
        break;
}

$tmp = strtolower(\xt_param::get_string($params, 'sortorder'));
switch ($tmp)
{
    case 'asc':
    case 'desc':
        $sortorder = $tmp;
        break;
}

$query = "SELECT A.id, A.name, count(B.product_id) AS count FROM " . cms_db_prefix() . "module_ec_prodmgr_categories A"
            . " LEFT OUTER JOIN " . cms_db_prefix() . "module_ec_prodmgr_product_categories B ON A.id = B.category_id";
$joins = array();
$jparms = array();
$where = array();
$qparms = array();
if ($category_id)
{
    $where[] .= "A.id = ?";
    $qparms[] = $category_id;
}
elseif ($category)
{
    $q2 = 'SELECT id FROM ' . cms_db_prefix() . 'module_ec_prodmgr_categories WHERE name IN (';
    $tmp = explode(',', $category);
    for ($i = 0; $i < count($tmp); $i ++)
    {
        $tmp[$i] = '"' . $tmp[$i] . '"';
    }
    $q2 .= implode(',', $tmp) . ')';
    $ids = $db->GetCol($q2);
    if (is_array($ids) && count($ids))
    {
        $where[] = 'A.id IN (' . implode(',', $ids) . ')';
    }
}
elseif ($categoryfield)
{
    $exprs = \xt_array::smart_explode($categoryfield);
    if (is_array($exprs) && count($exprs))
    {
        for ($i = 0; $i < count($exprs); $i ++)
        {
            list ($fldname, $fldval) = explode(':', $exprs[$i], 2);
            if ($fldname != '' && $fldval != '')
            {
                $joins[] = cms_db_prefix() . "module_ec_prodmgr_category_fields CF{$i} ON A.id = CF{$i}.category_id AND CF{$i}.field_name = ?";
                $jparms[] = $fldname;
                $where[] = "CF{$i}.field_value = ?";
                $qparms[] = $fldval;
            }
        }
    }
}

// final query assembly.
if (count($joins))
{
    $query .= ' LEFT JOIN ' . implode(' LEFT JOIN ', $joins);
}
if (count($where))
{
    $query .= ' WHERE ' . implode(' AND ', $where);
}
$qparms = array_merge($jparms, $qparms);
$query .= " GROUP BY A.id ORDER BY $sortby $sortorder";
$categories = $db->GetArray($query, $qparms);
if (! $categories)
{
    return;
}

$tmp = \xt_array::extract_field($categories, 'id');
$query2 = 'SELECT * FROM ' . cms_db_prefix() . 'module_ec_prodmgr_category_fields
            WHERE category_id IN (' . implode(',', $tmp) . ') ORDER BY category_id ASC, field_order ASC';
$tmp2 = $db->GetArray($query2);
$results = array();
for ($i = 0; $i < count($categories); $i ++)
{
    $row = &$categories[$i];
    if ((! isset($params['showall']) || $params['showall'] < 1) && $row['count'] <= 0)
    {
        continue;
    }

    $obj = new \StdClass();
    foreach ($row as $k => $v)
    {
        $obj->$k = $v;
    }

    // extract all of the rows that have this category id
    $tmpa = array();
    for ($j = 0; $j < count($tmp2); $j ++)
    {
        if ($tmp2[$j]['category_id'] < $row['id'])
        {
            continue;
        }
        if ($tmp2[$j]['category_id'] > $row['id'])
        {
            break;
        }

        $tmpa[] = $tmp2[$j];
    }
    if (is_array($tmpa) && count($tmpa) > 0)
    {
        $obj->fields = \xt_array::to_hash($tmpa, 'field_name');
    }

    $params['categoryid'] = $obj->id;
    if (! $category_id)
    {
        $obj->detail_url = $this->create_url($id, 'categorylist', ($resultpage != '')
                            ? $resultpage : $returnid, $params);
    }
    $params['categoryname'] = $obj->name;
    $obj->summary_url = $this->create_url($id, 'default', ($resultpage != '')
                            ? $resultpage : $returnid, $params);
    $results[] = $obj;
}

$tpl = $this->CreateSmartyTemplate($thetemplate);
$tpl->assign('categorylist', $results);

$tpl->display();

// EOF
?>
