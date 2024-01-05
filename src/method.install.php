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

if (! isset($gCms))
{
    exit();
}

if (version_compare(phpversion(), '8.0') < 0)
{
    return "Minimum PHP version of 8.0 required";
}

$db = $this->GetDb();
$dict = NewDataDictionary($db);
$taboptarray = array(
    'mysql' => 'TYPE=InnoDB'
);
$flds = "
	id I KEY AUTO NOTNULL,
	product_name C(255) NOTNULL,
	details X,
    price F,
	create_date " . CMS_ADODB_DT . ",
	modified_date " . CMS_ADODB_DT . ",
    taxable I,
    status C(50),
    weight F,
    sku    C(25),
    alias  C(255),
    url    C(255),
    owner  I,
    digital I DEFAULT 0,
    extra X2
";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ec_prodmgr", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_idxurl', cms_db_prefix()
                                                        . 'module_ec_prodmgr', 'id,url,status');
$dict->ExecuteSQLArray($sqlarray);

$flds = "
    id I KEY AUTO,
    name C(255) NOTNULL,
    create_date " . CMS_ADODB_DT . ",
    modified_date " . CMS_ADODB_DT . "
";

$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ec_prodmgr_categories", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "
    product_id I KEY NOTNULL,
    category_id I KEY NOTNULL,
    create_date " . CMS_ADODB_DT . ",
    modified_date " . CMS_ADODB_DT;
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ec_prodmgr_product_categories", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "
    category_id I NOTNULL,
    field_type  C(20) NOTNULL,
    field_name  C(255) NOTNULL,
    field_prompt C(255) NOTNULL,
    field_value  X,
    field_order  I
";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_prodmgr_category_fields", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "
    id I KEY AUTO NOTNULL,
    name C(255) NOTNULL,
    prompt C(255) NOTNULL,
    type C(50) NOTNULL,
    max_length I,
    options X,
    create_date " . CMS_ADODB_DT . ",
    modified_date " . CMS_ADODB_DT . ",
    item_order I,
    public I
";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ec_prodmgr_fielddefs", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "
    product_id I KEY NOTNULL,
    fielddef_id I KEY NOTNULL,
    value X,
    create_date " . CMS_ADODB_DT . ",
    modified_date " . CMS_ADODB_DT . "
";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . "module_ec_prodmgr_fieldvals", $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

/*
 * not used any more, but here for reference temporarily.
 * $flds = "
 * attrib_set_id I KEY AUTO,
 * product_id I KEY,
 * attrib_set_name C(255) NOT NULL
 * ";
 * $sqlarray = $dict->CreateTableSQL(cms_db_prefix()."module_ec_prodmgr_attribsets",
 * $flds, $taboptarray);
 * $dict->ExecuteSQLArray($sqlarray);
 *
 *
 * $flds = "
 * attrib_id I KEY AUTO,
 * attrib_set_id I KEY,
 * attrib_text C(255) KEY,
 * attrib_adjustment C(50),
 * sku C(25)
 * ";
 * $sqlarray = $dict->CreateTableSQL(cms_db_prefix()."module_ec_prodmgr_attributes",
 * $flds, $taboptarray);
 * $dict->ExecuteSQLArray($sqlarray);
 */

$flds = "id I KEY AUTO NOTNULL,
         product_id I KEY NOTNULL,
         text C(255) KEY NOTNULL,
         adjustment C(50),
         sku  C(25),
         qoh I,
         notes X,
         iorder I NOTNULL";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . 'module_ec_prodmgr_attribs', $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "id I KEY AUTO NOTNULL,
         name C(255) NOTNULL,
         parent_id I NOTNULL,
         item_order I NOTNULL,
         hierarchy C(255),
         image C(255),
         long_name X,
         description X,
         extra1 C(255),
         extra2 C(255)";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . 'module_ec_prodmgr_hierarchy', $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$flds = "product_id I KEY NOTNULL,
         hierarchy_id I KEY NOTNULL";
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . 'module_ec_prodmgr_prodtohier', $flds, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

