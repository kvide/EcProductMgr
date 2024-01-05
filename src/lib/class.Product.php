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

class Product
{
    const STATUS_DISABLED = 'disabled';
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    private $_data = [
        'id' => null,
        'name' => null,
        'details' => null,
        'price' => null,
        'create_date' => null,
        'modified_date' => null,
        'taxable' => null,
        'digital' => false,
        'status' => self::STATUS_DRAFT,
        'weight' => null,
        'sku' => null,
        'alias' => null,
        'url' => null,
        'owner' => null
    ];

    private $_categories = [];
    private $_hierarchies = [];
    private $_field_vals = [];
    private $_attribs = [];
    private $_extra = [];

    public function __clone()
    {
        $old_id = (int) $this->_data['id'];
        $this->_data['id'] = null;
        foreach ($this->_attribs as $attrib)
        {
            $attrib->set_product_id(null);
            $attrib->set_sku(null);
        }
    }

    public function __get($key)
    {
        switch ($key)
        {
            case 'create_date':
            case 'id':
            case 'modified_date':
            case 'owner':
                return (int) $this->_data[$key];
                break;
            case 'name':
            case 'sku':
            case 'alias':
            case 'url':
            case 'details':
            case 'status':
                return trim($this->_data[$key]);
                break;
            case 'price':
            case 'weight':
                return (float) $this->_data[$key];
                break;
            case 'taxable':
            case 'digital':
                return (bool) $this->_data[$key];
                break;
            case 'categories':
                return $this->_categories;
                break;
            case 'hierarchies':
                return $this->_hierarchies;
                break;
            case 'hierarchy_id': // for compatibility.
            case 'first_hierarchy':
                if (count($this->_hierarchies))
                    return $this->_hierarchies[0];
                return;
                break;
            case 'field_vals':
                return $this->_field_vals;
                break;
            case 'attribs':
                return $this->_attribs;
                break;
            case 'file_path':
                if ($id = $this->id)
                {
                    return product_utils::get_product_upload_path($id);
                }
                break;

            case 'is_service':
                return $this->get_extra('is_service');
                break;
        }
    }

    public function __set($key, $val)
    {
        throw new \LogicException("$key is not a settable member of " . get_class($this));
    }

    public function __isset($key)
    {
        return true;
    }

    public function set_name($str)
    {
        $this->_data['name'] = trim($str);
    }

    public function set_status($str)
    {
        $str = trim($str);
        switch ($str)
        {
            case self::STATUS_DISABLED:
            case self::STATUS_DRAFT:
            case self::STATUS_PUBLISHED:
                $this->_data['status'] = $str;
                break;
            default:
                throw new \LogicException($str . ' is an invalid status value in ' . __METHOD__);
        }
    }

    public function set_details($str)
    {
        $this->_data['details'] = trim($str);
    }

    public function set_price($val)
    {
        $this->_data['price'] = (float) $val;
    }

    public function set_weight($val)
    {
        $this->_data['weight'] = (float) $val;
    }

    public function set_taxable($val)
    {
        $this->_data['taxable'] = (bool) $val;
    }

    public function set_digital($val)
    {
        $this->_data['digital'] = (bool) $val;
    }

    public function set_sku($str)
    {
        $this->_data['sku'] = trim($str);
    }

    public function set_alias($str)
    {
        $this->_data['alias'] = trim($str);
    }

    public function set_url($str)
    {
        $this->_data['url'] = trim($str);
    }

    public function set_owner($owner_id)
    {
        $this->_data['owner'] = (int) $owner_id;
    }

    public function set_categories(array $intlist = null)
    {
        if (is_null($intlist))
        {
            $intlist = [];
        }
        foreach ($intlist as $one)
        {
            if ((int) $one < 1)
            {
                throw new \LogicException('Invalid integer array passed to ' . __METHOD__);
            }
        }
        $this->_categories = $intlist;
    }

    public function set_hierarchies(array $intlist = null)
    {
        if (is_null($intlist))
        {
            $intlist = [];
        }
        if (count($intlist) == 1 && $intlist[0] == - 1)
        {
            $intlist = [];
        }
        foreach ($intlist as $one)
        {
            if ((int) $one < 1)
            {
                throw new \LogicException('Invalid integer array passed to ' . __METHOD__);
            }
        }
        $this->_hierarchies = $intlist;
    }

