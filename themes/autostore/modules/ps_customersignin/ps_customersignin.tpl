<div id="_desktop_user_info">
    <div class="header_user_info dropdown-mobile">
      <span class="js-toggle btn-toggle-mobile font-user hidden-lg-up"></span>
      <div class="js-toggle-list header_user_info__list dropdown-toggle-mobile">
        {if $logged}
          <a
            class="logout "
            href="{$logout_url}"
            rel="nofollow"
            title="{l s='Sign out' d='Shop.Theme.Actions'}"
          >
            <i class="material-icons hidden-md-down">lock_open</i>
            <span>{l s='Sign out' d='Shop.Theme.Actions'}</span>
          </a>
          <a
            class="account"
            href="{$my_account_url}"
            title="{l s='View my customer account' d='Shop.Theme.Customeraccount'}"
            rel="nofollow"
          >
            <i class="font-user hidden-md-down"></i>
            <span>{$customerName}</span>
          </a>
        {else}
          <a
            href="{$my_account_url}"
            title="{l s='Log in to your customer account' d='Shop.Theme.Customeraccount'}"
            rel="nofollow"
          >
            <i class="font-sign-in hidden-md-down"></i>
            <span>{l s='Sign in' d='Shop.Theme.Actions'}</span>
          </a>
          {*<a
            href="{$urls.pages.register}"
            title="{l s='Log in to your customer account' d='Shop.Theme.Customeraccount'}"
            rel="nofollow"
          >
            <span>{l s='Register' d='Shop.Theme.Actions'}</span>
          </a>*}
        {/if}
      </div>
  </div>
</div>
