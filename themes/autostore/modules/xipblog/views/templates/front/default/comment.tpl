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
<div class="comment_respond clearfix m_bottom_50" id="respond">
    <h3 class="comment_reply_title" id="reply-title">
        {l s='Leave a reply' d='Modules.Xipblog.Shop'}
        <small>
            <a href="/wp_showcase/wp-supershot/?p=38#respond" id="cancel-comment-reply-link" rel="nofollow" style="display:none;">
                {l s='Cancel reply' d='Modules.Xipblog.Shop'}
            </a>
        </small>
    </h3>
    <form class="comment_form js-comment-form" action="" method="post" id="xipblogs_commentfrom" role="form" data-toggle="validator" data-success-text="{l s='Successfully comment added' d='Modules.Xipblog.Shop'}" data-error-text="{l s='Something wrong! please try again' d='Modules.Xipblog.Shop'}" data-wait-text="{l s='Please wait...' d='Modules.Xipblog.Shop'}" data-submit-text="{l s='Send comment' d='Modules.Xipblog.Shop'}">
    	<div class="form-group xipblogs_message"></div>
    	<div class="form-group xipblog_name_parent">
    	  <label for="xipblog_name">{l s='Your name:' d='Modules.Xipblog.Shop'}</label>
    	  <input type="text"  id="xipblog_name" name="xipblog_name" class="form-control xipblog_name" required>
    	</div>
    	<div class="form-group xipblog_email_parent">
    	  <label for="xipblog_email">{l s='Your Email:' d='Modules.Xipblog.Shop'}</label>
    	  <input type="email"  id="xipblog_email" name="xipblog_email" class="form-control xipblog_email" required>
    	</div>
    	<div class="form-group xipblog_subject_parent">
    	  <label for="xipblog_subject">{l s='Subject:' d='Modules.Xipblog.Shop'}</label>
    	  <input type="text"  id="xipblog_subject" name="xipblog_subject" class="form-control xipblog_subject" required>
    	</div>
    	<div class="form-group xipblog_content_parent">
    	  <label for="xipblog_content">{l s='Comment:' d='Modules.Xipblog.Shop'}</label>
    	  <textarea rows="15" cols="" id="xipblog_content" name="xipblog_content" class="form-control xipblog_content" required></textarea>
    	</div>
    	<input type="hidden" class="xipblog_id_parent" id="xipblog_id_parent" name="xipblog_id_parent" value="0">
    	<input type="hidden" class="xipblog_id_post" id="xipblog_id_post" name="xipblog_id_post" value="{$xipblogpost.id_xipposts}">
    	<input type="submit" class="btn btn-default pull-left xipblog_submit_btn" value="{l s='Send comment' d='Modules.Xipblog.Shop'}">
    </form>
</div>