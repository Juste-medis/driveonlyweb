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
{extends file='page.tpl'}

{block name='head_seo'}
  <title>{block name='head_seo_title'}{$meta_title}{/block}</title>
  {block name='hook_after_title_tag'}
    {hook h='displayAfterTitleTag'}
  {/block}
  <meta name="description" content="{block name='head_seo_description'}{$meta_description}{/block}">
  <meta name="keywords" content="{block name='head_seo_keywords'}{$meta_keywords}{/block}">
  {if $page.meta.robots !== 'index'}
    <meta name="robots" content="{$page.meta.robots}">
  {/if}
  {if $page.canonical}
    <link rel="canonical" href="{$page.canonical}">
  {/if}
  {block name='head_hreflang'}
    {foreach from=$urls.alternative_langs item=pageUrl key=code}
      <link rel="alternate" href="{$pageUrl}" hreflang="{$code}">
    {/foreach}
  {/block}

  {block name='head_open_graph'}
    <meta property="og:title" content="{$meta_title}" />
    <meta property="og:description" content="{$meta_description}" />
    <meta property="og:url" content="{$urls.current_url}" />
    <meta property="og:site_name" content="{$shop.name}" />
    {if !isset($product) && $page.page_name != 'product'}<meta property="og:type" content="website" />{/if}
  {/block}  
{/block}

{block name="page_content_container"}
	<section id="content" class="page-content ">
	{if isset($xipblogpost) && !empty($xipblogpost)}
	<div class="kr_blog_post_area post-category">
		<div class="kr_blog_post_inner row blog_style_{$xipblogsettings.blog_style} column_{$xipblogsettings.blog_no_of_col}">
			{foreach from=$xipblogpost item=xpblgpst}
				<article id="blog_post" class="blog_post blog_post_{$xpblgpst.post_format} clearfix">
					<div class="blog_post_content">
						<div class="blog_post_content_top">
							{if $xpblgpst.post_format != 'video' && $xpblgpst.post_format != 'audio' && $xpblgpst.post_format != 'gallery'}
							<a class="post_thumbnail" href="{$xpblgpst.link}" title="{l s='Read more' d='Modules.Xipblog.Shop'}">
							{else}
							<div class="post_thumbnail">
							{/if}
							{block name="xipblog_post_thumbnail"}
								{if $xpblgpst.post_format == 'video'}
									{assign var="postvideos" value=','|explode:$xpblgpst.video}
									{if $postvideos|@count > 1 }
										{assign var="class" value='carousel'}
									{else}
										{assign var="class" value=''}
									{/if}
									{include file="module:xipblog/views/templates/front/default/post-video.tpl" postvideos=$postvideos width='870' height="482" class=$class}
								{elseif $xpblgpst.post_format == 'audio'}
									{assign var="postaudio" value=','|explode:$xpblgpst.audio}
									{if $postaudio|@count > 1 }
										{assign var="class" value='carousel'}
									{else}
										{assign var="class" value=''}
									{/if}
									{include file="module:xipblog/views/templates/front/default/post-audio.tpl" postaudio=$postaudio class=$class}
								{elseif $xpblgpst.post_format == 'gallery'}
									{if $xpblgpst.gallery_lists|@count > 1 }
										{assign var="class" value='carousel'}
									{else}
										{assign var="class" value=''}
									{/if}
									{include file="module:xipblog/views/templates/front/default/post-gallery.tpl" gallery_lists=$xpblgpst.gallery_lists imagesize="large" class=$class}
								{else}
									<img class="img-responsive" src="{$xpblgpst.post_img_large}" alt="{$xpblgpst.post_title}">
								{/if}
							{/block}
							{if $xpblgpst.post_format != 'video' && $xpblgpst.post_format != 'audio' && $xpblgpst.post_format != 'gallery'}
							</a>
							{else}
							</div>
							{/if}
						</div>
						<div class="blog_post_content_bottom">
							<h3 class="post_title"><a href="{$xpblgpst.link}" title="{l s='Read more' d='Modules.Xipblog.Shop'}">{$xpblgpst.post_title}</a></h3>
							<div class="post_content">
                                {if isset($xpblgpst.post_excerpt) && !empty($xpblgpst.post_excerpt)}
                                    {$xpblgpst.post_excerpt|strip_tags:'UTF-8'|truncate:240:'...'}
                                {else}
                                    {$xpblgpst.post_content|strip_tags:'UTF-8'|truncate:240:'...'}
                                {/if}
								<a class="read_more" href="{$xpblgpst.link}"><span>{l s='Read more' d='Modules.Xipblog.Shop'}</span></a>
							</div>
						</div>
						<div class="post_meta">
							<span class="meta_author font-user">
								{$xpblgpst.post_author_arr.firstname} {$xpblgpst.post_author_arr.lastname}</span>
							</span>
							<span class="meta_date font-calendar">
							     {$xpblgpst.post_date|date_format:$language.date_format_lite nofilter}
							</span>
							{*<span class="meta_category">
								<i class="material-icons">&#xE3C9;</i>
								<a href="{$xpblgpst.category_default_arr.link}">{$xpblgpst.category_default_arr.name}</a>
							</span>*}
							<span class="meta_comment font-eye">
								{l s='Views' d='Modules.Xipblog.Shop'} ({$xpblgpst.comment_count})
							</span>
						</div>
					</div>
				</article>
			{/foreach}
		</div>
	</div>
	{/if}
	</section>
{include file="module:xipblog/views/templates/front/default/pagination.tpl"}
{/block}
{block name="left_column"}
	{assign var="layout_column" value=$layout|replace:'layouts/':''|replace:'.tpl':''|strval}
	{if ($layout_column == 'layout-left-column')}
		<div id="left-column" class="sidebar col-xs-12 col-lg-3">
			{if ($xipblog_column_use == 'own_ps')}
				{hook h="displayxipblogleft"}
			{else}
				{hook h="displayLeftColumn"}
			{/if}
		</div>
	{/if}
{/block}
{block name="right_column"}
	{assign var="layout_column" value=$layout|replace:'layouts/':''|replace:'.tpl':''|strval}
	{if ($layout_column == 'layout-right-column')}
		<div id="right-column" class="sidebar col-xs-12 col-lg-3">
			{if ($xipblog_column_use == 'own_ps')}
				{hook h="displayxipblogright"}
			{else}
				{hook h="displayRightColumn"}
			{/if}
		</div>
	{/if}
{/block}