#
# Indexes
#
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_name',
    cms_db_prefix() . 'module_ec_prodmgr', 'product_name');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_category_name',
    cms_db_prefix() . 'module_ec_prodmgr_categories', 'name', ['UNIQUE']);
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_status'
    , cms_db_prefix() . 'module_ec_prodmgr', 'status');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_alias',
    cms_db_prefix() . 'module_ec_prodmgr', 'alias');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_price',
    cms_db_prefix() . 'module_ec_prodmgr', 'price');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_dates',
    cms_db_prefix() . 'module_ec_prodmgr', 'create_date,modified_date');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_prod_cat',
    cms_db_prefix() . 'module_ec_prodmgr_product_categories', 'product_id,category_id');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_cat_prod',
    cms_db_prefix() . 'module_ec_prodmgr_product_categories', 'category_id,product_id');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_cat_fld_name',
    cms_db_prefix() . 'module_ec_prodmgr_category_fields', 'category_id,field_name');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_flddef_name',
    cms_db_prefix() . 'module_ec_prodmgr_fielddefs', 'name', [' UNIQUE']);
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_flddef_type',
    cms_db_prefix() . 'module_ec_prodmgr_fielddefs', 'type');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_fldval_prod_def',
    cms_db_prefix() . 'module_ec_prodmgr_fielvals', 'product_id,fielddef_id');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_fldval_def_prod',
    cms_db_prefix() . 'module_ec_prodmgr_fielvals', 'fielddef_id,product_id');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_hier_name',
    cms_db_prefix() . 'module_ec_prodmgr_hierarchy', 'name');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_hier_name',
    cms_db_prefix() . 'module_ec_prodmgr_hierarchy', 'name');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_hier_parent',
    cms_db_prefix() . 'module_ec_prodmgr_hierarchy', 'parent_id');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_hier_longname',
    cms_db_prefix() . 'module_ec_prodmgr_hierarchy', 'long_name');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_hier_hierarchy',
    cms_db_prefix() . 'module_ec_prodmgr_hierarchy', 'hierarchy');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_owner',
    cms_db_prefix() . 'module_ec_prodmgr', 'owner');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . 'ec_prodmgr_product_cats',
    cms_db_prefix() . 'module_ec_prodmgr_product_categories', 'product_id,category_id', array('UNIQUE' => 1));
$dict->ExecuteSQLArray($sqlarray);

#
# Templates
#
$create_template_type = function ($type_name, $mod)
{
    try
    {
        $tpl_type = new \CmsLayoutTemplateType();
        $tpl_type->set_originator('EcProductMgr');
        $tpl_type->set_dflt_flag();
        $tpl_type->set_name($type_name);
        $tpl_type->set_lang_callback('EcProductMgr::tpl_type_lang_cb');
        $tpl_type->set_content_callback('EcProductMgr::tpl_type_reset_cb');
        $tpl_type->reset_content_to_factory();
        $tpl_type->save();
    }
    catch (\CmsException $e)
    {
        \xt_utils::log_exception($e);
        audit('', 'EcProductMgr', 'Install error: ' . $e->GetMessage());
    }

    $tpl_type = \CmsLayoutTemplateType::load('EcProductMgr::' . $type_name);

    return $tpl_type;
}; // function

$create_template_of_type = function ($type_ob, $dflt = true)
{
    $name = 'EcProductMgr sample ' . $type_ob->get_name();
    $ob = new \CmsLayoutTemplate();
    $ob->set_type($type_ob);
    $ob->set_content($type_ob->get_dflt_contents());
    $ob->set_owner(get_userid());
    $ob->set_type_dflt($dflt);
    $new_name = $ob->generate_unique_name($name);
    $ob->set_name($new_name);
    $ob->save();
}; // function

$listview_type = $create_template_type('List View', $this);
$create_template_of_type($listview_type);
$detailview_type = $create_template_type('Detail View', $this);
$create_template_of_type($detailview_type);
$hierview_type = $create_template_type('Hierarchy View', $this);
$create_template_of_type($hierview_type);
$categoryview_type = $create_template_type('Category View', $this);
$create_template_of_type($categoryview_type);
$search_type = $create_template_type('Search', $this);
$create_template_of_type($search_type);

# Set Permission
$this->CreatePermission('Modify Products', 'Modify Products');

# Preferences
$this->SetPreference('products_currencysymbol', '$');
$this->SetPreference('products_weightunits', 'kg');
$this->SetPreference('allowed_imagetypes', 'jpg,jpeg,gif,png');
$this->SetPreference('allowed_filetypes', 'pdf,doc,txt,jpg,jpeg,gif,png');
$this->SetPreference('deleteproductfiles', 1);
$this->SetPreference('upload_dir', '_products');
$this->SetPreference('slugtemplate', '{if empty($hierarchy3)}{$name}{else}{$hierarchy3}/{$name}{/if}');

# Events
$this->AddEventHandler('EcommerceExt', 'OrderUpdated', FALSE);

#
# EOF
#
?>
