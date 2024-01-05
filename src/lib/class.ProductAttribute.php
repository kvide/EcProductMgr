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

class ProductAttribute
{

    private $_data = [
        'id' => null,
        'product_id' => null,
        'text' => null,
        'adjustment' => null,
        'sku' => null,
        'qoh' => null,
        'notes' => null,
        'iorder' => null
    ];

    public function __get($key)
    {
        switch ($key)
        {
            case 'id':
            case 'product_id':
            case 'qoh':
            case 'iorder':
                return (int) $this->_data[$key];
                break;
            case 'text':
            case 'adjustment':
            case 'sku':
            case 'notes':
                return trim($this->_data[$key]);
                break;
            default:
                throw new \LogicException("$key is not a gettable member of " . __CLASS__);
        }
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
                case 'product_id':
                case 'qoh':
                case 'iorder':
                    $this->_data[$key] = (int) $val;
                    break;
                case 'text':
                case 'adjustment':
                case 'sku':
                case 'notes':
                    $this->_data[$key] = trim($val);
                    break;
            }
        }
    }

    public function to_array()
    {
        return $this->_data;
    }

    public function set_product_id($val)
    {
        if (! is_null($val))
        {
            $val = (int) $val;
            if ($val < 1)
            {
                throw new \LogicException('Invalid product_id provided to ' . __METHOD__);
            }
        }
        $this->_data['product_id'] = $val;
    }

    public function set_text($val)
    {
        $val = trim($val);
        if (! $val)
        {
            throw new \LogicException('Invalid value provided to ' . __METHOD__);
        }
        $this->_data['text'] = $val;
    }

    public function set_adjustment($val)
    {
        $val = trim($val);
        if (! $val)
        {
            throw new \LogicException('Invalid value provided to ' . __METHOD__);
        }
        $this->_data['adjustment'] = $val;
    }

    public function set_sku($val)
    {
        if (! is_null($val))
        {
            $val = trim($val);
        }
        $this->_data['sku'] = $val;
    }

    public function set_qoh($val)
    {
        $val = (int) $val;
        $this->_data['qoh'] = $val;
    }

    public function set_notes($val)
    {
        $val = trim($val);
        $this->_data['notes'] = $val;
    }

} // Class

#
# EOF
#
?>
