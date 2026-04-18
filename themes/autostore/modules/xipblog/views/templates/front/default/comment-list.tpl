{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{if $xipblog_commets}
<div class="comments_area" id="comments">
    <h2 class="comments_title">
        {l s='All comments' d='Modules.Xipblog.Shop'}
    </h2>
    <ul class="comment_list">
		{foreach from=$xipblog_commets item=xipblog_commet}
        <li class="comment" id="comment_{$xipblog_commet.id_xip_comments}">
            <article class="comment_body">
				<div class="comment_author vcard">
				    <img alt="" class="xipblog_img avatar avatar-70 photo" height="70" src="https://2.gravatar.com/avatar/597a1e6b0dfdf57f53ef8fb80fa190d7?s=70&d=mm&r=g" width="70">
				</div>
				<div class="comment_content">
					<div class="comment_meta">
					    <div class="comment_meta_author">
					    	<b class="fn">{$xipblog_commet.name}</b>
					    </div>
					    <div class="comment_meta_date">
					    	<time datetime="2016-03-07T04:33:23+00:00">
					    	    {$xipblog_commet.created|date_format:"%e %B, %Y"}
					    	</time>
					    </div>
					    <div class="reply">
					        <a aria-label="Reply to raihan@sntbd.com" class="comment-reply-link" href="#" onclick='return addComment.moveForm( "div-comment-3", "3", "respond", "38" )' rel="nofollow">
					            {l s='Reply' d='Modules.Xipblog.Shop'}
					        </a>
					    </div>
					</div>
					<div class="comment_content_bottom">
						<p>
							{$xipblog_commet.content}
						</p>
					</div>
				</div>
            </article>
        </li>
		{/foreach}
    </ul>
</div>
{/if}