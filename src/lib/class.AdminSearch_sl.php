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

/* note, class cannot be named AdminSearch_slave as the filename then conflicts with
 *   that in the AdminSearch module, resulting in an error */
final class AdminSearch_sl extends \AdminSearch_slave
{

    public function get_name()
    {
        $mod = \cms_utils::get_module(\MOD_ECPRODUCTMGR);
        return $mod->Lang('adminsearch_lbl');
    }

    public function get_description()
    {
        $mod = \cms_utils::get_module(\MOD_ECPRODUCTMGR);
        return $mod->Lang('adminsearch_desc');
    }

    public function check_permission()
    {
        $userid = get_userid();
        return check_permission($userid, 'Modify Products');
    }

    public function get_matches()
    {
        $orec = [
            'title' => null,
            'description' => null,
            'edit_url' => null,
            'text' => null
        ];
        $mod = \cms_utils::get_module(\MOD_ECPRODUCTMGR);
        $db = $mod->GetDb();
        $term = '%' . $this->get_text() . '%';

        $fielddefs = product_utils::get_fielddefs(true);
        $cols = ['P.*'];
        $joins = $where = $parms = [];
        $where[] = 'P.product_name LIKE ?';
        $parms[] = $term;
        $where[] = 'P.details LIKE ?';
        $parms[] = $term;

        if (count($fielddefs))
        {
            $idx = 1;
            foreach ($fielddefs as $one)
            {
                if (! in_array($one->type, ['textbox', 'textarea', 'imagetext', 'file']))
                {
                    continue;
                }
                $tmp = "FV" . $idx;
                $fdid = $one->id;
                $cols[] = "$tmp.value";
                $joins[] = 'LEFT JOIN ' . ProductStorage::product_fieldvals_table_name()
                                . " $tmp ON P.id = $tmp.product_id AND $tmp.fielddef_id = $fdid";
                $where[] = "$tmp.value LIKE ?";
                $parms[] = $term;
                $idx ++;
            }
        }

        // build the query
        $sql = 'SELECT ' . implode(',', $cols) . ' FROM ' . ProductStorage::product_table_name() . ' P';
        if (count($joins))
        {
            $sql .= ' ' . implode(' ', $joins);
        }
        if (count($where))
        {
            $sql .= ' WHERE ' . implode(' OR ', $where);
        }
        $sql .= 'ORDER BY P.modified_date DESC';
        $dbr = $db->GetArray($sql, $parms);
        if (! is_array($dbr) || ! count($dbr))
        {
            return;
        }

        $out = [];
        foreach ($dbr as $row)
        {
            foreach ($row as $key => $val)
            {
                if (($pos = strpos($val, $this->get_text())))
                {
                    // use the first occurrance
                    $start = max(0, $pos - 50);
                    $end = min(strlen($val), $pos + 50);
                    $text = substr($val, $start, $end - $start);
                    $text = cms_htmlentities($text);
                    $text = str_replace($this->get_text(), '<span class="search_oneresult">'
                                        . $this->get_text() . '</span>', $text);
                    $text = str_replace("\r", '', $text);
                    $text = str_replace("\n", '', $text);
                    break;
                }
            }
            $rec = $orec;
            $rec['title'] = $row['product_name'];
            $rec['description'] = \AdminSearch_tools::summarize($row['details']);
            $rec['edit_url'] = $mod->create_url('m1_', 'editproduct', '', [
                'compid' => $row['id']
            ]);
            $rec['text'] = $text;
            $out[] = $rec;
        }

        return $out;
    }

} // end of class

?>
