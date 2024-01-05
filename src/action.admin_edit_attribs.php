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
    return;
}
if (! isset($params['prodid']))
{
    return;
}

$this->SetCurrentTab('products');

$product = $this->GetProductStorage()->get_by_id(\xt_param::get_int($params, 'prodid'));
$skel = new ProductAttribute();
$attribs = [$skel];
if ($product->attribs)
{
    $attribs = $product->attribs;
}

if (isset($params['cancel']))
{
    $this->SetMessage($this->Lang('operation_cancelled'));
    $this->RedirectTotab($id);
}
if (isset($params['copyattribs']))
{
    if (! isset($params['copyfrom']) || $params['copyfrom'] < 1)
    {
        echo $this->ShowErrors($this->Lang('error_missingparam'));
    }
    else
    {
        $product2 = $this->GetProductStorage()->get_by_id(\xt_param::get_int($params, 'copyfrom'));
        $attribs = $product2->attribs;
        echo $this->ShowMessage($this->Lang('msg_optionscopied'));
    }
}
if (isset($params['submit']))
{
    try
    {
        products_attrib::delete_by_product($params['prodid']);
        if (isset($params['sku']) && isset($params['text']))
        {
            // we have some options.
            $attribs = [];
            $keys = [
                'text',
                'adjustment',
                'sku',
                'qoh',
                'notes'
            ];
            for ($i = 0; $i < count($params['sku']); $i ++)
            {
                $rec = ['product_id' => (int) $params['prodid']];
                foreach ($keys as $key)
                {
                    $rec[$key] = $params[$key][$i];
                }
                $attrib = new ProductAttribute();
                $attrib->from_array($rec);
                $attribs[] = $attrib;
            }
            $product->set_attributes($attribs);
            $this->GetProductStorage()->save($product);
        }

        $this->SetMessage($this->Lang('msg_options_saved'));
        $this->RedirectToTab($id);
    }
    catch (\CmsException $e)
    {
        echo $this->ShowErrors($e->GetMessage());
    }
}
else
{
    $tmp = products_attrib::load_by_product($params['prodid']);
    if (is_array($tmp) && count($tmp))
    {
        $attribs = $tmp;
    }
}

// get a list of products (except this one) with options
// todo: use productStorage method.
$query = 'SELECT DISTINCT P.id, P.product_name FROM ' . cms_db_prefix() . 'module_ec_prodmgr_attribs A
          LEFT JOIN ' . cms_db_prefix() . 'module_ec_prodmgr P
          ON A.product_id = P.id WHERE A.product_id != ?
          ORDER BY product_name ASC';
$tmp = $db->GetArray($query, [$product->id]);
if (is_array($tmp) && count($tmp))
{
    $t2 = array();
    foreach ($tmp as $rec)
    {
        $t2[$rec['id']] = $rec['product_name'];
    }
    $smarty->assign('products_with_attribs', $t2);
}

$smarty->assign('product', $product);
$smarty->assign('sku_is_required', $this->GetPreference('skurequired', 0));
$smarty->assign('formstart', $this->XTCreateFormStart($id, 'admin_edit_attribs', '',
                array('prodid' => $params['prodid'])));
$smarty->assign('formend', $this->CreateFormEnd());
$smarty->assign('skel_row', $skel);
$smarty->assign('attribs', $attribs);

echo $this->ProcessTemplate('admin_edit_attribs.tpl');

#
# EOF
#
?>
