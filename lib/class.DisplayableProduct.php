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

class DisplayableProduct extends \EcProductMgr\Product
{

    private $_ddata = [
        'notpretty' => null,
        'hierpage' => null,
        'detailpage' => null,
        'detailtemplate' => null,
        'curpage' => null
    ];

    private $_cache_fields;

    private $_cache_cats;

    public function __construct(Product $in, array $params = null)
    {
        $mod = \cms_utils::get_module(\MOD_ECPRODUCTMGR);
        $this->from_array($in->to_array());
        if (! is_null($params))
        {
            $this->_ddata['curpage'] = \xt_param::get_int($params, 'curpage');
            $this->_ddata['detailpage'] = \xt_param::get_int($params, 'detailpage');
            $this->_ddata['notpretty'] = \xt_param::get_string($params, 'notpretty');
            $this->_ddata['hierpage'] = \xt_param::get_string($params, 'hierpage');
            if ($this->_ddata['curpage'] < 1)
                throw new \LogicException('An invalid curpage was passed to' . __METHOD__);
        }
    }

    public function __get($key)
    {
        $mod = \cms_utils::get_module(\MOD_ECPRODUCTMGR);

        switch ($key)
        {
            case 'curpage':
                $val = (int) $this->_ddata['curpage'];
                if ($val > 0)
                    return $val;
                return \cms_utils::get_current_pageid();
                break;

            case 'detailpage':
                if ($this->_ddata['detailpage'] > 0)
                    return $this->_ddata['detailpage'];
                return $this->curpage;
                break;

            case 'detailtemplate':
                return $this->_ddata['detailtemplate'];
                break;

            case 'price':
                return $mod->get_adjusted_price($this, parent::__get('price'));
                break;

            case 'file_location':
                return product_utils::get_product_upload_url($this->id);
                break;

            case 'hierarchy_id':
                return $this->first_hierarchy;
                break;

            case 'hierpage':
            case 'hierarchy_page':
                return $this->_ddata['hierpage'];
                break;

            case 'breadcrumb':
                if ($tmp = $this->hierarchy_id)
                {
                    return hierarchy_ops::get_breadcrumb('prod', $tmp, $this->hierarchy_page);
                }
                break;

            case 'detail_page':
                return $this->_ddata['detailpage'];
                break;

            case 'detail_url':
            case 'canonical':
                $pretty = (! $this->notpretty || strpos($this->notpretty, 'details') !== FALSE)
                            ? $this->pretty_detail_url() : null;
                $parms = ['productid' => $this->id];
                if ($this->detailtemplate)
                {
                    $parms['detailtemplate'] = $this->detailtemplate;
                }
                return $mod->create_url('p_', 'details', $this->detailpage, $parms, false, false, $pretty);
                break;

            case 'fields':
                // this is a merge of the fielddefs and products.
                if (! $this->_cache_fields)
                {
                    $fieldvals = $this->field_vals;
                    if (! count($fieldvals))
                    {
                        return;
                    }
                    $defs = product_utils::get_fielddefs();
                    foreach ($fieldvals as $fid => $value)
                    {
                        if (! isset($defs[$fid]))
                        {
                            continue;
                        }
                        $rec = $defs[$fid];
                        $rec->value = $value;
                        $this->_cache_fields[$rec->name] = $rec;
                    }
                }
                return $this->_cache_fields;
                break;

            case 'categories':
                // this is a merge of the full category info
                if (! $this->_cache_cats)
                {
                    $member_cats = parent::__get('categories');
                    if (! $member_cats)
                    {
                        return;
                    }
                    $allcats = product_utils::get_full_categories();
                    if (! $allcats)
                    {
                        return;
                    }
                    foreach ($member_cats as $catid)
                    {
                        if (! isset($allcats[$catid]))
                        {
                            continue;
                        }
                        $rec = $allcats[$catid];
                        $this->_cache_cats[] = $rec;
                    }
                }
                return $this->_cache_cats;
                break;

            case 'album':
                return $this->get_extra('album');
                break;

            case 'product_name':
                return parent::__get('name');
                break;

            default:
                return parent::__get($key);
        }
    }

    public function __isset($key)
    {
        return true;
    }

    // creates a product detail url.
    protected function pretty_detail_url()
    {
        $module = \cms_utils::get_module(\MOD_ECPRODUCTMGR);
        $db = \CmsApp::get_instance()->GetDB();

        $pretty_url = null;
        $prefix = $module->GetPreference('urlprefix');
        if ($this->url)
        {
            // if we have a url slug we just prepend the prefix (if we have any)
            if ($prefix && ! endswith($prefix, '/'))
            {
                $prefix .= '/';
            }
            $pretty_url = "{$prefix}{$this->url}";
        }
        else
        {
            // no urlslug, so build the url based on the prefix, the alias, and the hierarchy stuff.
            //$pretty_url = ($prefix) ? $prefix : $module->GetName();
            // TODO: Products module used modulye name which was convenient
            $pretty_url = ($prefix) ? $prefix : 'product';
            $usereturnid = ((int) $module->GetPreference('detailpage') < 1);
            $done = false;
            if ($module->GetPreference('usehierpathurls', 0) && ! empty($this->alias) && $this->hierarchy_id > 0)
            {
                $tmp = hierarchy_ops::get_hierarchy_info($this->hierarchy_id);
                if ($tmp)
                {
                    $tmp2 = explode(' | ', $tmp['long_name']);
                    for ($i = 0; $i < count($tmp2); $i ++)
                    {
                        $tmp2[$i] = munge_string_to_url($tmp2[$i]);
                    }
                    $path = implode('/', $tmp2);

                    $pretty_url .= '/details';
                    if ($usereturnid)
                    {
                        $pretty_url .= '/' . $this->detailpage;
                    }
                    if (! empty($path))
                    {
                        $pretty_url .= "/$path";
                    }
                    $pretty_url .= "/" . $this->alias;
                    $done = true;
                }
            }

            if (! $done)
            {
                // old pretty urls... "$prefix/id/$returnid/$something"
                $pretty_url .= '/' . $this->id;
                if ($usereturnid)
                {
                    $pretty_url .= '/' . $this->detailpage;
                }
                $alias = $this->alias;
                if (empty($alias))
                {
                    $alias = product_utils::make_alias($this->name);
                }
                $pretty_url .= "/$alias";
            }
        }

        return $pretty_url;
    }

} // class

?>
