{xtcss_add}
#album_container {
   padding: 2mm;
   max-height: 15cm;
   overflow-y: auto;
}
.album_row {
   border: 1px solid gray;
   padding: 5px;
   margin: 5px;
   float: left;
   width: 130px;
   height: 105px;
}
.album_thumb {
   width:  100px;
   height: 100px;
}
.album_delete {
   float: right;
   cursor: pointer;
   position: relative;
   margin: 5px;
}
{/xtcss_add}

<script type="text/javascript">
$(document).ready(function(){
  {if isset($compid)}
  $(document).on('click','#editoptions',function(){
    if( !confirm('{$mod->Lang('confirm_editoptions')}') ) return false;
    window.location = '{mod_action_link module=EcProductMgr action='admin_edit_attribs' prodid=$compid jsfriendly=1}';
    return false;
  });
  {/if}

  if( $.fancybox ) $('.fancybox').fancybox();
  $('span.cdautocomplete > input').autocomplete({
    minLength: 2,
    source: function( request, response ) {
      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '{mod_action_link module='CompanyDirectory' action='ajax_selcompany' jsfriendly=1 pagelimit=20}&showtemplate=false',
        data: {
          addid: 1,
	  term: request.term
        },
	success: function( data ) {
	  response(data);
        }
      });
    }
  });
});
</script>

{form_start compid=$compid}
{if !empty($compid)}
<h3>{$mod->Lang('edit_product')}&nbsp;({$compid})</h3>
{else}
<h3>{$mod->Lang('addproduct')}</h3>
{/if}

<div class="pageoverflow">
  <p class="pageinput">{$hidden}
    <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}"/>
    {if !empty($compid)}<input type="submit" id="editoptions" value="{$mod->Lang('edit_options')}"/>{/if}
    <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" formnovalidate/>
  </p>
</div>

{xt_start_tabs}
{xt_tabheader name='main' label=$mod->Lang('product_info')}
{if $customfieldscount gt 0}{xt_tabheader name='fields' label=$mod->Lang('fields')}{/if}
{xt_tabheader name='album' label=$mod->Lang('album')}
{xt_tabheader name='advanced' label=$mod->Lang('advanced')}

{xt_tabcontent_start name='main'}
<div class="c_full cf">
  <div class="grid_8">
    <div class="c_ful cfl">
      <div class="grid_3">{$mod->Lang('name')}:</div>
      <div class="grid_9">
        <input type="text" id="product_name" name="{$actionid}product_name" value="{$product_name}" size="40" maxlength="255"/>
      </div>
    </div>
    <div class="c_full cf">
      <div class="grid_3">{$mod->Lang('price')}:</div>
      <div class="grid_9">{$currency_symbol}
        <input type="text" id="product_price" name="{$actionid}price" value="{$price}" size="10" maxlength="12"/>
      </div>
    </div>
    <div class="c_full cf">
      <div class="grid_3">{$detailstext}:</div>
      <div class="grid_9">{$inputdetails}</div>
    </div>
  </div>

  <div class="grid_4">{* right column *}
    {* status field *}
    <div class="c_full cf">
      <div class="grid_3">{$statustext}:</div>
      <div class="grid_9">{$inputstatus}</div>
    </div>

    {* the hierarchy stuff *}
    {if count($hierarchy_items)}
      <div class="c_full cf">
        <p class="grid_3">{$mod->Lang('hierarchy_position')}:</p>
        <p class="grid_9">
	  <select name="{$actionid}hierarchy" id="hierarchy">
	    {html_options options=array_flip($hierarchy_items) selected=$hierarchy_pos}
	  </select>
	</p>
      </div>
    {/if}

    {* categories *}
    {if !empty($all_categories)}
      {if empty($sel_categories)}{$sel_categories=[]}{/if}
      <div class="c_full cf">
        <div class="grid_3">{$mod->Lang('categories')}:</div>
        <div class="grid_9">
	  <select name="{$actionid}categories[]" id="categories" multiple="multiple">
	    {html_options options=$all_categories selected=$sel_categories}
	  </select>
	</div>
      </div>
    {/if}
  </div>
</div>

