{extends file='checkout/checkout.tpl'}
{block name='footer'}
  {block name='hook_footer_custom'}
      {hook h='displayFooterCustom'}
  {/block}
<div class="footer-container wow fadeInDown">
    <div class="footer-one">
      <div class="container">
        <div class="row">
          {block name='hook_footer'}
            {hook h='displayFooter'}
          {/block}
          <div class="footer-three col-lg-3">
            {block name='hook_footer_before'}
              {hook h='displayFooterBefore'}
            {/block}
          </div>
        </div>
      </div>
    </div>
    <div class="footer-two">
      <div class="container">
        <div class="row inner-wrapper">
          {*{block name='copyright_link'}
            <a class="copyright" href="http://www.prestashop.com" target="_blank">
              {l s='%copyright% %year% - Ecommerce software by %prestashop%' sprintf=['%prestashop%' => 'PrestaShop™', '%year%' => 'Y'|date, '%copyright%' => '©'] d='Shop.Theme.Global'}
            </a>
          {/block}*}
          {block name='hook_footer_after'}
            {hook h='displayFooterAfter'}
          {/block}
        </div>
      </div>
    </div>
</div>
<div class="btn-to-top js-btn-to-top"></div>
{/block}