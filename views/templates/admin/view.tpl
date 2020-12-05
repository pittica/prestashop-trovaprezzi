{**
 * 2020 Pittica S.r.l.s.
 *
 * @author    Lucio Benini <info@pittica.com>
 * @copyright 2020 Pittica S.r.l.s.
 * @license   http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 *}

{extends file="helpers/view/view.tpl"}
{block name="override_tpl"}
	{if $image}
		<div class="alert alert-warning">{l s='Image URL is missing.' mod='pitticatrovaprezzi'}</div>
	{/if}
	{if $code}
		<div class="alert alert-warning">{l s='Part Number or EAN13 is missing.' mod='pitticatrovaprezzi'}</div>
	{/if}
	{if $edit_link}
		<div>
			<a href="{$edit_link}" target="_new">{l s='Edit product' mod='pitticatrovaprezzi'}</a>
		</div>
	{/if}
	{if $back_link}
		<div>
			<a href="{$back_link}">{l s='Back to list' mod='pitticatrovaprezzi'}</a>
		</div>
	{/if}
{/block}