{* display custom fields *}
{function do_custom_field}
    <div class="c_full cf">
      <p class="grid_3">{if isset($customfield->prompt)}{$customfield->prompt}{else}{$customfield->name}{/if}:</p>
      <div class="grid_9">
        {if isset($customfield->value)}
	  <p class="grid_12">
          {if $customfield->type == 'image' && !empty($customfield->url)}
              <a href="{$customfield->url}" class="fancybox">{$customfield->value}</a>
	  {elseif !empty($customfield->url) && $customfield->type == 'file'}
              <a href="{$customfield->url}" class="fancybox">{$customfield->value}</a>
	  {/if}
          {if isset($customfield->delete)}
              {$mod->Lang('delete')}&nbsp;{$customfield->delete}
          {/if}
	  </p>
        {/if}
        {if isset($customfield->hidden)}{$customfield->hidden}{/if}
        <p class="grid_12">
	  {if $customfield->type == 'filelink'}
 	    {xt_filepicker name=$customfield->nameattr value=$customfield->value}
	  {elseif $customfield->input_box}
  	    {$customfield->input_box}
	  {/if}
          {if isset($customfield->attribute)}<br/>{$customfield->attribute}{/if}
	</p>
      </div>
    </div>
{/function}

{if $customfieldscount gt 0}
{xt_tabcontent_start name='fields'}
  {foreach from=$customfields item=customfield}
    {if $customfield->type != 'dimensions' && $customfield->type != 'subscription'}
      {do_custom_field}
    {/if}
  {/foreach}
{/if}

{xt_tabcontent_start name='album'}
  {$album=$product->get_extra('album')}
  <script>
    function new_image( val ) {
       var cont = $('#album_skel').clone();
       cont.removeAttr('id').show();
       $('.album_hidden',cont).val(val);
       $('.album_thumb',cont).prop('src','{uploads_url}/'+val);
       $('.album_link',cont).prop('href','{uploads_url}/'+val);
       $('#album_container').append(cont);
    }
    $(function(){
       $(document).on('click','.album_delete',function(ev){
          $(this).closest('.album_row').remove();
       });
       $(document).on('cmsfp:change',"[name='addimage']",function(ev){
          new_image( $(this).val() );
          var picker = $("[name='addimage']");
          picker.filepicker('option','value','');
       });
       {if !empty($album)}
         {foreach $album as $image}
         new_image( '{$image}' );
         {/foreach}
       {/if}
    });
  </script>
  <div class="c_full cf">
     <label class="grid_3">{$mod->Lang('add_image')}:</label>
     {xt_filepicker name="addimage" type='image'}
  </div>
  <hr/>
  <div id="album_container">
     <div id="album_skel" class="album_row" style="display: none;">
        <a class="album_delete" title="{$mod->Lang('delete_image')}">{admin_icon icon='delete.gif'}</a>
	<input type="hidden" class="album_hidden" name="{$actionid}album[]"/>
	<a class="album_link" title="{$mod->Lang('view_image')}" target="_blank"><img class="album_thumb"/></a>
     </div>
  </div>