    public function set_attributes(array $list = null)
    {
        if (is_null($list))
        {
            $list = [];
        }
        foreach ($list as $one)
        {
            if (! $one instanceof ProductAttribute)
            {
                throw new \LogicException('Invalid array passed to ' . __METHOD__);
            }
        }
        $this->_attribs = $list;
    }

    public function set_field($fid, $val)
    {
        $fid = (int) $fid;
        if ($fid < 1)
        {
            throw new \LogicException('Invalid field id passed to ' . __METHOD__);
        }
        if (is_null($val))
        {
            if (isset($this->_field_vals[$fid]))
            {
                unset($this->_field_vals[$fid]);
            }
        }
        else
        {
            $this->_field_vals[$fid] = $val;
        }
    }

    public function get_field_value($fid)
    {
        $fid = (int) $fid;
        if ($fid < 1)
        {
            throw new \LogicException('Invalid field id passed to ' . __METHOD__);
        }
        if (isset($this->_field_vals[$fid]))
        {
            return $this->_field_vals[$fid];
        }
    }

    public function get_extra($key, $dflt = null)
    {
        if (array_key_exists($key, $this->_extra))
        {
            return $this->_extra[$key];
        }

        return $dflt;
    }

    public function set_service($flag = true)
    {
        $this->set_extra('is_service', (bool) $flag);
    }

    public function set_extra($key, $val)
    {
        $key = trim($key);
        if (is_null($val))
        {
            if (array_key_exists($key, $this->_extra))
            {
                unset($this->_extra[$key]);
            }
        }
        else
        {
            $this->_extra[$key] = $val;
        }
    }

    public function to_array()
    {
        $out = [];
        $out['id'] = $this->id;
        $out['name'] = $this->name;
        $out['details'] = $this->details;
        $out['status'] = $this->status;
        $out['sku'] = $this->sku;
        $out['alias'] = $this->alias;
        $out['url'] = $this->url;
        $out['owner'] = $this->owner;
        $out['create_date'] = $this->create_date;
        $out['modified_date'] = $this->modified_date;
        $out['taxable'] = $this->taxable;
        $out['digital'] = $this->digital;
        $out['price'] = $this->price;
        $out['weight'] = $this->weight;
        $out['categories'] = $this->_categories;
        $out['hierarchies'] = $this->_hierarchies;
        $out['field_vals'] = $this->_field_vals;
        $out['extra'] = $this->_extra;
        $out['attribs'] = [];
        foreach ($this->_attribs as $obj)
        {
            $out['attribs'][] = $obj->to_array();
        }

        return $out;
    }

    /**
     *
     * @ignore
     */
    public function from_array(array $input)
    {
        foreach ($input as $key => $val)
        {
            switch ($key)
            {
                case 'id':
                case 'owner':
                    $this->_data[$key] = (int) $val;
                    break;
                case 'product_name':
                    // an alias for the name.. the field in the database is product_name.
                    $this->_data['name'] = trim($val);
                    break;
                case 'name':
                case 'details':
                case 'status':
                case 'sku':
                case 'alias':
                case 'url':
                    $this->_data[$key] = trim($val);
                    break;
                case 'create_date':
                case 'modified_date':
                    if (! is_numeric($val))
                    {
                        $val = strtotime($val);
                    }
                    $this->_data[$key] = $val;
                    break;
                case 'taxable':
                case 'digital':
                    $this->_data[$key] = (bool) $val;
                    break;
                case 'price':
                case 'weight':
                    $this->_data[$key] = (float) $val;
                    break;
                case 'categories':
                    $this->set_categories($val);
                    break;
                case 'hierarchies':
                    $this->set_hierarchies($val);
                    break;
                case 'extra':
                    if (is_array($val))
                    {
                        $this->_extra = $val;
                    }
                    break;
                case 'field_vals':
                    if (is_array($val))
                    {
                        $this->_field_vals = $val;
                    }
                    break;
                case 'attribs':
                    $out = [];
                    foreach ($val as $row)
                    {
                        $obj = new ProductAttribute();
                        $obj->from_array($row);
                        $out[] = $obj;
                    }
                    $this->set_attributes($out);
                    break;
            }
        }
    }

} // class

?>
