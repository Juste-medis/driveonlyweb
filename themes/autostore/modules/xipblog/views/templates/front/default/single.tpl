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
  <title>{block name='head_seo_title'}{$xipblogpost.meta_title}{/block}</title>
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
    <meta property="og:title" content="{$xipblogpost.meta_title}" />
    <meta property="og:description" content="{$meta_description}" />
    <meta property="og:url" content="{$urls.current_url}" />
    <meta property="og:site_name" content="{$shop.name}" />
    {if !isset($product) && $page.page_name != 'product'}<meta property="og:type" content="website" />{/if}
  {/block}  
{/block}


{block name='head_microdata_special'}
<script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Article",
      "headline": "{$xipblogpost.post_title}",
      "image": "{$xipblogpost.post_img_home_default}",
      "datePublished": "{$xipblogpost.post_date|date_format:$language.date_format_lite nofilter}",
      "dateModified": "{$xipblogpost.post_date|date_format:$language.date_format_lite nofilter}",
      "author": {
          "@type": "Person",
          "name": "{$xipblogpost.post_author_arr.firstname} {$xipblogpost.post_author_arr.lastname}",
          "url": "{$urls.base_url}"
        }
    }
</script>
{/block}

{block name="page_content_container"}
    <section id="content" class="page-content mb-4">
        <div class="kr_blog_post_area single">
            <div class="kr_blog_post_inner">
                <article id="blog_post" class="blog_post blog_post_{$xipblogpost.post_format}">
                    <div class="blog_post_content">
                        <div class="blog_post_content_top">
                            <div class="post_thumbnail">
                                {if $xipblogpost.post_format == 'video'}
                                    {assign var="postvideos" value=','|explode:$xipblogpost.video}
                                    {if $postvideos|@count > 1 }
                                        {assign var="class" value='carousel'}
                                    {else}
                                        {assign var="class" value=''}
                                    {/if}
                                    {include file="module:xipblog/views/templates/front/default/post-video.tpl" postvideos=$postvideos width='870' height="482" class=$class}
                                {elseif $xipblogpost.post_format == 'audio'}
                                    {assign var="postaudio" value=','|explode:$xipblogpost.audio}
                                    {if $postaudio|@count > 1 }
                                        {assign var="class" value='carousel'}
                                    {else}
                                        {assign var="class" value=''}
                                    {/if}
                                    {include file="module:xipblog/views/templates/front/default/post-audio.tpl" postaudio=$postaudio width='870' height="482" class=$class}
                                {elseif $xipblogpost.post_format == 'gallery'}
                                    {if $xipblogpost.gallery_lists|@count > 1 }
                                        {assign var="class" value='carousel'}
                                    {else}
                                        {assign var="class" value=''}
                                    {/if}
                                    {include file="module:xipblog/views/templates/front/default/post-gallery.tpl" gallery_lists=$xipblogpost.gallery_lists imagesize="medium" class=$class}
                                {else}
                                    <img class="xipblog_img img-responsive" src="{$xipblogpost.post_img_large}" alt="{$xipblogpost.post_title}">
                                {/if}
                            </div>
                        </div>

                        <div class="blog_post_content_bottom">
                            <h3 class="post_title">{$xipblogpost.post_title}</h3>
                            <div class="post_meta clearfix">
                                <div class="meta_author">
                                    <i class="material-icons">&#xE7FD;</i>
                                    <span> {$xipblogpost.post_author_arr.firstname} {$xipblogpost.post_author_arr.lastname}</span>
                                </div>
                                <div class="meta_category">
                                    <i class="material-icons">&#xE3C9;</i>
                                    <span>{l s='In' d='Modules.Xipblog.Shop'}</span>
                                    <span>{$xipblogpost.category_default_arr.name}</span>
                                </div>
                                <div class="meta_comment">
                                    <i class="material-icons">remove_red_eye</i>
                                    <span>{l s='Views' d='Modules.Xipblog.Shop'} ({$xipblogpost.comment_count})</span>
                                </div>
                            </div>
                            <div class="post_content rte">
                                {$xipblogpost.post_content nofilter}
                            </div>
                        </div>

                    </div>
                </article>
            </div>
        </div>
    </section>
    {if (($xipblogpost.comment_status == 'open') || ($xipblogpost.comment_status == 'close')) && (isset($disable_blog_com) && $disable_blog_com == 1)}
        {include file="module:xipblog/views/templates/front/default/comment-list.tpl"}
    {/if}
    {if (isset($disable_blog_com) && $disable_blog_com == 1) && ($xipblogpost.comment_status == 'open')}
        {include file="module:xipblog/views/templates/front/default/comment.tpl"}
    {/if}
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