{xt_tabcontent_start name='advanced'}
<script type="text/javascript">
$(document).ready(function(){
  // variables
  var pid = {$compid|default:0};
  var manual_urlslug = pid;
  var finished_setup = 0;
  var ajax_timeout;
  var ajax_xhr;

  // setup cursor for ajax stuff
  $('form').ajaxStart(function() {
    $('*').css('cursor','progress');
  });

  $('form').ajaxStop(function() {
    $('*').css('cursor','auto');
  });

  function _ajax_getslug() {
     if( typeof ajax_xhr != 'undefined' && ajax_xhr != 0 ) ajax_xhr.abort();
     var $name = $('#product_name');
     var form = $name.closest('form');
     var pname = $name.val();
     ajax_xhr = $.ajax({
        url: '{mod_action_url action='admin_ajax_getslug' forajax=1}',
	method: 'POST',
	data: {
	  '{$actionid}pid':  pid,
	  '{$actionid}name': pname,
	  '{$actionid}hier': $('#hierarchy').val(),
	  '{$actionid}cats': $('#categories').val(),
	},
	success: function( res ) {
	   $('#urlslug').val(res);
	   ajax_xhr = 0;
	}
     })
  }

  function ajax_getslug() {
     if( !finished_setup ) return;
     if( manual_urlslug ) return;
     if( ajax_timeout != undefined ) clearTimeout(ajax_timeout);
     ajax_timeout = setTimeout(_ajax_getslug,500);
  }

  $('#urlslug').keyup(function(){
     manual_urlslug = 0;
     if( $(this).val() != '' ) manual_urlslug = 1;
  });

  $('#product_name').keyup(function(){
     // ajax call to get a url slug given a template
     ajax_getslug();
  });

  $('#hierarchy,#categories').change(function(){
     // ajax call to get a url slug given a template
     ajax_getslug();
  })

  finished_setup = 1;
})
</script>

  <div class="c_full cf">
     <label class="grid_3">{$mod->Lang('digital')}:</label>
     <div class="grid_9">
       <select class="grid__12" name="{$actionid}digital">{xt_yesno_options selected=$product->digital}</select>
       <br/>{$mod->Lang('info_digital')}
     </div>
  </div>

  <div class="c_full cf">
     <label class="grid_3">{$mod->Lang('is_service')}:</label>
     <div class="grid_9">
       <select class="grid__12" name="{$actionid}is_service">{xt_yesno_options selected=$product->is_service}</select>
       <br/>{$mod->Lang('info_isservice')}
     </div>
  </div>

  <div class="c_full cf">
    {$sku_req=$mod->GetPreference('skurequired')}
    <p class="grid_3">{if $sku_req}*{/if}{$mod->Lang('sku')}:</p>
    <p class="grid_9">
      <input type="text" name="{$actionid}sku" value="{$sku}" maxlength="25" {if $sku_req}required{/if}/>
      <br/>{$mod->Lang('info_sku')}
    </p>
  </div>

  <div class="c_full cf">
    <p class="grid_3">{$mod->Lang('weight')} ({$weightunits}):</p>
    <p class="grid_9">
      <input type="text" name="{$actionid}weight" value="{$weight}" size="8" maxlength="12"/>
    </p>
  </div>

  {if count($customfields)}
    {foreach $customfields as $customfield}
       {if $customfield->type == 'dimensions' || $customfield->type == 'subscription'}
          {do_custom_field $customfield}
       {/if}
    {/foreach}
  {/if}
  <hr/>
  <h4>{$mod->Lang('other')}</h4>

  {if !empty($feu_ownerlist)}
  <div class="c_full cf">
    <div class="grid_3">{$mod->Lang('owner')}:</div>
    <div class="grid_9">
      <select name="{$actionid}owner" style="width: 100%;">
         {html_options options=$feu_ownerlist selected=$product->owner}
      </select>
    </div>
  </div>
  {/if}
  <div class="c_full cf">
    <div class="grid_3">{$taxabletext}:</div>
    <div class="grid_9">
      <select style="width: 100%;" name="{$actionid}taxable">{cms_yesno selected=$product->taxable}</select>
    </div>
  </div>
  <div class="c_full cf">
    {$searchable=$product->get_extra('searchable',1)}
    <div class="grid_3">{$mod->Lang('searchable')}:</div>
    <div class="grid_9">
      <select style="width: 100%;" name="{$actionid}searchable">{cms_yesno selected=$searchable}</select>
      <br/>{$mod->Lang('info_searchable')}</p>
    </div>
  <div>
  <hr/>

  <h4>URL / SEO</h4>
  <div class="c_full cf">
    <p class="grid_3">{$mod->Lang('url_slug')}:</p>
    <p class="grid_9">
      <input type="text" id="urlslug" name="{$actionid}urlslug" value="{$urlslug}" size="80"/>
      <br/>{$mod->Lang('info_url_slug')}
    </p>
  </div>
  <div class="c_full cf">
    <p class="grid_3">{$mod->Lang('url_alias')}:</p>
    <p class="grid_9">{$inputalias}<br/>{$mod->Lang('info_url_alias')}</p>
  </div>
{xt_end_tabs}

<div class="pageoverflow">
  <p class="pageinput">
    <input type="submit" name="{$actionid}submit" value="{$mod->Lang('submit')}"/>
    <input type="submit" name="{$actionid}cancel" value="{$mod->Lang('cancel')}" formnovalidate/>
  </p>
</div>
{form_end}
